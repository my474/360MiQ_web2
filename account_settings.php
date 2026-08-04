<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
if (!$user) {
    header('Location: account.php?view=login&return_to=account_settings');
    exit;
}
$messages = array();
$errors = array();
$display_name_suggestions = array();
$settings_display_name = $user['display_name'];
$preferences = miq_account_user_preferences((int) $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!miq_account_check_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session token expired. Refresh the page and try again.';
    } else {
        try {
            $action = (string) ($_POST['action'] ?? '');
            $users = miq_account_table('users');
            if ($action === 'profile') {
                $settings_display_name = (string) ($_POST['display_name'] ?? '');
                $name = miq_account_resolve_display_name($settings_display_name, $user['email'], false, (int) $user['id']);
                miq_account_query("UPDATE {$users} SET display_name = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'si', array($name, (int) $user['id']))->close();
                $messages[] = 'Your display name was updated.';
                $settings_display_name = $name;
                $user = miq_account_current_user();
            } elseif ($action === 'preferences') {
                $preferences = miq_account_save_preferences((int) $user['id'], array(
                    'default_market' => $_POST['default_market'] ?? '',
                    'preferred_timeframe' => $_POST['preferred_timeframe'] ?? '',
                    'theme_mode' => $_POST['theme_mode'] ?? '',
                    'chart_type' => $_POST['chart_type'] ?? '',
                    'chart_period' => $_POST['chart_period'] ?? '',
                    'auto_save_charts' => isset($_POST['auto_save_charts']),
                ));
                $messages[] = 'Your synced preferences were updated.';
            } elseif ($action === 'password') {
                $current_password = (string) ($_POST['current_password'] ?? '');
                $new_password = (string) ($_POST['new_password'] ?? '');
                $full_user = miq_account_find_user_by_email($user['email']);
                if (!$full_user['password_hash'] || !password_verify($current_password, $full_user['password_hash'])) throw new RuntimeException('Your current password is not correct.');
                miq_account_validate_password($new_password);
                $hash = password_hash($new_password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
                miq_account_query("UPDATE {$users} SET password_hash = ?, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'si', array($hash, (int) $user['id']))->close();
                $fresh = miq_account_find_user_by_email($user['email']);
                miq_account_login_user($fresh['id'], $fresh['session_version'], false);
                $messages[] = 'Your password was changed.';
            } elseif ($action === 'google_link') {
                $identity = miq_account_google_identity((string) ($_POST['credential'] ?? ''));
                if ($identity['email'] !== miq_account_normalize_email($user['email'])) throw new RuntimeException('For safety, Google must use the same email as this account.');
                $identities = miq_account_table('identities');
                $linked = miq_account_fetch_one(miq_account_query("SELECT user_id FROM {$identities} WHERE provider = 'google' AND provider_user_id = ? LIMIT 1", 's', array($identity['provider_user_id'])));
                if ($linked && (int) $linked['user_id'] !== (int) $user['id']) throw new RuntimeException('That Google account is already linked to another 360MiQ account.');
                if (!$linked) miq_account_query("INSERT INTO {$identities} (user_id, provider, provider_user_id, provider_email, created_at) VALUES (?, 'google', ?, ?, UTC_TIMESTAMP())", 'iss', array((int) $user['id'], $identity['provider_user_id'], $identity['email']))->close();
                miq_account_query("UPDATE {$users} SET avatar_url = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'si', array($identity['avatar_url'], (int) $user['id']))->close();
                $messages[] = 'Google is now connected to your account.';
            } elseif ($action === 'delete') {
                if (trim((string) ($_POST['confirmation'] ?? '')) !== 'DELETE MY ACCOUNT') throw new RuntimeException('Type DELETE MY ACCOUNT to confirm account deletion.');
                $db = miq_account_db();
                $db->begin_transaction();
                miq_account_delete_user_data((int) $user['id']);
                $db->commit();
                miq_account_logout(true);
                miq_account_flash('success', 'Your account and private workspace data were deleted.');
                header('Location: ./');
                exit;
            }
        } catch (Throwable $error) {
            if (isset($db) && $db instanceof mysqli) $db->rollback();
            if ($error instanceof MiqAccountDisplayNameTakenException) {
                $display_name_suggestions = $error->suggestions;
            }
            $errors[] = $error->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta property="og:title" content="Account Settings - 360MiQ.com" />
    <meta name="description" content="Manage your 360MiQ account, privacy, and workspace data." />
    <title>Account Settings - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css?v=20260726.6">
    <link rel="stylesheet" href="assets/css/workspace.css?v=20260726.5">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <?php if (miq_account_config()['google_client_id'] !== ''): ?><script src="https://accounts.google.com/gsi/client" async defer onload="window.miqInitGoogleButtons&&window.miqInitGoogleButtons()"></script><?php endif; ?>
</head>
<body>
<?php $page = 'account'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page container">
    <div class="miq-workspace-heading"><div><span class="miq-account-kicker">Account controls</span><h1>Account settings</h1><p><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p></div><a class="btn btn-outline-primary" href="workspace">Back to Workspace</a></div>
    <?php foreach ($messages as $message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php endforeach; ?>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endforeach; ?>
    <div class="miq-workspace-grid">
        <section class="miq-workspace-panel"><h2>Profile</h2><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="profile"><label for="settings-display-name">Public display name</label><input id="settings-display-name" class="form-control" name="display_name" value="<?php echo htmlspecialchars($settings_display_name, ENT_QUOTES, 'UTF-8'); ?>" maxlength="80" required><small>This name is shown with community activity and, for SSO-created contributor profiles, published WordPress articles after your next Write for Us sign-in. Use a personal name or handle; names resembling 360MiQ or official support accounts are not allowed.</small><?php if (!empty($display_name_suggestions)): ?><div class="miq-display-name-suggestions" aria-live="polite"><span>Available suggestions:</span><?php foreach ($display_name_suggestions as $suggestion): ?><button type="button" class="btn btn-sm btn-outline-primary miq-display-name-suggestion" data-display-name-suggestion="<?php echo htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?>" data-display-name-target="settings-display-name"><?php echo htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?></button><?php endforeach; ?></div><?php endif; ?><button class="btn btn-primary" type="submit">Save profile</button></form></section>
        <section class="miq-workspace-panel miq-workspace-panel-wide">
            <h2>Synced preferences</h2>
            <p>Use the same defaults on every device. Saved chart layouts remain separate and keep their own complete chart state.</p>
            <form method="post" class="miq-preferences-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="preferences">
                <div class="miq-settings-fields">
                    <label>Default market<select class="form-control" name="default_market"><?php foreach (array('NYSE', 'NASDAQ', 'LSE', 'TSX', 'ASX', 'NSE', 'TYO', 'HKEX', 'SHSE', 'SZSE') as $option): ?><option value="<?php echo $option; ?>" <?php echo $preferences['default_market'] === $option ? 'selected' : ''; ?>><?php echo $option; ?></option><?php endforeach; ?></select></label>
                    <label>Preferred timeframe<select class="form-control" name="preferred_timeframe"><?php foreach (array('1m', '3m', '6m', 'ytd', '1y', '2y', '3y', '5y', '8y', '10y', 'all') as $option): ?><option value="<?php echo $option; ?>" <?php echo $preferences['preferred_timeframe'] === $option ? 'selected' : ''; ?>><?php echo strtoupper($option); ?></option><?php endforeach; ?></select></label>
                    <label>Theme<select class="form-control" name="theme_mode"><option value="system" <?php echo $preferences['theme_mode'] === 'system' ? 'selected' : ''; ?>>Follow device</option><option value="light" <?php echo $preferences['theme_mode'] === 'light' ? 'selected' : ''; ?>>Light</option><option value="dark" <?php echo $preferences['theme_mode'] === 'dark' ? 'selected' : ''; ?>>Dark</option></select></label>
                    <label>New-chart style<select class="form-control" name="chart_type"><?php foreach (array('candlestick' => 'Candlestick', 'heikin_ashi' => 'Heikin Ashi', 'bar' => 'Bar', 'line' => 'Line', 'area' => 'Area', 'baseline' => 'Baseline') as $value => $label): ?><option value="<?php echo $value; ?>" <?php echo $preferences['chart_type'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php endforeach; ?></select></label>
                    <label>Chart interval<select class="form-control" name="chart_period"><?php foreach (array('daily', 'weekly', 'monthly', 'quarterly', 'yearly') as $option): ?><option value="<?php echo $option; ?>" <?php echo $preferences['chart_period'] === $option ? 'selected' : ''; ?>><?php echo ucfirst($option); ?></option><?php endforeach; ?></select></label>
                </div>
                <label class="miq-settings-check"><input type="checkbox" name="auto_save_charts" value="1" <?php echo $preferences['auto_save_charts'] ? 'checked' : ''; ?>> Automatically sync chart changes while signed in</label>
                <button class="btn btn-primary" type="submit">Save preferences</button>
            </form>
        </section>
        <section class="miq-workspace-panel"><h2>Change password</h2><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="password"><label for="current-password">Current password</label><input id="current-password" class="form-control" type="password" name="current_password" maxlength="1024" autocomplete="current-password" required><label for="new-password">New password</label><input id="new-password" class="form-control" type="password" name="new_password" minlength="8" maxlength="1024" autocomplete="new-password" required><button class="btn btn-primary" type="submit">Change password</button></form></section>
        <section class="miq-workspace-panel"><h2>Your data</h2><p>Export your account data or permanently delete your account and private workspace.</p><a class="btn btn-outline-primary" href="account_export.php">Download my data</a></section>
        <section class="miq-workspace-panel"><h2>Connected login</h2><?php if (miq_account_config()['google_client_id'] !== ''): ?><p>Connect Google for faster sign-in. The Google email must match this account.</p><form method="post" id="google-link-form" class="miq-google-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="google_link"><input type="hidden" name="credential" id="google-link-credential"><div class="miq-google-button" data-google-client-id="<?php echo htmlspecialchars(miq_account_config()['google_client_id'], ENT_QUOTES, 'UTF-8'); ?>" data-google-mode="link" data-google-size="medium"></div></form><?php else: ?><p>Google connection will be available after the production OAuth client is configured.</p><?php endif; ?></section>
        <section class="miq-workspace-panel miq-workspace-panel-wide"><h2>Delete account</h2><p>This permanently removes your account, private charts, scripts, searches, and watchlists.<?php if (miq_community_enabled()): ?> Community drafts and published ideas will also be removed.<?php endif; ?></p><form method="post" onsubmit="return window.confirm('Delete this account and all workspace data?');"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(miq_account_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="delete"><label for="delete-confirmation">Type DELETE MY ACCOUNT to confirm</label><input id="delete-confirmation" class="form-control" name="confirmation" maxlength="22" required><button class="btn btn-outline-danger" type="submit">Delete account permanently</button></form></section>
    </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
