<?php
require_once __DIR__ . '/account/bootstrap.php';

$view = isset($_GET['view']) ? (string) $_GET['view'] : 'login';
$return_to = miq_account_safe_return_to(isset($_GET['return_to']) ? $_GET['return_to'] : (isset($_POST['return_to']) ? $_POST['return_to'] : '/'));
$errors = array();

function miq_account_redirect($path)
{
    header('Location: ' . $path, true, 302);
    exit;
}

function miq_account_token_link($kind, $token)
{
    $query = $kind === 'verify' ? 'verify=' : 'reset=';
    return miq_account_config()['base_url'] . '/account?' . $query . rawurlencode($token);
}

function miq_account_process_verification($token)
{
    if (!preg_match('/^[a-f0-9]{32,128}$/i', (string) $token)) {
        miq_account_flash('danger', 'That verification link is invalid or has expired.');
        return;
    }

    $tokens = miq_account_table('email_tokens');
    $users = miq_account_table('users');
    $row = miq_account_fetch_one(miq_account_query(
        "SELECT t.id AS token_id, u.id AS user_id FROM {$tokens} t INNER JOIN {$users} u ON u.id = t.user_id WHERE t.token_hash = ? AND t.expires_at >= UTC_TIMESTAMP() LIMIT 1",
        's',
        array(miq_account_hash_token($token))
    ));
    if (!$row) {
        miq_account_flash('danger', 'That verification link is invalid or has expired.');
        return;
    }

    miq_account_query("UPDATE {$users} SET email_verified_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array((int) $row['user_id']))->close();
    miq_account_query("DELETE FROM {$tokens} WHERE id = ?", 'i', array((int) $row['token_id']))->close();
    miq_account_flash('success', 'Your email is verified. You can now sign in.');
}

function miq_account_process_email_registration($email, $password, $display_name)
{
    if (strlen($password) < 8) {
        throw new InvalidArgumentException('Use a password with at least 8 characters.');
    }

    $user_id = miq_account_create_user($email, $password, $display_name, 'email', null);
    $token = miq_account_issue_email_token($user_id, 'verify');
    $user = miq_account_find_user_by_email($email);
    $link = miq_account_token_link('verify', $token);
    $sent = $user && miq_account_send_mail(
        $user['email'],
        'Verify your 360MiQ account',
        "Welcome to 360MiQ.\n\nVerify your email address by opening this link:\n{$link}\n\nThis link expires in 24 hours.\n"
    );
    if (!$sent && miq_account_config()['debug']) {
        error_log('360MiQ verification link: ' . $link);
    }
    return $sent;
}

function miq_account_process_google_login($credential)
{
    $identity = miq_account_google_identity($credential);
    $user = miq_account_find_google_user($identity['provider_user_id']);

    if (!$user) {
        $existing = miq_account_find_user_by_email($identity['email']);
        if ($existing) {
            throw new RuntimeException('An account already exists for this email. Sign in with email first, then connect Google from your account settings.');
        }

        $user_id = miq_account_create_user($identity['email'], null, $identity['display_name'], 'google', $identity['provider_user_id']);
        $users = miq_account_table('users');
        miq_account_query(
            "UPDATE {$users} SET avatar_url = ?, email_verified_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ?",
            'si',
            array($identity['avatar_url'], $user_id)
        )->close();
        $user = miq_account_find_google_user($identity['provider_user_id']);
    }

    if (!$user || $user['status'] !== 'active') {
        throw new RuntimeException('This account is not available.');
    }

    miq_account_login_user($user['id'], $user['session_version']);
}

if (isset($_GET['verify'])) {
    try {
        miq_account_process_verification($_GET['verify']);
    } catch (Throwable $error) {
        miq_account_flash('danger', 'We could not verify that link. Please request a new one.');
    }
    miq_account_redirect('/account?view=login');
}

if (isset($_GET['reset'])) {
    $view = 'reset';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!miq_account_check_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Your session token expired. Refresh the page and try again.';
    } else {
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
        try {
            if ($action === 'register') {
                $email = miq_account_normalize_email($_POST['email'] ?? '');
                miq_account_process_email_registration($email, (string) ($_POST['password'] ?? ''), (string) ($_POST['display_name'] ?? ''));
                miq_account_flash('success', 'Account created. Check your email to verify your address before signing in.');
                miq_account_redirect('/account?view=login');
            } elseif ($action === 'login') {
                $user = miq_account_find_user_by_email($_POST['email'] ?? '');
                if (!$user || !$user['password_hash'] || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
                    throw new RuntimeException('The email or password is not correct.');
                }
                if ($user['status'] !== 'active') {
                    throw new RuntimeException('This account is not available.');
                }
                if (empty($user['email_verified_at'])) {
                    throw new RuntimeException('Please verify your email before signing in.');
                }
                miq_account_login_user($user['id'], $user['session_version']);
                miq_account_redirect($return_to);
            } elseif ($action === 'google') {
                miq_account_process_google_login((string) ($_POST['credential'] ?? ''));
                miq_account_redirect($return_to);
            } elseif ($action === 'request_reset') {
                $email = miq_account_normalize_email($_POST['email'] ?? '');
                $user = miq_account_find_user_by_email($email);
                if ($user && $user['password_hash']) {
                    $token = miq_account_issue_email_token($user['id'], 'reset');
                    $link = miq_account_token_link('reset', $token);
                    $sent = miq_account_send_mail($user['email'], 'Reset your 360MiQ password', "Open this link to reset your password:\n{$link}\n\nThis link expires in one hour.\n");
                    if (!$sent && miq_account_config()['debug']) {
                        error_log('360MiQ reset link: ' . $link);
                    }
                }
                miq_account_flash('success', 'If an account exists for that email, a reset link has been sent.');
                miq_account_redirect('/account?view=login');
            } elseif ($action === 'reset_password') {
                $token = (string) ($_POST['token'] ?? '');
                $password = (string) ($_POST['password'] ?? '');
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('Use a password with at least 8 characters.');
                }
                $tokens = miq_account_table('password_reset_tokens');
                $users = miq_account_table('users');
                $row = miq_account_fetch_one(miq_account_query(
                    "SELECT t.id AS token_id, u.id AS user_id FROM {$tokens} t INNER JOIN {$users} u ON u.id = t.user_id WHERE t.token_hash = ? AND t.expires_at >= UTC_TIMESTAMP() LIMIT 1",
                    's',
                    array(miq_account_hash_token($token))
                ));
                if (!$row) {
                    throw new RuntimeException('That reset link is invalid or has expired.');
                }
                $hash = password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
                miq_account_query("UPDATE {$users} SET password_hash = ?, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'si', array($hash, (int) $row['user_id']))->close();
                miq_account_query("DELETE FROM {$tokens} WHERE id = ?", 'i', array((int) $row['token_id']))->close();
                miq_account_flash('success', 'Your password was changed. You can now sign in.');
                miq_account_redirect('/account?view=login');
            }
        } catch (Throwable $error) {
            $errors[] = miq_account_config()['debug'] ? $error->getMessage() : $error->getMessage();
        }
    }
}

$flash = miq_account_take_flash();
$current_user = miq_account_current_user();
if ($current_user && $view !== 'reset') {
    miq_account_redirect($return_to === '/' ? '/workspace' : $return_to);
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta property="og:title" content="Account - 360MiQ.com" />
    <meta name="description" content="Sign in to save charts, Pine scripts, watchlists, and community ideas on 360MiQ.com." />
    <title>Account - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css">
    <?php if (miq_account_config()['google_client_id'] !== ''): ?>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php endif; ?>
</head>
<body>
<?php $page = 'account'; include __DIR__ . '/header.php'; ?>
<main class="miq-account-page">
    <section class="miq-account-card container">
        <div class="miq-account-intro">
            <span class="miq-account-kicker">360MiQ Workspace</span>
            <h1><?php echo $view === 'register' ? 'Create your account' : ($view === 'reset' ? 'Reset your password' : 'Sign in to your workspace'); ?></h1>
            <p>Save charts, Pine scripts, searches, watchlists, and community ideas across devices.</p>
        </div>

        <?php if ($flash): ?><div class="alert alert-<?php echo htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endforeach; ?>

        <?php if ($view === 'register'): ?>
            <form method="post" class="miq-account-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="display_name">Display name</label>
                <input id="display_name" name="display_name" class="form-control" maxlength="80" autocomplete="name" required>
                <label for="register_email">Email</label>
                <input id="register_email" name="email" class="form-control" type="email" autocomplete="email" required>
                <label for="register_password">Password</label>
                <input id="register_password" name="password" class="form-control" type="password" minlength="8" autocomplete="new-password" required>
                <button class="btn btn-primary btn-block" type="submit">Create account</button>
            </form>
            <p class="miq-account-switch">Already have an account? <a href="/account?view=login">Sign in</a></p>
        <?php elseif ($view === 'reset'): ?>
            <form method="post" class="miq-account-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['reset'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <label for="reset_password">New password</label>
                <input id="reset_password" name="password" class="form-control" type="password" minlength="8" autocomplete="new-password" required>
                <button class="btn btn-primary btn-block" type="submit">Change password</button>
            </form>
        <?php else: ?>
            <form method="post" class="miq-account-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="login_email">Email</label>
                <input id="login_email" name="email" class="form-control" type="email" autocomplete="email" required>
                <label for="login_password">Password</label>
                <input id="login_password" name="password" class="form-control" type="password" autocomplete="current-password" required>
                <button class="btn btn-primary btn-block" type="submit">Sign in</button>
            </form>
            <div class="miq-account-divider"><span>or</span></div>
            <?php if (miq_account_config()['google_client_id'] !== ''): ?>
                <form method="post" id="google-login-form" class="miq-google-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="google">
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="credential" id="google-credential">
                    <div id="g_id_onload" data-client_id="<?php echo htmlspecialchars(miq_account_config()['google_client_id'], ENT_QUOTES, 'UTF-8'); ?>" data-callback="miqHandleGoogleCredential" data-auto_prompt="false"></div>
                    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="continue_with" data-shape="rectangular" data-logo_alignment="left"></div>
                </form>
            <?php else: ?>
                <div class="miq-google-unavailable">Google login will appear after the production OAuth client is configured.</div>
            <?php endif; ?>
            <div class="miq-account-links"><a href="/account?view=register">Create an account</a><a href="/account?view=forgot">Forgot password?</a></div>
            <?php if ($view === 'forgot'): ?>
                <form method="post" class="miq-account-form miq-reset-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="request_reset">
                    <label for="forgot_email">Reset email</label>
                    <input id="forgot_email" name="email" class="form-control" type="email" autocomplete="email" required>
                    <button class="btn btn-outline-primary btn-block" type="submit">Send reset link</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
