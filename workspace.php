<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
if (!$user) {
    header('Location: /account?view=login&return_to=/workspace');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta property="og:title" content="My Workspace - 360MiQ.com" />
    <meta name="description" content="Your saved charts, Pine scripts, searches, watchlists, and community ideas on 360MiQ.com." />
    <title>My Workspace - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css">
    <link rel="stylesheet" href="assets/css/workspace.css">
</head>
<body>
<?php $page = 'workspace'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page container">
    <div class="miq-workspace-heading">
        <div>
            <span class="miq-account-kicker">Private workspace</span>
            <h1>Welcome, <?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Your saved research stays private unless you choose to share it.</p>
        </div>
        <a class="btn btn-primary" href="/tool?tab=3">Open Advanced Chart</a>
    </div>
    <div id="miq-workspace-status" class="alert" hidden></div>
    <div class="miq-workspace-tabs" role="tablist" aria-label="Workspace sections">
        <button class="btn btn-outline-primary active" data-workspace-tab="overview" type="button">Overview</button>
        <button class="btn btn-outline-primary" data-workspace-tab="charts" type="button">Saved Charts</button>
        <button class="btn btn-outline-primary" data-workspace-tab="scripts" type="button">Pine Scripts</button>
        <button class="btn btn-outline-primary" data-workspace-tab="searches" type="button">Recent Searches</button>
        <button class="btn btn-outline-primary" data-workspace-tab="watchlists" type="button">Watchlists</button>
        <button class="btn btn-outline-primary" data-workspace-tab="ideas" type="button">Community Ideas</button>
    </div>
    <section id="miq-workspace-content" class="miq-workspace-grid" aria-live="polite">
        <div class="miq-workspace-loading">Loading your workspace…</div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/workspace.js"></script>
</body>
</html>
