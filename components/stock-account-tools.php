<?php
$miq_stock_account_code = isset($stockcode) ? strtoupper(trim((string) $stockcode)) : '';
$miq_stock_account_logged_in = !empty($miq_account_user);
?>
<section id="miq-stock-account-tools" class="miq-stock-tools card clean-card" data-stock-code="<?php echo htmlspecialchars($miq_stock_account_code, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="miq-stock-tools-heading">
        <div>
            <span class="miq-account-kicker">Private research</span>
            <h2>My stock workspace</h2>
        </div>
        <a href="workspace" class="miq-stock-tools-workspace-link">Open Workspace</a>
    </div>
    <div class="miq-stock-tools-status" data-stock-tools-status role="status" hidden></div>
    <?php if (!$miq_stock_account_logged_in): ?>
        <div class="miq-stock-tools-signed-out">
            <p>Sign in to add this stock to watchlists, keep private research notes, and create price alerts.</p>
            <a class="btn btn-primary" href="account.php?view=login&amp;return_to=<?php echo rawurlencode(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/stockinfo'); ?>">Sign in</a>
        </div>
    <?php else: ?>
        <div class="miq-stock-tools-grid">
            <section class="miq-stock-tool-panel" aria-labelledby="miq-stock-watchlist-title">
                <h3 id="miq-stock-watchlist-title"><i class="fas fa-star" aria-hidden="true"></i> Watchlist</h3>
                <p>Track this stock and its daily move.</p>
                <label for="miq-stock-watchlist-select">Watchlist</label>
                <select id="miq-stock-watchlist-select" class="form-control"></select>
                <button id="miq-stock-watchlist-toggle" class="btn btn-primary btn-block" type="button" disabled>Add to watchlist</button>
                <form id="miq-stock-watchlist-create" class="miq-stock-tool-inline">
                    <input class="form-control" name="name" maxlength="120" placeholder="New watchlist name" required>
                    <button class="btn btn-outline-primary" type="submit">Create</button>
                </form>
            </section>
            <section class="miq-stock-tool-panel" aria-labelledby="miq-stock-note-title">
                <h3 id="miq-stock-note-title"><i class="fas fa-book-open" aria-hidden="true"></i> Research note</h3>
                <p>Private, timestamped notes for this stock.</p>
                <form id="miq-stock-note-form">
                    <input type="hidden" name="id">
                    <input class="form-control" name="title" maxlength="160" placeholder="Note title" required>
                    <textarea class="form-control" name="body" maxlength="20000" rows="3" placeholder="Thesis, evidence, risks…" required></textarea>
                    <div class="miq-stock-tool-actions">
                        <button class="btn btn-primary" type="submit">Save note</button>
                        <button class="btn btn-link" type="button" data-note-cancel hidden>Cancel edit</button>
                    </div>
                </form>
                <div class="miq-stock-mini-list" data-stock-note-list></div>
            </section>
            <section class="miq-stock-tool-panel" aria-labelledby="miq-stock-alert-title">
                <h3 id="miq-stock-alert-title"><i class="fas fa-bell" aria-hidden="true"></i> Price alert</h3>
                <p>Notify me in Workspace when the target is reached.</p>
                <form id="miq-stock-alert-form" class="miq-stock-alert-form">
                    <select class="form-control" name="condition_type" aria-label="Alert condition"><option value="above">At or above</option><option value="below">At or below</option></select>
                    <input class="form-control" name="target_price" type="number" min="0.0001" max="1000000000000" step="any" placeholder="Target price" required>
                    <button class="btn btn-primary" type="submit">Create alert</button>
                </form>
                <div class="miq-stock-mini-list" data-stock-alert-list></div>
            </section>
        </div>
    <?php endif; ?>
</section>
