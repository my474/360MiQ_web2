<?php
require_once __DIR__ . '/db.php';

function miq_account_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = miq_account_config();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
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

function miq_account_current_user()
{
    static $loaded = false;
    static $user = null;

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
            "SELECT id, email, display_name, role, email_verified_at, status, session_version FROM {$table} WHERE id = ? LIMIT 1",
            'i',
            array((int) $_SESSION['miq_account_user_id'])
        ));
    } catch (Throwable $error) {
        $user = null;
    }

    if (!$user || $user['status'] !== 'active' || (int) $user['session_version'] !== (int) $_SESSION['miq_account_session_version']) {
        miq_account_logout(false);
        return null;
    }

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

    if (in_array($user['role'], array('moderator', 'admin'), true)) {
        return true;
    }

    $emails = miq_account_config()['moderator_emails'];
    return in_array(strtolower($user['email']), array_map('strtolower', $emails), true);
}

function miq_account_logout($destroy_session = true)
{
    miq_account_start_session();
    unset($_SESSION['miq_account_user_id'], $_SESSION['miq_account_session_version']);
    if ($destroy_session) {
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

function miq_account_login_user($user_id, $session_version)
{
    miq_account_start_session();
    session_regenerate_id(true);
    $_SESSION['miq_account_user_id'] = (int) $user_id;
    $_SESSION['miq_account_session_version'] = (int) $session_version;
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

function miq_account_safe_return_to($value, $fallback = '/')
{
    $value = trim((string) $value);
    if ($value === '' || strpos($value, '/') !== 0 || strpos($value, '//') === 0 || preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)) {
        return $fallback;
    }
    return $value;
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

function miq_account_send_mail($to, $subject, $body)
{
    $config = miq_account_config();
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
    miq_account_query(
        "INSERT INTO {$table} (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())",
        'iss',
        array($user_id, miq_account_hash_token($token), $expires_at)
    )->close();
    return $token;
}

function miq_account_google_identity($credential)
{
    $config = miq_account_config();
    if ($config['google_client_id'] === '') {
        throw new RuntimeException('Google login is not configured yet.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Google login requires the PHP cURL extension.');
    }

    $curl = curl_init($config['google_tokeninfo_url'] . '?id_token=' . rawurlencode($credential));
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ));
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $claims = json_decode((string) $response, true);

    if ($status !== 200 || !is_array($claims)) {
        throw new RuntimeException('Google login could not be verified.');
    }
    if (!hash_equals($config['google_client_id'], (string) ($claims['aud'] ?? '')) || (int) ($claims['exp'] ?? 0) < time()) {
        throw new RuntimeException('Google login token has expired or belongs to another application.');
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
        "SELECT u.id, u.email, u.display_name, u.role, u.email_verified_at, u.status, u.session_version FROM {$users} u INNER JOIN {$identities} i ON i.user_id = u.id WHERE i.provider = 'google' AND i.provider_user_id = ? LIMIT 1",
        's',
        array((string) $provider_user_id)
    ));
}

function miq_account_find_user_by_email($email)
{
    $users = miq_account_table('users');
    return miq_account_fetch_one(miq_account_query(
        "SELECT id, email, password_hash, display_name, role, status, email_verified_at, session_version FROM {$users} WHERE email = ? LIMIT 1",
        's',
        array(miq_account_normalize_email($email))
    ));
}
