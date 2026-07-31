<?php
require_once __DIR__ . '/account/bootstrap.php';
if (!miq_community_enabled()) {
    http_response_code(404);
    $_GET['err'] = '404';
    require __DIR__ . '/error.php';
    exit;
}

$user = miq_account_current_user();
if (!$user) {
    header('Location: account.php?view=login&return_to=community_moderation');
    exit;
}
if (!miq_account_is_moderator($user)) {
    http_response_code(403);
    echo 'Moderator access required.';
    exit;
}
header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta name="robots" content="noindex, nofollow">
    <title>Community Moderation - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css?v=20260726.6">
    <link rel="stylesheet" href="assets/css/workspace.css?v=20260726.5">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
</head>
<body class="miq-moderation-body">
<?php $page = 'moderation'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page miq-moderation-page container">
    <div class="miq-workspace-heading">
        <div>
            <span class="miq-account-kicker">Moderator tools</span>
            <h1>Community moderation</h1>
            <p>Review submissions, resolve reports, and maintain an accountable action record.</p>
        </div>
        <a class="btn btn-outline-primary" href="community">View public community</a>
    </div>
    <div id="miq-moderation-status" class="alert" hidden></div>
    <section class="miq-moderation-summary" aria-label="Moderation summary">
        <button class="miq-moderation-stat is-active" type="button" data-moderation-tab="pending">
            <span data-moderation-count="pending">-</span>
            <small>Pending ideas</small>
        </button>
        <button class="miq-moderation-stat" type="button" data-moderation-tab="reports">
            <span data-moderation-count="reports">-</span>
            <small>Open reports</small>
        </button>
        <button class="miq-moderation-stat" type="button" data-moderation-tab="replies">
            <span data-moderation-count="replies">-</span>
            <small>Pending replies</small>
        </button>
        <button class="miq-moderation-stat" type="button" data-moderation-tab="history">
            <span data-moderation-count="actions">-</span>
            <small>Actions logged</small>
        </button>
    </section>
    <div class="miq-moderation-tabs" role="tablist" aria-label="Moderation sections">
        <button class="btn btn-outline-primary active" type="button" role="tab" aria-selected="true" data-moderation-tab="pending">Pending ideas</button>
        <button class="btn btn-outline-primary" type="button" role="tab" aria-selected="false" data-moderation-tab="reports">User reports</button>
        <button class="btn btn-outline-primary" type="button" role="tab" aria-selected="false" data-moderation-tab="replies">Pending replies</button>
        <button class="btn btn-outline-primary" type="button" role="tab" aria-selected="false" data-moderation-tab="history">Audit history</button>
        <button class="btn btn-outline-secondary ml-auto" type="button" data-moderation-refresh><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>
    <section class="miq-workspace-panel miq-workspace-panel-wide miq-moderation-panel" data-moderation-panel="pending">
        <div class="miq-moderation-panel-heading"><div><span class="miq-account-kicker">Publication queue</span><h2>Pending ideas</h2></div><p>Publish suitable submissions or reject them with a clear moderator note.</p></div>
        <div id="miq-moderation-queue" aria-live="polite">Loading pending ideas...</div>
    </section>
    <section class="miq-workspace-panel miq-workspace-panel-wide miq-moderation-panel" data-moderation-panel="replies" hidden>
        <div class="miq-moderation-panel-heading"><div><span class="miq-account-kicker">Reply queue</span><h2>Pending replies</h2></div><p>Review replies before they become visible on published ideas.</p></div>
        <div id="miq-moderation-replies" aria-live="polite">Loading pending replies...</div>
    </section>
    <section class="miq-workspace-panel miq-workspace-panel-wide miq-moderation-panel" data-moderation-panel="reports" hidden>
        <div class="miq-moderation-panel-heading"><div><span class="miq-account-kicker">Safety queue</span><h2>User reports</h2></div><p>Review the report and complete idea before dismissing it or hiding the content.</p></div>
        <div id="miq-moderation-reports" aria-live="polite">Loading reports...</div>
    </section>
    <section class="miq-workspace-panel miq-workspace-panel-wide miq-moderation-panel" data-moderation-panel="history" hidden>
        <div class="miq-moderation-panel-heading"><div><span class="miq-account-kicker">Accountability</span><h2>Audit history</h2></div><p>The latest 100 moderation actions, including moderator notes.</p></div>
        <div id="miq-moderation-history" aria-live="polite">Loading history...</div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/moderation.js?v=20260726.2"></script>
</body>
</html>
