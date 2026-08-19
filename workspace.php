<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
$community_enabled = miq_community_enabled();
if (!$user) {
    header('Location: account.php?view=login&return_to=workspace');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <?php
    $miq_workspace_unread_notifications = (int) ($miq_account_unread_notifications ?? 0);
    $miq_workspace_unread_badge = $miq_workspace_unread_notifications > 99 ? '99+' : (string) $miq_workspace_unread_notifications;
    ?>
    <meta property="og:title" content="My Workspace - 360MiQ.com" />
    <meta name="description" content="<?php echo $community_enabled ? 'Your saved charts, Pine scripts, screener presets, searches, and community ideas on 360MiQ.com.' : 'Your saved charts, Pine scripts, screener presets, and searches on 360MiQ.com.'; ?>" />
    <title>My Workspace - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i,600,600i&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/simple-line-icons.min.css">
    <link rel="stylesheet" href="assets/css/account.css?v=20260806.7">
    <link rel="stylesheet" href="assets/css/workspace.css?v=20260812.4">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="assets/js/Utils.js?v=20260819.1"></script>
    <script src="assets/js/jquery-ui.min.js?v=20260819.1"></script>
</head>
<body data-community-enabled="<?php echo $community_enabled ? 'true' : 'false'; ?>">
<?php $page = 'workspace'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page container">
    <div class="miq-workspace-heading">
        <div>
            <span class="miq-account-kicker">Private workspace</span>
            <h1>Welcome, <?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Your saved research stays private unless you choose to share it.</p>
        </div>
    </div>
    <div id="miq-workspace-status" class="alert" hidden></div>
    <div class="miq-workspace-tabs" role="tablist" aria-label="Workspace sections">
        <button class="btn btn-outline-primary active" data-workspace-tab="overview" type="button">Dashboard</button>
        <button class="btn btn-outline-primary" data-workspace-tab="charts" type="button">Saved Charts</button>
        <button class="btn btn-outline-primary" data-workspace-tab="scripts" type="button">Pine Scripts</button>
        <button class="btn btn-outline-primary" data-workspace-tab="presets" type="button">Screener Presets</button>
        <button class="btn btn-outline-primary" data-workspace-tab="watchlists" type="button">Watchlists</button>
        <button class="btn btn-outline-primary" data-workspace-tab="notes" type="button">Research Notes</button>
        <button class="btn btn-outline-primary" data-workspace-tab="alerts" type="button">Price Alerts</button>
        <button class="btn btn-outline-primary" data-workspace-tab="searches" type="button">Recent Searches</button>
        <?php if ($community_enabled): ?><button class="btn btn-outline-primary" data-workspace-tab="ideas" type="button">Community Ideas</button><button class="btn btn-outline-primary" data-workspace-tab="bookmarks" type="button">Bookmarks</button><?php endif; ?>
        <button class="btn btn-outline-primary" data-workspace-tab="notifications" type="button">Notifications <span class="miq-tab-count" data-miq-account-unread-badge<?php if ($miq_workspace_unread_notifications < 1): ?> hidden<?php endif; ?>><?php echo $miq_workspace_unread_badge; ?></span></button>
    </div>
    <section id="miq-workspace-content" class="miq-workspace-grid" aria-live="polite">
        <div class="miq-workspace-loading">Loading your workspace…</div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/workspace.js?v=20260819.2"></script>
</body>
</html>
