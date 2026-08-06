<?php
require_once __DIR__ . '/account/bootstrap.php';
if (!miq_community_enabled()) {
    http_response_code(404);
    $_GET['err'] = '404';
    require __DIR__ . '/error.php';
    exit;
}

$user = miq_account_current_user();
$context_code = strtoupper(trim(isset($_GET['code']) ? $_GET['code'] : ''));
$trend_context_type = $context_code !== '' ? 'stock' : 'site';
$trend_context_key = $context_code !== '' ? $context_code : 'site';
$trend_subject = $context_code !== '' ? $context_code : 'Global market';
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $miq_page_context_type = $trend_context_type;
    $miq_page_context_key = $trend_context_key;
    ?>
    <?php include __DIR__ . '/meta.php'; ?>
    <meta property="og:title" content="Community Ideas - 360MiQ.com" />
    <meta name="description" content="Explore moderated user-generated market ideas on 360MiQ.com." />
    <title>Community Ideas - 360MiQ.com</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/account.css?v=20260806.3">
<link rel="stylesheet" href="assets/css/workspace.css?v=20260806.2">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
</head>
<body class="miq-community-body">
<?php $page = 'community'; include __DIR__ . '/header.php'; ?>
<main class="miq-community-page container">
    <div class="miq-workspace-heading">
        <div>
            <span class="miq-account-kicker">Moderated community</span>
            <h1>Community Ideas<?php echo $context_code !== '' ? ' for ' . htmlspecialchars($context_code, ENT_QUOTES, 'UTF-8') : ''; ?></h1>
            <p>Short, user-generated market opinions. They are not investment advice and are reviewed before publication.</p>
        </div>
        <?php if ($user): ?><button class="btn btn-primary" type="button" data-open-idea-form>Share an idea</button><?php else: ?><a class="btn btn-primary" href="account.php?view=login&amp;return_to=<?php echo rawurlencode(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/community'); ?>">Sign in to share</a><?php endif; ?>
    </div>
    <?php
    $miq_pulse_featured = true;
    include __DIR__ . '/components/community-pulse.php';
    unset($miq_pulse_featured);
    ?>
    <section class="miq-community-list-card miq-sentiment-chart miq-sentiment-trend-card" data-sentiment-chart data-chart-mode="detail" data-context-type="<?php echo $trend_context_type; ?>" data-context-key="<?php echo htmlspecialchars($trend_context_key, ENT_QUOTES, 'UTF-8'); ?>" data-timeframe="30d" data-subject="<?php echo htmlspecialchars($trend_subject, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="miq-sentiment-trend-heading">
            <div>
                <h2><?php echo htmlspecialchars($trend_subject, ENT_QUOTES, 'UTF-8'); ?> 30-day outlook trend</h2>
                <p>Daily share of active bullish, neutral, and bearish votes. Each vote expires 30 days after it is submitted or renewed.</p>
            </div>
            <div class="miq-sentiment-legend" data-sentiment-legend hidden>
                <span class="is-bullish">Bullish</span>
                <span class="is-neutral">Neutral</span>
                <span class="is-bearish">Bearish</span>
            </div>
        </div>
        <div class="miq-sentiment-chart-plot" data-sentiment-plot></div>
        <div class="miq-sentiment-chart-status" data-sentiment-status aria-live="polite">Loading sentiment trend…</div>
    </section>
    <section class="miq-community-form-card" data-idea-form <?php echo $user ? '' : 'hidden'; ?>>
        <h2>Share a concise idea</h2>
        <form id="miq-idea-form">
            <input type="hidden" name="id" value="">
            <div class="miq-form-row">
                <div><label for="idea-code">Stock code</label><input id="idea-code" name="code" class="form-control" maxlength="40" value="<?php echo htmlspecialchars($context_code, ENT_QUOTES, 'UTF-8'); ?>" placeholder="AAPL or 0005.HK"></div>
                <div><label for="idea-direction">View</label><select id="idea-direction" name="direction" class="form-control"><option value="bullish">Bullish</option><option value="bearish">Bearish</option><option value="neutral">Neutral</option></select></div>
                <div><label for="idea-timeframe">Time horizon</label><input id="idea-timeframe" name="timeframe" class="form-control" maxlength="40" placeholder="e.g. 3–6 months"></div>
            </div>
            <label for="idea-title">Title</label><input id="idea-title" name="title" class="form-control" maxlength="160" required placeholder="What is the idea in one sentence?"><label for="idea-thesis">Thesis</label><textarea id="idea-thesis" name="thesis" class="form-control" maxlength="8000" rows="5" required></textarea>
            <div class="miq-form-row"><div><label for="idea-catalyst">Catalyst</label><textarea id="idea-catalyst" name="catalyst" class="form-control" maxlength="4000" rows="3"></textarea></div><div><label for="idea-risk">Risk / invalidation</label><textarea id="idea-risk" name="risk" class="form-control" maxlength="4000" rows="3"></textarea></div></div>
            <label for="idea-disclosure">Disclosure</label><input id="idea-disclosure" name="disclosure" class="form-control" maxlength="500" placeholder="e.g. I hold this stock / No position"><div class="miq-idea-actions"><button class="btn btn-outline-primary" type="button" data-save-idea-draft>Save draft</button><button class="btn btn-primary" type="button" data-submit-idea>Submit for review</button></div><div id="miq-idea-status" class="miq-inline-status" aria-live="polite"></div>
        </form>
    </section>
    <section class="miq-community-list-card"><div class="miq-community-list-heading"><h2>Published ideas</h2><span class="miq-inline-status" id="miq-community-list-status"></span></div><div id="miq-community-list">Loading published ideas…</div></section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/community.js?v=20260726.2"></script>
</body>
</html>
