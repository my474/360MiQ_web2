<?php
require_once __DIR__ . '/db.php';

function miq_account_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = miq_account_config();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    ini_set('session.gc_maxlifetime', (string) $config['session_lifetime']);
    ini_set('session.use_strict_mode', '1');
    session_name($config['cookie_name']);
    session_set_cookie_params(array(
        'lifetime' => $config['session_lifetime'],
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    session_start();
}

function miq_account_bootstrap()
{
    miq_account_start_session();
    if (!isset($_SESSION['miq_account_session_version'])) {
        $_SESSION['miq_account_session_version'] = 1;
    }
}

function miq_account_session_hash()
{
    miq_account_start_session();
    $session_id = session_id();
    return $session_id === '' ? '' : hash('sha256', $session_id);
}

function miq_account_remove_current_session()
{
    $session_hash = miq_account_session_hash();
    if ($session_hash === '') {
        return;
    }

    try {
        $sessions = miq_account_table('sessions');
        miq_account_query("DELETE FROM {$sessions} WHERE session_hash = ?", 's', array($session_hash))->close();
    } catch (Throwable $error) {
        error_log('360MiQ session cleanup failure: ' . $error->getMessage());
    }
}

function miq_account_record_activity($user_id, $force = false, $login = false)
{
    miq_account_start_session();
    $user_id = (int) $user_id;
    $interval = (int) miq_account_config()['activity_write_interval'];
    $last_write = isset($_SESSION['miq_account_last_activity_write']) ? (int) $_SESSION['miq_account_last_activity_write'] : 0;
    if (!$force && $last_write > 0 && time() - $last_write < $interval) {
        return;
    }
    $_SESSION['miq_account_last_activity_write'] = time();

    try {
        $users = miq_account_table('users');
        $sessions = miq_account_table('sessions');
        $activity = miq_account_table('user_activity_daily');
        $session_hash = miq_account_session_hash();
        $user_agent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $ip_hash = hash('sha256', miq_account_client_ip());
        $expires_at = gmdate('Y-m-d H:i:s', time() + (int) miq_account_config()['session_lifetime']);

        if ($login) {
            miq_account_query(
                "UPDATE {$users} SET last_login_at = UTC_TIMESTAMP(), last_seen_at = UTC_TIMESTAMP(), login_count = login_count + 1 WHERE id = ?",
                'i',
                array($user_id)
            )->close();
        } else {
            miq_account_query("UPDATE {$users} SET last_seen_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array($user_id))->close();
        }

        miq_account_query(
            "INSERT INTO {$activity} (user_id, activity_date, first_seen_at, last_seen_at, request_count) VALUES (?, UTC_DATE(), UTC_TIMESTAMP(), UTC_TIMESTAMP(), 1) ON DUPLICATE KEY UPDATE last_seen_at = UTC_TIMESTAMP(), request_count = request_count + 1",
            'i',
            array($user_id)
        )->close();

        if ($session_hash !== '') {
            miq_account_query(
                "INSERT INTO {$sessions} (user_id, session_hash, user_agent, ip_hash, last_seen_at, expires_at, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), user_agent = VALUES(user_agent), ip_hash = VALUES(ip_hash), last_seen_at = UTC_TIMESTAMP(), expires_at = VALUES(expires_at)",
                'issss',
                array($user_id, $session_hash, $user_agent, $ip_hash, $expires_at)
            )->close();
        }

        if ($login) {
            miq_account_query("DELETE FROM {$sessions} WHERE expires_at < UTC_TIMESTAMP()")->close();
        }
    } catch (Throwable $error) {
        error_log('360MiQ account activity failure: ' . $error->getMessage());
    }
}

function miq_account_release_expired_suspension($user)
{
    if (!$user || ($user['status'] ?? '') !== 'suspended' || empty($user['suspended_until'])) {
        return $user;
    }
    $until = strtotime($user['suspended_until'] . ' UTC');
    if ($until === false || $until > time()) {
        return $user;
    }

    $users = miq_account_table('users');
    $statement = miq_account_query(
        "UPDATE {$users} SET status = 'active', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL, suspended_by_user_id = NULL, updated_at = UTC_TIMESTAMP() WHERE id = ? AND status = 'suspended' AND suspended_until IS NOT NULL AND suspended_until <= UTC_TIMESTAMP()",
        'i',
        array((int) $user['id'])
    );
    $released = $statement->affected_rows > 0;
    $statement->close();
    if ($released) {
        $user['status'] = 'active';
        $user['suspended_at'] = null;
        $user['suspended_until'] = null;
        $user['suspension_reason'] = null;
        $user['suspended_by_user_id'] = null;
    }
    return $user;
}

function miq_account_access_error($user)
{
    if ($user && ($user['status'] ?? '') === 'suspended') {
        if (!empty($user['suspended_until'])) {
            $until = strtotime($user['suspended_until'] . ' UTC');
            if ($until !== false) {
                return 'This account is suspended until ' . gmdate('Y-m-d H:i', $until) . ' UTC.';
            }
        }
        return 'This account has been permanently blocked.';
    }
    return 'This account is not available.';
}

function miq_account_require_active_user($user)
{
    $user = miq_account_release_expired_suspension($user);
    if (!$user || ($user['status'] ?? '') !== 'active') {
        throw new RuntimeException(miq_account_access_error($user));
    }
    return $user;
}

function miq_account_current_user()
{
    static $loaded = false;
    static $user = null;

    $cookie_name = miq_account_config()['cookie_name'];
    if (session_status() !== PHP_SESSION_ACTIVE && empty($_COOKIE[$cookie_name])) {
        $loaded = true;
        return null;
    }
    miq_account_bootstrap();
    if ($loaded) {
        return $user;
    }
    $loaded = true;

    if (empty($_SESSION['miq_account_user_id'])) {
        return null;
    }

    try {
        $table = miq_account_table('users');
        $user = miq_account_fetch_one(miq_account_query(
            "SELECT id, email, display_name, avatar_url, role, email_verified_at, status, session_version, last_login_at, last_seen_at, login_count, suspended_at, suspended_until, suspension_reason, suspended_by_user_id FROM {$table} WHERE id = ? LIMIT 1",
            'i',
            array((int) $_SESSION['miq_account_user_id'])
        ));
    } catch (Throwable $error) {
        $user = null;
    }

    $user = miq_account_release_expired_suspension($user);
    if (!$user || $user['status'] !== 'active' || (int) $user['session_version'] !== (int) $_SESSION['miq_account_session_version']) {
        miq_account_logout(false);
        return null;
    }

    miq_account_record_activity((int) $user['id']);
    return $user;
}

function miq_account_is_authenticated()
{
    return miq_account_current_user() !== null;
}

function miq_account_is_moderator($user = null)
{
    $user = $user ?: miq_account_current_user();
    if (!$user) {
        return false;
    }

    if (miq_account_is_admin($user) || $user['role'] === 'moderator') {
        return true;
    }

    $emails = miq_account_config()['moderator_emails'];
    return in_array(strtolower($user['email']), array_map('strtolower', $emails), true);
}

function miq_account_is_admin($user = null)
{
    $user = $user ?: miq_account_current_user();
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'admin') {
        return true;
    }
    $emails = miq_account_config()['admin_emails'];
    return in_array(strtolower($user['email']), array_map('strtolower', $emails), true);
}

function miq_account_logout($destroy_session = true)
{
    miq_account_start_session();
    miq_account_remove_current_session();
    unset($_SESSION['miq_account_user_id'], $_SESSION['miq_account_session_version'], $_SESSION['miq_account_last_activity_write']);
    if ($destroy_session) {
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

function miq_account_login_user($user_id, $session_version, $record_login = true)
{
    miq_account_start_session();
    miq_account_remove_current_session();
    session_regenerate_id(true);
    $_SESSION['miq_account_user_id'] = (int) $user_id;
    $_SESSION['miq_account_session_version'] = (int) $session_version;
    unset($_SESSION['miq_account_last_activity_write']);
    miq_account_record_activity((int) $user_id, true, (bool) $record_login);
}

function miq_account_normalize_email($email)
{
    return strtolower(trim((string) $email));
}

if (!class_exists('MiqAccountDisplayNameTakenException')) {
    class MiqAccountDisplayNameTakenException extends RuntimeException
    {
        public $suggestions;

        public function __construct($suggestions = array())
        {
            $this->suggestions = is_array($suggestions) ? $suggestions : array();
            parent::__construct('That display name is already taken. Choose an available suggestion or enter another name.');
        }
    }
}

if (!class_exists('MiqAccountDisplayNamePolicyException')) {
    class MiqAccountDisplayNamePolicyException extends InvalidArgumentException
    {
        public $reason;

        public function __construct($reason = 'official')
        {
            $this->reason = (string) $reason;
            parent::__construct('That display name cannot be used because it could be mistaken for 360MiQ or an official 360MiQ account. Please choose a personal name or handle.');
        }
    }
}

if (!class_exists('MiqAccountRateLimitException')) {
    class MiqAccountRateLimitException extends RuntimeException
    {
    }
}

function miq_account_clean_display_name($name)
{
    $name = trim(preg_replace('/\s+/', ' ', (string) $name));
    return substr($name, 0, 80);
}

function miq_account_display_name($name, $email)
{
    $name = miq_account_clean_display_name($name);
    if ($name === '') {
        $name = strstr($email, '@', true);
    }
    $name = miq_account_clean_display_name($name);
    return $name !== '' ? $name : 'Investor';
}

function miq_account_display_name_policy_text($name)
{
    $value = (string) $name;
    if (class_exists('Normalizer')) {
        $normalized = \Normalizer::normalize($value, \Normalizer::FORM_KD);
        if (is_string($normalized)) {
            $value = $normalized;
        }
    }

    // Cover the most common Latin/Cyrillic/Greek look-alikes used in brand impersonation.
    $value = strtr($value, array(
        'А' => 'A', 'а' => 'a', 'В' => 'B', 'в' => 'b', 'С' => 'C', 'с' => 'c',
        'Е' => 'E', 'е' => 'e', 'Н' => 'H', 'н' => 'h', 'І' => 'I', 'і' => 'i',
        'Ј' => 'J', 'ј' => 'j', 'К' => 'K', 'к' => 'k', 'М' => 'M', 'м' => 'm',
        'О' => 'O', 'о' => 'o', 'Р' => 'P', 'р' => 'p', 'Т' => 'T', 'т' => 't',
        'Х' => 'X', 'х' => 'x', 'Ү' => 'Y', 'ү' => 'y', 'Ζ' => 'Z', 'ζ' => 'z',
        'Ι' => 'I', 'ι' => 'i', 'Ο' => 'O', 'ο' => 'o', 'Ρ' => 'P', 'ρ' => 'p',
        'Χ' => 'X', 'χ' => 'x', 'Υ' => 'Y', 'υ' => 'y', 'ı' => 'i', 'ɪ' => 'i',
        '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
        '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
        'Ｍ' => 'M', 'ｍ' => 'm', 'Ｉ' => 'I', 'ｉ' => 'i', 'Ｑ' => 'Q', 'ｑ' => 'q',
        'Ԛ' => 'Q', 'ԛ' => 'q'
    ));
    $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
    $value = strtolower($value);
    $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value);
    return is_string($value) ? $value : (string) $name;
}

function miq_account_display_name_policy_key($name)
{
    $value = miq_account_display_name_policy_text($name);
    $value = preg_replace('/[^a-z0-9]+/', '', $value);
    return is_string($value) ? $value : '';
}

function miq_account_display_name_policy_violation($name)
{
    $policy_text = miq_account_display_name_policy_text($name);
    $policy_key = miq_account_display_name_policy_key($name);
    $brand_key = strtr($policy_key, array('o' => '0', '1' => 'i', 'l' => 'i'));

    if (
        strpos($policy_key, '360miq') !== false
        || strpos($brand_key, '360miq') !== false
        || strpos($policy_key, 'threesixtymiq') !== false
    ) {
        return 'brand';
    }

    $tokens = preg_split('/[^a-z0-9]+/', $policy_text, -1, PREG_SPLIT_NO_EMPTY);
    $reserved_official_terms = array(
        'admin', 'administrator', 'billing', 'compliance', 'customer service',
        'customer support', 'employee', 'finance', 'help desk', 'helpdesk',
        'legal', 'moderator', 'newsroom', 'official', 'office', 'press',
        'privacy', 'sales', 'security', 'service desk', 'servicedesk',
        'staff', 'support', 'team', 'technical support', 'trust and safety',
        'verified', 'verification'
    );
    $joined_tokens = implode(' ', $tokens);
    $joined_key = implode('', $tokens);
    foreach ($reserved_official_terms as $term) {
        $term_tokens = preg_split('/[^a-z0-9]+/', $term, -1, PREG_SPLIT_NO_EMPTY);
        $term_key = implode('', $term_tokens);
        if ($joined_tokens === $term || $joined_key === $term_key || in_array($term, $tokens, true)) {
            return 'official';
        }
    }

    // These combinations are particularly likely to be read as an official support identity.
    $official_combinations = array(
        array('account', 'team'),
        array('community', 'team'),
        array('customer', 'care'),
        array('help', 'center'),
        array('help', 'centre'),
        array('support', 'team'),
        array('system', 'admin'),
        array('account', 'recovery'),
        array('trust', 'safety')
    );
    foreach ($official_combinations as $combination) {
        if (count(array_intersect($combination, $tokens)) === count($combination)) {
            return 'official';
        }
    }

    return null;
}

function miq_account_validate_display_name_policy($name)
{
    $violation = miq_account_display_name_policy_violation($name);
    if ($violation !== null) {
        throw new MiqAccountDisplayNamePolicyException($violation);
    }
}

function miq_account_display_name_exists($display_name, $exclude_user_id = 0)
{
    $users = miq_account_table('users');
    $sql = "SELECT id FROM {$users} WHERE LOWER(display_name) = LOWER(?)";
    $types = 's';
    $params = array($display_name);
    if ((int) $exclude_user_id > 0) {
        $sql .= ' AND id <> ?';
        $types .= 'i';
        $params[] = (int) $exclude_user_id;
    }
    $sql .= ' LIMIT 1';
    return miq_account_fetch_one(miq_account_query($sql, $types, $params)) !== null;
}

function miq_account_display_name_with_suffix($base, $suffix)
{
    $max_base_length = 80 - strlen($suffix);
    return substr($base, 0, max(1, $max_base_length)) . $suffix;
}

function miq_account_display_name_suggestions($name, $email, $exclude_user_id = 0)
{
    $base = miq_account_display_name($name, $email);
    $suggestions = array();
    foreach (array('2', '3', '4', '5', '6', '7', '8', '9', '10') as $suffix) {
        $candidate = miq_account_display_name_with_suffix($base, $suffix);
        if (!miq_account_display_name_exists($candidate, $exclude_user_id)) {
            $suggestions[] = $candidate;
        }
        if (count($suggestions) >= 3) {
            break;
        }
    }
    return $suggestions;
}

function miq_account_resolve_display_name($name, $email, $allow_suggested_suffix = false, $exclude_user_id = 0)
{
    $candidate = miq_account_display_name($name, $email);
    miq_account_validate_display_name_policy($candidate);
    if (!miq_account_display_name_exists($candidate, $exclude_user_id)) {
        return $candidate;
    }

    $suggestions = miq_account_display_name_suggestions($candidate, $email, $exclude_user_id);
    if ($allow_suggested_suffix && !empty($suggestions)) {
        return $suggestions[0];
    }

    throw new MiqAccountDisplayNameTakenException($suggestions);
}

function miq_account_csrf_token()
{
    miq_account_start_session();
    if (empty($_SESSION['miq_account_csrf'])) {
        $_SESSION['miq_account_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['miq_account_csrf'];
}

function miq_account_check_csrf($token)
{
    return is_string($token) && hash_equals(miq_account_csrf_token(), $token);
}

function miq_account_issue_native_google_challenge($return_to = 'workspace')
{
    miq_account_start_session();
    $nonce = bin2hex(random_bytes(32));
    $_SESSION['miq_native_google_challenge'] = array(
        'nonce_hash' => hash('sha256', $nonce),
        'return_to' => miq_account_safe_return_to($return_to, 'workspace'),
        'expires_at' => time() + (int) miq_account_config()['native_google_challenge_ttl'],
    );
    return $nonce;
}

function miq_account_consume_native_google_challenge($nonce)
{
    miq_account_start_session();
    $challenge = isset($_SESSION['miq_native_google_challenge'])
        ? $_SESSION['miq_native_google_challenge']
        : null;
    unset($_SESSION['miq_native_google_challenge']);

    $nonce = is_string($nonce) ? trim($nonce) : '';
    if (
        !is_array($challenge)
        || !preg_match('/^[a-f0-9]{64}$/', $nonce)
        || empty($challenge['nonce_hash'])
        || empty($challenge['expires_at'])
        || (int) $challenge['expires_at'] < time()
        || !hash_equals((string) $challenge['nonce_hash'], hash('sha256', $nonce))
    ) {
        throw new RuntimeException('The Android Google sign-in request expired. Reload the account page and try again.');
    }

    return array(
        'nonce' => $nonce,
        'return_to' => miq_account_safe_return_to($challenge['return_to'] ?? 'workspace', 'workspace'),
    );
}

function miq_account_safe_return_to($value, $fallback = '/')
{
    $value = trim((string) $value);
    if (
        $value === ''
        || preg_match('/[\x00-\x1F\x7F\\\\]/', $value)
        || preg_match('/%(?:2f|5c)/i', $value)
        || strpos($value, '//') === 0
    ) {
        return $fallback;
    }

    $parts = parse_url($value);
    if (
        $parts === false
        || isset($parts['scheme'])
        || isset($parts['host'])
        || isset($parts['user'])
        || isset($parts['pass'])
    ) {
        return $fallback;
    }

    return $value;
}

function miq_account_validate_password($password)
{
    $length = strlen((string) $password);
    if ($length < 8) {
        throw new InvalidArgumentException('Use a password with at least 8 characters.');
    }
    if ($length > 1024) {
        throw new InvalidArgumentException('The password is too long.');
    }
}

function miq_account_flash($type, $message)
{
    miq_account_start_session();
    $_SESSION['miq_account_flash'] = array('type' => $type, 'message' => $message);
}

function miq_account_take_flash()
{
    miq_account_start_session();
    $flash = isset($_SESSION['miq_account_flash']) ? $_SESSION['miq_account_flash'] : null;
    unset($_SESSION['miq_account_flash']);
    return $flash;
}

function miq_account_hash_token($token)
{
    return hash('sha256', $token);
}

function miq_account_client_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function miq_account_rate_limit_key($scope, $identifier)
{
    return hash('sha256', (string) $scope . "\0" . strtolower(trim((string) $identifier)));
}

function miq_account_rate_limit($scope, $identifier, $limit, $window_seconds)
{
    $limit = max(1, (int) $limit);
    $window_seconds = max(1, (int) $window_seconds);
    $db = null;

    try {
        $db = miq_account_db();
        $table = miq_account_table('rate_limits');
        $key_hash = miq_account_rate_limit_key($scope, $identifier);

        $db->begin_transaction();

        $insert = $db->prepare("INSERT IGNORE INTO {$table} (scope, key_hash, window_started_at, attempts, last_attempt_at) VALUES (?, ?, UTC_TIMESTAMP(), 0, UTC_TIMESTAMP())");
        if (!$insert) {
            throw new RuntimeException('Rate-limit initialization failed.');
        }
        $insert->bind_param('ss', $scope, $key_hash);
        if (!$insert->execute()) {
            $insert->close();
            throw new RuntimeException('Rate-limit initialization failed.');
        }
        $insert->close();

        $select = $db->prepare("SELECT window_started_at, attempts FROM {$table} WHERE scope = ? AND key_hash = ? FOR UPDATE");
        if (!$select) {
            throw new RuntimeException('Rate-limit lookup failed.');
        }
        $select->bind_param('ss', $scope, $key_hash);
        if (!$select->execute()) {
            $select->close();
            throw new RuntimeException('Rate-limit lookup failed.');
        }
        $result = $select->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if ($result) {
            $result->free();
        }
        $select->close();
        if (!$row) {
            throw new RuntimeException('Rate-limit record was not created.');
        }

        $window_started = strtotime($row['window_started_at'] . ' UTC');
        $attempts = (int) $row['attempts'];
        if ($window_started === false || time() - $window_started >= $window_seconds) {
            $attempts = 1;
            $update = $db->prepare("UPDATE {$table} SET window_started_at = UTC_TIMESTAMP(), attempts = ?, last_attempt_at = UTC_TIMESTAMP() WHERE scope = ? AND key_hash = ?");
        } else {
            $attempts++;
            $update = $db->prepare("UPDATE {$table} SET attempts = ?, last_attempt_at = UTC_TIMESTAMP() WHERE scope = ? AND key_hash = ?");
        }
        if (!$update) {
            throw new RuntimeException('Rate-limit update failed.');
        }
        $update->bind_param('iss', $attempts, $scope, $key_hash);
        if (!$update->execute()) {
            $update->close();
            throw new RuntimeException('Rate-limit update failed.');
        }
        $update->close();
        $db->commit();

        return $attempts <= $limit;
    } catch (Throwable $error) {
        if ($db instanceof mysqli) {
            $db->rollback();
        }
        error_log('360MiQ account rate-limit failure: ' . $error->getMessage());
        return false;
    }
}

function miq_account_require_rate_limit($scope, $identifier, $message)
{
    $limits = miq_account_config()['rate_limits'];
    if (!isset($limits[$scope]) || !miq_account_rate_limit($scope, $identifier, $limits[$scope]['limit'], $limits[$scope]['window'])) {
        throw new MiqAccountRateLimitException($message);
    }
}

function miq_account_email_rate_limit($to)
{
    $limits = miq_account_config()['rate_limits'];
    $checks = array(
        array('email_ip', miq_account_client_ip()),
        array('email_recipient', miq_account_normalize_email($to)),
        array('email_cooldown', miq_account_normalize_email($to)),
    );

    foreach ($checks as $check) {
        if (!isset($limits[$check[0]]) || !miq_account_rate_limit($check[0], $check[1], $limits[$check[0]]['limit'], $limits[$check[0]]['window'])) {
            return false;
        }
    }

    return true;
}

function miq_account_send_mail($to, $subject, $body)
{
    $config = miq_account_config();
    if (!miq_account_email_rate_limit($to)) {
        error_log('360MiQ account email rate limit reached for recipient hash ' . miq_account_rate_limit_key('email_recipient', $to));
        return false;
    }
    // Production uses the existing authenticated PHPMailer helper outside
    // the public web root. Keep SMTP credentials in that server-side file.
    if ($config['mailer_include'] !== '') {
        if (!is_file($config['mailer_include'])) {
            error_log('360MiQ account mailer include was not found: ' . $config['mailer_include']);
            return false;
        }

        require_once $config['mailer_include'];
        if (!function_exists('email')) {
            error_log('360MiQ account mailer include does not define email().');
            return false;
        }

        try {
            $html_body = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
            $result = email($subject, $html_body, $to, $to);
            if ($result !== true) {
                error_log('360MiQ account mailer helper did not confirm delivery. email() must return true on success and false on failure.');
                return false;
            }
            return true;
        } catch (Throwable $exception) {
            error_log('360MiQ account email delivery failed: ' . $exception->getMessage());
            return false;
        }
    }

    $headers = 'From: ' . $config['email_from_name'] . ' <' . $config['email_from'] . ">\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}

function miq_account_create_token($bytes = 32)
{
    return bin2hex(random_bytes($bytes));
}

function miq_account_create_user($email, $password = null, $display_name = '', $provider = 'email', $provider_user_id = null, $allow_suggested_name = false)
{
    $email = miq_account_normalize_email($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid email address.');
    }

    $users = miq_account_table('users');
    $identities = miq_account_table('identities');
    $password_hash = $password !== null ? password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT) : null;
    $db = miq_account_db();

    $db->begin_transaction();
    try {
        $existing = miq_account_fetch_one(miq_account_query("SELECT id FROM {$users} WHERE email = ? LIMIT 1", 's', array($email)));
        if ($existing) {
            throw new RuntimeException('An account already exists for this email. Sign in or link Google from account settings.');
        }

        $name = miq_account_resolve_display_name($display_name, $email, $allow_suggested_name);
        try {
            $statement = miq_account_query(
                "INSERT INTO {$users} (email, password_hash, display_name, role, status, session_version, created_at, updated_at) VALUES (?, ?, ?, 'user', 'active', 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                'sss',
                array($email, $password_hash, $name)
            );
        } catch (Throwable $error) {
            // A unique database index can still reject a simultaneous signup.
            if (miq_account_display_name_exists($name)) {
                throw new MiqAccountDisplayNameTakenException(miq_account_display_name_suggestions($name, $email));
            }
            throw $error;
        }
        $user_id = (int) $db->insert_id;
        $statement->close();

        miq_account_query(
            "INSERT INTO {$identities} (user_id, provider, provider_user_id, provider_email, created_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())",
            'isss',
            array($user_id, $provider, $provider_user_id, $email)
        )->close();
        $db->commit();
        return $user_id;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}

function miq_account_issue_email_token($user_id, $type)
{
    $token = miq_account_create_token();
    $table = miq_account_table($type === 'verify' ? 'email_tokens' : 'password_reset_tokens');
    $user_id = (int) $user_id;
    $expires = $type === 'verify' ? 86400 : 3600;
    $expires_at = gmdate('Y-m-d H:i:s', time() + $expires);
    miq_account_query("DELETE FROM {$table} WHERE user_id = ?", 'i', array($user_id))->close();
    miq_account_query(
        "INSERT INTO {$table} (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())",
        'iss',
        array($user_id, miq_account_hash_token($token), $expires_at)
    )->close();
    return $token;
}

function miq_account_google_identity($credential, $expected_nonce = null)
{
    $config = miq_account_config();
    if ($config['google_client_id'] === '') {
        throw new RuntimeException('Google login is not configured yet.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Google login requires the PHP cURL extension.');
    }

    $credential = trim((string) $credential);
    if ($credential === '' || strlen($credential) > 16384) {
        throw new RuntimeException('Google returned an invalid login credential.');
    }

    $curl = curl_init($config['google_tokeninfo_url']);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array('id_token' => $credential), '', '&', PHP_QUERY_RFC3986),
    ));
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $claims = json_decode((string) $response, true);

    if ($status !== 200 || !is_array($claims)) {
        throw new RuntimeException('Google login could not be verified.');
    }
    $issuer = (string) ($claims['iss'] ?? '');
    if (!in_array($issuer, array('accounts.google.com', 'https://accounts.google.com'), true)) {
        throw new RuntimeException('Google login returned an invalid token issuer.');
    }
    if (!hash_equals($config['google_client_id'], (string) ($claims['aud'] ?? '')) || (int) ($claims['exp'] ?? 0) < time()) {
        throw new RuntimeException('Google login token has expired or belongs to another application.');
    }
    if ($expected_nonce !== null) {
        $expected_nonce = (string) $expected_nonce;
        $returned_nonce = (string) ($claims['nonce'] ?? '');
        if ($expected_nonce === '' || $returned_nonce === '' || !hash_equals($expected_nonce, $returned_nonce)) {
            throw new RuntimeException('Google login could not be matched to this Android sign-in request.');
        }
    }
    $email_verified = $claims['email_verified'] ?? false;
    if (!($email_verified === true || $email_verified === 'true') || empty($claims['sub']) || empty($claims['email'])) {
        throw new RuntimeException('Google returned an unverified account.');
    }

    return array(
        'provider_user_id' => (string) $claims['sub'],
        'email' => miq_account_normalize_email($claims['email']),
        'display_name' => miq_account_display_name($claims['name'] ?? '', $claims['email']),
        'avatar_url' => isset($claims['picture']) ? (string) $claims['picture'] : null,
    );
}

function miq_account_find_google_user($provider_user_id)
{
    $identities = miq_account_table('identities');
    $users = miq_account_table('users');
    return miq_account_fetch_one(miq_account_query(
        "SELECT u.id, u.email, u.display_name, u.role, u.email_verified_at, u.status, u.session_version, u.suspended_at, u.suspended_until, u.suspension_reason, u.suspended_by_user_id FROM {$users} u INNER JOIN {$identities} i ON i.user_id = u.id WHERE i.provider = 'google' AND i.provider_user_id = ? LIMIT 1",
        's',
        array((string) $provider_user_id)
    ));
}

function miq_account_process_google_login($credential, $expected_nonce = null)
{
    $identity = miq_account_google_identity($credential, $expected_nonce);
    $user = miq_account_find_google_user($identity['provider_user_id']);

    if (!$user) {
        $existing = miq_account_find_user_by_email($identity['email']);
        if ($existing) {
            throw new RuntimeException('An account already exists for this email. Sign in with email first, then connect Google from your account settings.');
        }

        $user_id = miq_account_create_user($identity['email'], null, $identity['display_name'], 'google', $identity['provider_user_id'], true);
        $users = miq_account_table('users');
        miq_account_query(
            "UPDATE {$users} SET avatar_url = ?, email_verified_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ?",
            'si',
            array($identity['avatar_url'], $user_id)
        )->close();
        $user = miq_account_find_google_user($identity['provider_user_id']);
    }

    $user = miq_account_require_active_user($user);
    miq_account_login_user($user['id'], $user['session_version']);
}

function miq_account_find_user_by_email($email)
{
    $users = miq_account_table('users');
    return miq_account_fetch_one(miq_account_query(
        "SELECT id, email, password_hash, display_name, role, status, email_verified_at, session_version, suspended_at, suspended_until, suspension_reason, suspended_by_user_id FROM {$users} WHERE email = ? LIMIT 1",
        's',
        array(miq_account_normalize_email($email))
    ));
}
