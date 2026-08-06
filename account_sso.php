<?php
require_once __DIR__ . '/account/bootstrap.php';

function miq_sso_json($payload, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function miq_sso_shared_secret()
{
    return (string) miq_account_env('MIQ_SSO_SHARED_SECRET', '');
}

function miq_sso_issuer()
{
    $script_name = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    return preg_match('#^/full(?:/|$)#', $script_name) ? 'full' : 'production';
}

function miq_sso_issuer_signature($issuer, $token)
{
    return hash_hmac('sha256', (string) $issuer . "\n" . (string) $token, miq_sso_shared_secret());
}

function miq_sso_targets()
{
    return array(
        'posts' => '/blog/wp-admin/edit.php',
        'new-post' => '/blog/wp-admin/post-new.php',
    );
}

function miq_sso_target()
{
    $targets = miq_sso_targets();
    $target = isset($_GET['target']) ? (string) $_GET['target'] : '';
    if (isset($targets[$target])) {
        return array('key' => $target, 'return_to' => $targets[$target]);
    }

    // Accept the old return_to links while they are being replaced, but convert
    // them to an allowlisted identifier before sending a user through sign-in.
    if (isset($_GET['return_to'])) {
        $return_to = miq_account_safe_return_to($_GET['return_to'], $targets['posts']);
        foreach ($targets as $key => $path) {
            if ($return_to === $path) {
                return array('key' => $key, 'return_to' => $path);
            }
        }
    }

    return array('key' => 'posts', 'return_to' => $targets['posts']);
}

function miq_sso_browser_error($message, $status = 503)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    $is_rate_limited = (int) $status === 429;
    $safe_message = htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8');
    $page_title = $is_rate_limited ? 'Article Editor temporarily paused' : 'Article Editor unavailable';
    $heading = $is_rate_limited ? "Let's pause for a moment" : 'We could not open the Article Editor';
    $kicker = $is_rate_limited ? 'Security pause' : 'Article Editor';
    $note = $is_rate_limited
        ? 'Your 360MiQ account is still signed in. You can return to your workspace while this handoff resets.'
        : 'Your 360MiQ account is still signed in. Please return to the workspace and try again later.';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta name="description" content="360MiQ Article Editor handoff status." />
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?> - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/account.css?v=20260806.3">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
</head>
<body>
<?php $page = 'account'; include __DIR__ . '/header.php'; ?>
<main class="miq-account-page miq-sso-error-page">
    <section class="miq-account-card miq-sso-error-card" role="alert" aria-live="polite">
        <div class="miq-sso-error-icon" aria-hidden="true"><i class="fas fa-pencil-alt"></i></div>
        <div class="miq-account-intro">
            <span class="miq-account-kicker"><?php echo htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8'); ?></span>
            <h1><?php echo htmlspecialchars($heading, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="miq-sso-error-message"><?php echo $safe_message; ?></p>
        </div>
        <div class="miq-sso-error-note"><i class="fas fa-info-circle" aria-hidden="true"></i><span><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></span></div>
        <div class="miq-sso-error-actions">
            <a class="btn btn-primary" href="workspace">Back to workspace</a>
            <a class="btn btn-outline-primary" href="./">Return home</a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php
    exit;
}

function miq_sso_begin()
{
    $user = miq_account_current_user();
    $target = miq_sso_target();
    if (!$user) {
        $view = isset($_GET['signup']) && $_GET['signup'] === '1' ? 'register' : 'login';
        $handoff = 'account_sso.php?target=' . rawurlencode($target['key']);
        header('Location: account.php?view=' . $view . '&return_to=' . rawurlencode($handoff));
        exit;
    }

    if (miq_sso_shared_secret() === '') {
        miq_sso_browser_error('Write for Us sign-in is temporarily unavailable because WordPress SSO is not configured.');
    }

    $limits = miq_account_config()['rate_limits'];
    $sso_limit = $limits['sso_user'];
    if (!miq_account_rate_limit('sso_user', (string) $user['id'], $sso_limit['limit'], $sso_limit['window'])) {
        miq_sso_browser_error('Article Editor sign-in is temporarily paused after too many recent attempts. Please wait up to one hour before trying again.', 429);
    }

    $token = miq_account_create_token();
    $tokens = miq_account_table('sso_tokens');
    $expires_at = gmdate('Y-m-d H:i:s', time() + 300);
    miq_account_query(
        "DELETE FROM {$tokens} WHERE user_id = ? AND consumed_at IS NULL",
        'i',
        array((int) $user['id'])
    )->close();
    miq_account_query(
        "INSERT INTO {$tokens} (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())",
        'iss',
        array((int) $user['id'], miq_account_hash_token($token), $expires_at)
    )->close();
    header('Cache-Control: no-store');
    header('Referrer-Policy: no-referrer');
    $issuer = miq_sso_issuer();
    $wordpress_query = http_build_query(array(
        'miq_sso' => '1',
        'token' => $token,
        'issuer' => $issuer,
        'issuer_sig' => miq_sso_issuer_signature($issuer, $token),
        'return_to' => $target['return_to'],
    ), '', '&', PHP_QUERY_RFC3986);
    header('Location: /blog/?' . $wordpress_query);
    exit;
}

if (isset($_GET['mode']) && $_GET['mode'] === 'consume') {
    $provided_secret = isset($_SERVER['HTTP_X_MIQ_SSO_SECRET']) ? $_SERVER['HTTP_X_MIQ_SSO_SECRET'] : '';
    if ($provided_secret === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authorization = trim((string) $_SERVER['HTTP_AUTHORIZATION']);
        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            $provided_secret = trim($matches[1]);
        }
    }
    if (miq_sso_shared_secret() === '' || !hash_equals(miq_sso_shared_secret(), (string) $provided_secret)) {
        miq_sso_json(array('error' => 'SSO is not configured.'), 403);
    }

    $token = (string) ($_POST['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{32,128}$/i', $token)) {
        miq_sso_json(array('error' => 'Invalid SSO token.'), 422);
    }

    try {
        $db = miq_account_db();
        $tokens = miq_account_table('sso_tokens');
        $users = miq_account_table('users');
        $db->begin_transaction();
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT t.id AS token_id, u.id, u.email, u.display_name, u.avatar_url FROM {$tokens} t INNER JOIN {$users} u ON u.id = t.user_id WHERE t.token_hash = ? AND t.consumed_at IS NULL AND t.expires_at >= UTC_TIMESTAMP() AND u.status = 'active' LIMIT 1 FOR UPDATE",
            's',
            array(miq_account_hash_token($token))
        ));
        if (!$row) {
            $db->rollback();
            miq_sso_json(array('error' => 'SSO token is invalid or expired.'), 401);
        }
        miq_account_query("UPDATE {$tokens} SET consumed_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array((int) $row['token_id']))->close();
        $db->commit();
        unset($row['token_id']);
        miq_sso_json(array('user' => $row));
    } catch (Throwable $error) {
        if (isset($db) && $db instanceof mysqli) $db->rollback();
        miq_sso_json(array('error' => 'SSO handoff failed.'), 500);
    }
}

miq_sso_begin();
