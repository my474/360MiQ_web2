<?php
require_once __DIR__ . '/account/bootstrap.php';
$user = miq_account_current_user();
if (!$user || !miq_account_is_moderator($user)) {
    http_response_code(403);
    echo 'Moderator access required.';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta name="robots" content="noindex, nofollow">
    <title>Community Moderation - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/css/account.css">
    <link rel="stylesheet" href="assets/css/workspace.css">
</head>
<body>
<?php $page = 'moderation'; include __DIR__ . '/header.php'; ?>
<main class="miq-workspace-page container">
    <div class="miq-workspace-heading"><div><span class="miq-account-kicker">Moderator tools</span><h1>Community moderation</h1><p>Review every idea before it becomes public.</p></div><a class="btn btn-outline-primary" href="/community">View public community</a></div>
    <div id="miq-moderation-status" class="alert" hidden></div>
    <section class="miq-workspace-panel miq-workspace-panel-wide"><div id="miq-moderation-queue">Loading pending ideas…</div></section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/moderation.js"></script>
</body>
</html>
