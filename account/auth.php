<?php
require_once __DIR__ . '/db.php';

function miq_account_session_cookie_name()
{
    return (string) miq_account_config()['cookie_name'];
}

function miq_account_request_is_secure()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}

function miq_account_state_cookie_name($kind)
{
    return miq_account_session_cookie_name() . '_' . (string) $kind;
}

function miq_account_set_state_cookie($name, $value, $expires)
{
    return setcookie((string) $name, (string) $value, array(
        'expires' => (int) $expires,
        'path' => '/',
        'secure' => miq_account_request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ));
}

function miq_account_clear_state_cookie($name)
{
    miq_account_set_state_cookie($name, '', time() - 42000);
    unset($_COOKIE[$name]);
}

function miq_account_state_secret_file($config)
{
    $session_path = miq_account_private_session_path($config);
    if ($session_path === '') {
        return '';
    }

    $parent = realpath(dirname($session_path));
    if ($parent === false || is_link($parent) || !is_readable($parent) || !is_writable($parent)) {
        return '';
    }

    $document_root_setting = trim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $document_root = realpath($document_root_setting === '' ? dirname(__DIR__) : $document_root_setting);
    if ($document_root !== false && miq_account_session_path_is_within($parent, $document_root)) {
        return '';
    }

    return $parent . DIRECTORY_SEPARATOR . '.miq_account_state_secret';
}

function miq_account_state_secret()
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }

    $config = miq_account_config();
    $configured = trim((string) ($config['state_secret'] ?? ''));
    if ($configured !== '') {
        if (strlen($configured) < 32) {
            error_log('360MiQ account state secret must contain at least 32 characters.');
        } else {
            $secret = $configured;
            return $secret;
        }
    }

    $secret_file = miq_account_state_secret_file($config);
    if ($secret_file === '' || is_link($secret_file)) {
        $secret = '';
        return $secret;
    }

    $existing = @file_get_contents($secret_file);
    if (is_string($existing) && strlen($existing) >= 32) {
        if (DIRECTORY_SEPARATOR === '/') {
            @chmod($secret_file, 0600);
        }
        $secret = substr($existing, 0, 64);
        return $secret;
    }

    try {
        $generated = random_bytes(32);
        $temporary = $secret_file . '.tmp.' . bin2hex(random_bytes(8));
    } catch (Throwable $error) {
        $secret = '';
        return $secret;
    }

    $output = @fopen($temporary, 'x+b');
    if ($output !== false) {
        $written = fwrite($output, $generated);
        $flushed = fflush($output);
        fclose($output);
        if ($written === strlen($generated) && $flushed) {
            if (DIRECTORY_SEPARATOR === '/') {
                @chmod($temporary, 0600);
            }
            // Publish without replacing a secret another first request may
            // have created concurrently. This keeps already-issued tokens
            // valid during the initial deployment race.
            if (!@link($temporary, $secret_file) && !file_exists($secret_file)) {
                @rename($temporary, $secret_file);
            }
        }
        @unlink($temporary);
    }

    $existing = @file_get_contents($secret_file);
    if (is_string($existing) && strlen($existing) >= 32) {
        if (DIRECTORY_SEPARATOR === '/') {
            @chmod($secret_file, 0600);
        }
        $secret = substr($existing, 0, 64);
        return $secret;
    }

    $secret = '';
    return $secret;
}

function miq_account_base64url_encode($value)
{
    return rtrim(strtr(base64_encode((string) $value), '+/', '-_'), '=');
}

function miq_account_base64url_decode($value)
{
    $value = (string) $value;
    if ($value === '' || strlen($value) > 8192 || !preg_match('/^[A-Za-z0-9_-]+$/D', $value)) {
        return false;
    }

    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($value, '-_', '+/'), true);
}

function miq_account_signed_state($purpose, $claims, $lifetime)
{
    $secret = miq_account_state_secret();
    if ($secret === '') {
        return '';
    }

    $payload = array(
        'version' => 1,
        'purpose' => (string) $purpose,
        'expires_at' => time() + max(1, (int) $lifetime),
        'nonce' => bin2hex(random_bytes(32)),
    );
    foreach ((array) $claims as $key => $value) {
        $key = (string) $key;
        if (in_array($key, array('version', 'purpose', 'expires_at', 'nonce'), true)) {
            continue;
        }
        $payload[$key] = $value;
    }

    $encoded = miq_account_base64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
    if ($encoded === '') {
        return '';
    }
    return $encoded . '.' . hash_hmac('sha256', $encoded, $secret);
}

function miq_account_verify_signed_state($token, $purpose)
{
    $token = trim((string) $token);
    if ($token === '' || strlen($token) > 8192) {
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{64}$/D', $parts[1])) {
        return null;
    }
    $secret = miq_account_state_secret();
    if ($secret === '' || !hash_equals(hash_hmac('sha256', $parts[0], $secret), $parts[1])) {
        return null;
    }

    $decoded = miq_account_base64url_decode($parts[0]);
    $payload = $decoded === false ? null : json_decode($decoded, true);
    if (!is_array($payload)
        || (int) ($payload['version'] ?? 0) !== 1
        || (string) ($payload['purpose'] ?? '') !== (string) $purpose
        || (int) ($payload['expires_at'] ?? 0) < time()
        || !preg_match('/^[a-f0-9]{64}$/D', (string) ($payload['nonce'] ?? ''))
    ) {
        return null;
    }

    return $payload;
}

function miq_account_stateless_csrf_token($create = true)
{
    $cookie_name = miq_account_state_cookie_name('csrf');
    $existing = isset($_COOKIE[$cookie_name]) ? (string) $_COOKIE[$cookie_name] : '';
    if ($existing !== '' && miq_account_verify_signed_state($existing, 'csrf') !== null) {
        return $existing;
    }
    if (!$create) {
        return '';
    }

    $token = miq_account_signed_state('csrf', array(), (int) miq_account_config()['session_lifetime']);
    if ($token !== '') {
        miq_account_set_state_cookie($cookie_name, $token, time() + (int) miq_account_config()['session_lifetime']);
        $_COOKIE[$cookie_name] = $token;
    }
    return $token;
}

function miq_account_has_session_cookie()
{
    $name = miq_account_session_cookie_name();
    return isset($_COOKIE[$name]) && (string) $_COOKIE[$name] !== '';
}

function miq_account_normalize_session_path($path)
{
    $normalized = str_replace('\\', '/', rtrim((string) $path, "\\/"));
    if (DIRECTORY_SEPARATOR === '\\') {
        $normalized = strtolower($normalized);
    }
    return $normalized;
}

function miq_account_session_path_is_within($path, $directory)
{
    $path = miq_account_normalize_session_path($path);
    $directory = miq_account_normalize_session_path($directory);
    return $path !== ''
        && $directory !== ''
        && ($path === $directory || strpos($path, $directory . '/') === 0);
}

function miq_account_private_session_path($config)
{
    $configured = trim((string) ($config['session_save_path'] ?? ''));
    if ($configured !== '') {
        return $configured;
    }

    // Production already keeps its database include in a private php_script
    // directory. Reuse that private root without imposing a host-specific path
    // on local or alternate deployments; those can set the environment value.
    $database_include = trim((string) ($config['account_db_include'] ?? ''));
    $private_root = $database_include === '' ? '' : dirname($database_include);
    if ($private_root === ''
        || strtolower(basename(str_replace('\\', '/', $private_root))) !== 'php_script'
        || !is_dir($private_root)
    ) {
        return '';
    }

    return $private_root . DIRECTORY_SEPARATOR . 'account_sessions';
}

function miq_account_prepare_private_session_path($config)
{
    $path = miq_account_private_session_path($config);
    if ($path === '') {
        return '';
    }
    if (strpos($path, "\0") !== false || !preg_match('#^(?:[a-zA-Z]:[\\\\/]|/)#', $path)) {
        error_log('360MiQ private session path must be an absolute filesystem path.');
        return '';
    }

    $parent = realpath(dirname($path));
    $leaf = basename($path);
    if ($parent === false || $leaf === '' || $leaf === '.' || $leaf === '..') {
        error_log('360MiQ private session path has no accessible parent directory.');
        return '';
    }

    $candidate = $parent . DIRECTORY_SEPARATOR . $leaf;
    $document_root_setting = trim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $document_root = realpath($document_root_setting === '' ? dirname(__DIR__) : $document_root_setting);
    if ($document_root !== false && miq_account_session_path_is_within($candidate, $document_root)) {
        error_log('360MiQ refused to store account sessions inside the public document root.');
        return '';
    }
    if (is_link($candidate)) {
        error_log('360MiQ refused to use a symbolic link for private account sessions.');
        return '';
    }
    if (!is_dir($candidate) && !@mkdir($candidate, 0700) && !is_dir($candidate)) {
        error_log('360MiQ could not create the private account session directory.');
        return '';
    }

    $resolved = realpath($candidate);
    if ($resolved === false
        || is_link($candidate)
        || ($document_root !== false && miq_account_session_path_is_within($resolved, $document_root))
        || !is_readable($resolved)
        || !is_writable($resolved)
    ) {
        error_log('360MiQ private account session directory is not safe and writable.');
        return '';
    }

    if (DIRECTORY_SEPARATOR === '/') {
        @chmod($resolved, 0700);
        clearstatcache(true, $resolved);
        $permissions = @fileperms($resolved);
        if ($permissions === false || ($permissions & 0077) !== 0) {
            error_log('360MiQ private account session directory must use owner-only permissions.');
            return '';
        }
    }

    return $resolved;
}

function miq_account_file_session_location($save_path, $session_id)
{
    $parts = explode(';', trim((string) $save_path));
    $directory = trim((string) end($parts));
    if ($directory === '') {
        return '';
    }

    $depth = count($parts) > 1 && ctype_digit($parts[0]) ? (int) $parts[0] : 0;
    for ($index = 0; $index < $depth; $index++) {
        if (!isset($session_id[$index])) {
            return '';
        }
        $directory .= DIRECTORY_SEPARATOR . $session_id[$index];
    }
    return $directory . DIRECTORY_SEPARATOR . 'sess_' . $session_id;
}

function miq_account_migrate_session_file($old_save_path, $new_save_path, $cookie_name)
{
    $session_id = isset($_COOKIE[$cookie_name]) ? (string) $_COOKIE[$cookie_name] : '';
    if (!preg_match('/^[a-zA-Z0-9,-]{16,256}$/D', $session_id)) {
        return;
    }

    $source = miq_account_file_session_location($old_save_path, $session_id);
    $destination = $new_save_path . DIRECTORY_SEPARATOR . 'sess_' . $session_id;
    if ($source === ''
        || miq_account_normalize_session_path(dirname($source)) === miq_account_normalize_session_path($new_save_path)
        || !is_file($source)
        || is_link($source)
        || file_exists($destination)
    ) {
        return;
    }

    $input = @fopen($source, 'rb');
    if ($input === false) {
        return;
    }

    // PHP's file handler holds an exclusive lock while another request owns
    // the session. A shared lock prevents migrating a partially written file.
    if (!flock($input, LOCK_SH)) {
        fclose($input);
        return;
    }

    try {
        $temporary = $destination . '.migrate.' . bin2hex(random_bytes(8));
    } catch (Throwable $error) {
        flock($input, LOCK_UN);
        fclose($input);
        return;
    }
    $output = @fopen($temporary, 'x+b');
    if ($output === false) {
        flock($input, LOCK_UN);
        fclose($input);
        return;
    }

    $copied = stream_copy_to_stream($input, $output);
    $flushed = fflush($output);
    flock($input, LOCK_UN);
    fclose($input);
    fclose($output);
    if ($copied === false || !$flushed) {
        @unlink($temporary);
        return;
    }
    if (DIRECTORY_SEPARATOR === '/') {
        @chmod($temporary, 0600);
    }

    // A hard link publishes the fully copied file only if another request has
    // not already completed the same migration. Keep rename as a same-folder
    // fallback for hosts that disable hard links.
    if (!@link($temporary, $destination) && !file_exists($destination)) {
        @rename($temporary, $destination);
    }
    @unlink($temporary);
}

function miq_account_use_private_session_storage($config)
{
    if (strtolower((string) ini_get('session.save_handler')) !== 'files') {
        return false;
    }

    $private_path = miq_account_prepare_private_session_path($config);
    if ($private_path === '') {
        return false;
    }

    $old_save_path = (string) ini_get('session.save_path');
    miq_account_migrate_session_file($old_save_path, $private_path, (string) $config['cookie_name']);
    if (ini_set('session.save_path', $private_path) === false) {
        error_log('360MiQ could not activate private account session storage.');
        return false;
    }

    // cPanel does not clean custom session directories. Let PHP collect only
    // this application's files using the same lifetime as the login cookie.
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    return true;
}

function miq_account_start_session($allow_new = false)
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    $config = miq_account_config();
    $secure = miq_account_request_is_secure();

    // Do not let arbitrary cookies force PHP to create a new file-backed
    // session. Only an explicit state-changing flow (login) may create a new
    // session. Validate the cookie before doing any migration work.
    $session_id = '';
    if (!$allow_new) {
        if (!miq_account_has_session_cookie()) {
            return false;
        }
        $session_id = (string) $_COOKIE[miq_account_session_cookie_name()];
        if (!preg_match('/^[a-zA-Z0-9,-]{16,256}$/D', $session_id)) {
            return false;
        }
    }

    ini_set('session.gc_maxlifetime', (string) $config['session_lifetime']);
    ini_set('session.use_strict_mode', '1');
    miq_account_use_private_session_storage($config);

    if (!$allow_new && strtolower((string) ini_get('session.save_handler')) === 'files') {
        $session_file = miq_account_file_session_location((string) ini_get('session.save_path'), $session_id);
        if ($session_file === '' || !is_file($session_file) || is_link($session_file)) {
            return false;
        }
    }

    session_name($config['cookie_name']);
    session_set_cookie_params(array(
        'lifetime' => $config['session_lifetime'],
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    return session_start();
}

function miq_account_bootstrap()
{
    if (!miq_account_start_session()) {
        return false;
    }
    if (!isset($_SESSION['miq_account_session_version'])) {
        $_SESSION['miq_account_session_version'] = 1;
    }
    return true;
}

function miq_account_stateless_flash_payload($token)
{
    $payload = miq_account_verify_signed_state($token, 'flash');
    if (!is_array($payload)) {
        return null;
    }

    $type = (string) ($payload['type'] ?? '');
    $message = (string) ($payload['message'] ?? '');
    if (!in_array($type, array('danger', 'success', 'warning', 'info'), true) || $message === '') {
        return null;
    }
    return array('type' => $type, 'message' => substr($message, 0, 2000));
}

function miq_account_take_stateless_flash()
{
    $cookie_name = miq_account_state_cookie_name('flash');
    $token = isset($_COOKIE[$cookie_name]) ? (string) $_COOKIE[$cookie_name] : '';
    if ($token === '') {
        return null;
    }
    miq_account_clear_state_cookie($cookie_name);
    return miq_account_stateless_flash_payload($token);
}

function miq_account_session_hash()
{
    if (!miq_account_start_session()) {
        return '';
    }
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
    if (!miq_account_start_session()) {
        return;
    }
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
    if (!miq_account_bootstrap()) {
        return null;
    }
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

function miq_account_retire_current_notification_session()
{
    $notification_user_id = (int) ($_SESSION['miq_account_user_id'] ?? 0);
    $notification_session_hash = miq_account_session_hash();
    if ($notification_user_id > 0 && function_exists('miq_account_unregister_notification_session')) {
        try {
            miq_account_unregister_notification_session($notification_user_id, $notification_session_hash);
        } catch (Throwable $error) {
            error_log('360MiQ notification session cleanup failure: ' . $error->getMessage());
        }
    }
}

function miq_account_logout($destroy_session = true)
{
    if (!miq_account_start_session()) {
        miq_account_clear_state_cookie(miq_account_state_cookie_name('csrf'));
        miq_account_clear_state_cookie(miq_account_state_cookie_name('flash'));
        miq_account_clear_state_cookie(miq_account_state_cookie_name('google'));
        return;
    }
    miq_account_retire_current_notification_session();
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
    miq_account_clear_state_cookie(miq_account_state_cookie_name('csrf'));
    miq_account_clear_state_cookie(miq_account_state_cookie_name('flash'));
    miq_account_clear_state_cookie(miq_account_state_cookie_name('google'));
}

function miq_account_login_user($user_id, $session_version, $record_login = true)
{
    if (!miq_account_start_session(true)) {
        throw new RuntimeException('Unable to start the account session.');
    }
    miq_account_retire_current_notification_session();
    miq_account_remove_current_session();
    session_regenerate_id(true);
    $_SESSION['miq_account_user_id'] = (int) $user_id;
    $_SESSION['miq_account_session_version'] = (int) $session_version;
    unset($_SESSION['miq_account_last_activity_write']);
    miq_account_clear_state_cookie(miq_account_state_cookie_name('csrf'));
    miq_account_clear_state_cookie(miq_account_state_cookie_name('flash'));
    miq_account_clear_state_cookie(miq_account_state_cookie_name('google'));
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
    if (session_status() !== PHP_SESSION_ACTIVE && miq_account_has_session_cookie()) {
        miq_account_start_session();
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        if (empty($_SESSION['miq_account_csrf'])) {
            $_SESSION['miq_account_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['miq_account_csrf'];
    }

    $token = miq_account_stateless_csrf_token(true);
    if ($token !== '') {
        return $token;
    }

    // Fail closed if the private signing secret is unavailable. Creating a
    // fallback anonymous session here would recreate the file explosion this
    // token is intended to prevent.
    return '';
}

function miq_account_check_csrf($token)
{
    if (!is_string($token)) {
        return false;
    }
    if (session_status() !== PHP_SESSION_ACTIVE && miq_account_has_session_cookie()) {
        return hash_equals(miq_account_csrf_token(), $token);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        return hash_equals(miq_account_csrf_token(), $token);
    }

    $expected = miq_account_stateless_csrf_token(false);
    return $expected !== '' && hash_equals($expected, $token);
}

function miq_account_issue_native_google_challenge($return_to = 'workspace')
{
    if (session_status() !== PHP_SESSION_ACTIVE && miq_account_has_session_cookie()) {
        miq_account_start_session();
    }

    $nonce = bin2hex(random_bytes(32));
    $safe_return_to = miq_account_safe_return_to($return_to, 'workspace');
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['miq_native_google_challenge'] = array(
            'nonce_hash' => hash('sha256', $nonce),
            'return_to' => $safe_return_to,
            'expires_at' => time() + (int) miq_account_config()['native_google_challenge_ttl'],
        );
        return $nonce;
    }

    $lifetime = (int) miq_account_config()['native_google_challenge_ttl'];
    $state = miq_account_signed_state('native_google', array('return_to' => $safe_return_to), $lifetime);
    if ($state !== '') {
        $cookie_name = miq_account_state_cookie_name('google');
        miq_account_set_state_cookie($cookie_name, $state, time() + $lifetime);
        $_COOKIE[$cookie_name] = $state;
        return $state;
    }

    // Fail closed if the private signing secret is unavailable. Creating a
    // fallback anonymous session here would recreate the file explosion this
    // challenge is intended to prevent.
    return '';
}

function miq_account_consume_native_google_challenge($nonce)
{
    $state = trim((string) $nonce);
    $signed_claims = miq_account_verify_signed_state($state, 'native_google');
    if (is_array($signed_claims)) {
        $cookie_name = miq_account_state_cookie_name('google');
        $cookie_value = isset($_COOKIE[$cookie_name]) ? (string) $_COOKIE[$cookie_name] : '';
        if ($cookie_value === '' || !hash_equals($cookie_value, $state)) {
            throw new RuntimeException('The Android Google sign-in request expired. Reload the account page and try again.');
        }
        // The cookie makes this signed challenge one-time without a server
        // row. Clear it before verifying the Google credential, just like the
        // old session-backed challenge was consumed before verification.
        miq_account_clear_state_cookie($cookie_name);
        return array(
            'nonce' => (string) $signed_claims['nonce'],
            'return_to' => miq_account_safe_return_to($signed_claims['return_to'] ?? 'workspace', 'workspace'),
        );
    }

    if (!miq_account_has_session_cookie()) {
        throw new RuntimeException('The Android Google sign-in request expired. Reload the account page and try again.');
    }
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
    if (session_status() !== PHP_SESSION_ACTIVE && miq_account_has_session_cookie()) {
        miq_account_start_session();
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['miq_account_flash'] = array('type' => $type, 'message' => $message);
        return;
    }

    $type = in_array($type, array('danger', 'success', 'warning', 'info'), true) ? $type : 'info';
    $message = substr(trim((string) $message), 0, 2000);
    $token = miq_account_signed_state('flash', array('type' => $type, 'message' => $message), 600);
    if ($token !== '') {
        $cookie_name = miq_account_state_cookie_name('flash');
        miq_account_set_state_cookie($cookie_name, $token, time() + 600);
        $_COOKIE[$cookie_name] = $token;
        return;
    }

    // Do not create an anonymous PHP session when signed state is unavailable;
    // the caller can still complete the request, but the transient message is
    // intentionally dropped rather than recreating thousands of session files.
}

function miq_account_take_flash()
{
    if (session_status() !== PHP_SESSION_ACTIVE && miq_account_has_session_cookie()) {
        miq_account_start_session();
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $flash = isset($_SESSION['miq_account_flash']) ? $_SESSION['miq_account_flash'] : null;
        unset($_SESSION['miq_account_flash']);
        if ($flash !== null) {
            return $flash;
        }
    }

    return miq_account_take_stateless_flash();
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

function miq_account_find_user_by_id($user_id)
{
    $users = miq_account_table('users');
    return miq_account_fetch_one(miq_account_query(
        "SELECT id, email, password_hash, display_name, avatar_url, role, status, email_verified_at, session_version, suspended_at, suspended_until, suspension_reason, suspended_by_user_id FROM {$users} WHERE id = ? LIMIT 1",
        'i',
        array((int) $user_id)
    ));
}
