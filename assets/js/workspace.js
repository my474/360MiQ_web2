(function () {
    'use strict';

    var content = document.getElementById('miq-workspace-content');
    var status = document.getElementById('miq-workspace-status');
    var workspace = null;
    var communityEnabled = !document.body || document.body.getAttribute('data-community-enabled') !== 'false';
    var activeTab = new URLSearchParams(window.location.search).get('tab') || 'overview';
    if (!communityEnabled && (activeTab === 'ideas' || activeTab === 'bookmarks')) activeTab = 'overview';
    var chartsState = { items: [], page: 0, total: 0, search: '', kind: '', loading: false };
    var scriptsState = { items: [], page: 0, total: 0, search: '', status: '', loading: false };
    var editingNoteId = 0;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function formatPrice(value) {
        var parsed = Number(value);
        if (!Number.isFinite(parsed)) return value == null ? '' : String(value);
        return parsed.toLocaleString(undefined, { maximumFractionDigits: 6 });
    }

    function showStatus(message, type) {
        if (!status) return;
        status.hidden = !message;
        status.className = 'alert alert-' + (type || 'info');
        status.textContent = message || '';
    }

    function offerNotificationContext(category, source) {
        if (window.MIQNotifications && typeof window.MIQNotifications.offerContext === 'function') {
            window.MIQNotifications.offerContext(category, source);
            return;
        }
        if (typeof window.CustomEvent === 'function') {
            window.dispatchEvent(new window.CustomEvent('miq:notification-context', {
                detail: { category: category, source: source }
            }));
        }
    }

    function panel(title, body, wide) {
        return '<section class="miq-workspace-panel' + (wide ? ' miq-workspace-panel-wide' : '') + '"><h2>' + escapeHtml(title) + '</h2>' + body + '</section>';
    }

    function parseWorkspaceDate(value) {
        if (!value) return null;
        var text = String(value);
        if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(text)) {
            text = text.replace(' ', 'T') + 'Z';
        }
        var parsed = new Date(text);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function humanDate(value) {
        var parsed = parseWorkspaceDate(value);
        return parsed ? parsed.toLocaleString() : String(value || '');
    }

    function assetMeta(parts) {
        return '<span class="miq-asset-meta">' + parts.filter(Boolean).map(escapeHtml).join(' · ') + '</span>';
    }

    function workspaceItems(key) {
        return Array.isArray(workspace[key]) ? workspace[key] : [];
    }

    function recentSearches() {
        return workspaceItems('searches').slice().sort(function (left, right) {
            var leftDate = parseWorkspaceDate(left.searched_at);
            var rightDate = parseWorkspaceDate(right.searched_at);
            var dateDifference = (rightDate ? rightDate.getTime() : 0) - (leftDate ? leftDate.getTime() : 0);
            if (dateDifference !== 0) return dateDifference;
            return Number(right.id || 0) - Number(left.id || 0);
        });
    }

    function workspaceCount(key) {
        var counts = workspace.counts || {};
        if (counts[key] != null) return Number(counts[key]);
        return workspaceItems(key).length;
    }

    function dashboardMetric(value, label, detail, href) {
        return '<a class="miq-dashboard-metric" href="' + href + '">' +
            '<strong>' + Number(value || 0) + '</strong>' +
            '<span>' + escapeHtml(label) + '</span>' +
            '<small>' + escapeHtml(detail) + '</small></a>';
    }

    function recentActivityItems() {
        var items = [];
        workspaceItems('charts').slice(0, 2).forEach(function (item) {
            items.push({
                type: 'Saved chart',
                title: item.name,
                detail: item.code || 'Advanced Chart',
                date: item.updated_at,
                href: 'tool?tab=3&stockcode=' + encodeURIComponent(item.code || '') + '&chart_id=' + encodeURIComponent(item.id)
            });
        });
        workspaceItems('scripts').slice(0, 2).forEach(function (item) {
            items.push({
                type: 'Pine script',
                title: item.name,
                detail: item.code ? 'Test symbol ' + item.code : (item.status || 'Draft'),
                date: item.updated_at,
                href: 'tool?tab=3&stockcode=' + encodeURIComponent(item.code || 'SPY') + '&script_id=' + encodeURIComponent(item.id)
            });
        });
        workspaceItems('screener_presets').slice(0, 2).forEach(function (item) {
            items.push({
                type: 'Screener preset',
                title: item.name,
                detail: item.is_default === true || item.is_default === 1 || item.is_default === '1' ? 'Default preset' : 'Saved screen',
                date: item.updated_at,
                href: 'screener?preset=' + encodeURIComponent(item.client_key)
            });
        });
        recentSearches().slice(0, 2).forEach(function (item) {
            items.push({
                type: 'Recent stock',
                title: item.code,
                detail: item.display_name || item.exchange || 'Stock research',
                date: item.searched_at,
                order: Number(item.id || 0),
                href: 'stockinfo?code=' + encodeURIComponent(item.code)
            });
        });
        workspaceItems('notes').slice(0, 2).forEach(function (item) {
            items.push({
                type: 'Research note',
                title: item.title,
                detail: item.stock_code || item.chart_name || item.script_name || 'Private journal',
                date: item.updated_at,
                href: 'workspace?tab=notes'
            });
        });
        if (communityEnabled) {
            workspaceItems('ideas').slice(0, 1).forEach(function (item) {
                items.push({
                    type: 'Community idea',
                    title: item.title,
                    detail: [item.direction, item.status].filter(Boolean).join(' · '),
                    date: item.updated_at,
                    href: 'workspace?tab=ideas'
                });
            });
        }
        return items.sort(function (left, right) {
            var leftDate = parseWorkspaceDate(left.date);
            var rightDate = parseWorkspaceDate(right.date);
            var dateDifference = (rightDate ? rightDate.getTime() : 0) - (leftDate ? leftDate.getTime() : 0);
            if (dateDifference !== 0) return dateDifference;
            return Number(right.order || 0) - Number(left.order || 0);
        }).slice(0, 4);
    }

    function renderRecentActivity() {
        var items = recentActivityItems();
        if (!items.length) {
            return '<div class="miq-dashboard-empty"><strong>Start your first piece of research</strong><span>Open a chart, screen stocks, or search for a company. Your recent work will appear here.</span><a class="btn btn-sm btn-primary" href="screener">Open Stock Screener</a></div>';
        }
        return '<div class="miq-dashboard-activity-grid">' + items.map(function (item) {
            return '<a class="miq-dashboard-activity" href="' + item.href + '">' +
                '<span class="miq-dashboard-type">' + escapeHtml(item.type) + '</span>' +
                '<strong>' + escapeHtml(item.title) + '</strong>' +
                '<span>' + escapeHtml(item.detail) + '</span>' +
                '<small>' + escapeHtml(humanDate(item.date)) + '</small></a>';
        }).join('') + '</div>';
    }

    function renderRecentStocks() {
        var searches = recentSearches().slice(0, 8);
        var body = searches.length
            ? '<div class="miq-dashboard-stock-list">' + searches.map(function (item) {
                return '<a href="stockinfo?code=' + encodeURIComponent(item.code) + '" title="' + escapeHtml(item.display_name || item.code) + '">' + escapeHtml(item.code) + '</a>';
            }).join('') + '</div>'
            : '<div class="miq-dashboard-mini-empty">Your recently viewed stocks will appear here.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>History</span><h2>Recent stocks</h2></div><a href="workspace?tab=searches">View all</a></div>' + body + '</section>';
    }

    function renderSavedScreens() {
        var presets = workspaceItems('screener_presets').slice(0, 4);
        var body = presets.length
            ? '<div class="miq-dashboard-compact-list">' + presets.map(function (item) {
                var detail = item.is_default === true || item.is_default === 1 || item.is_default === '1'
                    ? 'Default preset'
                    : 'Updated ' + humanDate(item.updated_at);
                return '<a href="screener?preset=' + encodeURIComponent(item.client_key) + '"><strong>' + escapeHtml(item.name) + '</strong><small>' + escapeHtml(detail) + '</small></a>';
            }).join('') + '</div>'
            : '<div class="miq-dashboard-mini-empty">Save a screener setup to reuse it here.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>Screening</span><h2>Saved screens</h2></div><a href="screener">Open screener</a></div>' + body + '</section>';
    }

    function renderRecentCharts() {
        var charts = workspaceItems('charts').slice(0, 4);
        var body = charts.length
            ? '<div class="miq-dashboard-compact-list">' + charts.map(function (item) {
                return '<a href="tool?tab=3&stockcode=' + encodeURIComponent(item.code || '') + '&chart_id=' + Number(item.id) +
                    '"><strong>' + escapeHtml(item.name) + '</strong><small>' + escapeHtml(item.code || 'Advanced Chart') +
                    ' · updated ' + escapeHtml(humanDate(item.updated_at)) + '</small></a>';
            }).join('') + '</div>'
            : '<div class="miq-dashboard-mini-empty">Your recently saved charts will appear here.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>Charting</span><h2>Recent charts</h2></div><a href="workspace?tab=charts">View all</a></div>' + body + '</section>';
    }

    function quoteMap() {
        return workspaceItems('watchlist_quotes').reduce(function (map, quote) {
            map[String(quote.code || '').toUpperCase()] = quote;
            return map;
        }, {});
    }

    function renderWatchlistMovers() {
        var quotes = workspaceItems('watchlist_quotes').slice().sort(function (left, right) {
            return Math.abs(Number(right.change_pct || 0)) - Math.abs(Number(left.change_pct || 0));
        }).slice(0, 8);
        var body = quotes.length ? '<div class="miq-dashboard-movers">' + quotes.map(function (quote) {
            var change = Number(quote.change_pct || 0);
            var changeClass = change > 0 ? 'is-up' : (change < 0 ? 'is-down' : '');
            return '<a href="stockinfo?code=' + encodeURIComponent(quote.code) + '"><span><strong>' + escapeHtml(quote.code) +
                '</strong><small>' + escapeHtml(quote.name_en || quote.name_tc || quote.exchange || '') +
                '</small></span><span class="miq-mover-price">' + escapeHtml(quote.close == null ? '—' : quote.close) +
                '<em class="' + changeClass + '">' + (change > 0 ? '+' : '') + change.toFixed(2) + '%</em></span></a>';
        }).join('') + '</div>' : '<div class="miq-dashboard-mini-empty">Add stocks to a watchlist to see daily movers here.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>Watchlists</span><h2>Watchlist movers</h2></div><a href="workspace?tab=watchlists">Manage</a></div>' + body + '</section>';
    }

    function renderTriggeredAlerts() {
        var alerts = workspaceItems('alerts').filter(function (alert) { return alert.status === 'triggered'; }).slice(0, 5);
        var body = alerts.length ? '<div class="miq-dashboard-compact-list">' + alerts.map(function (alert) {
            return '<a href="stockinfo?code=' + encodeURIComponent(alert.code) + '"><strong>' + escapeHtml(alert.code) +
                ' ' + (alert.condition_type === 'above' ? '≥ ' : '≤ ') + escapeHtml(formatPrice(alert.target_price)) +
                '</strong><small>Triggered ' + escapeHtml(humanDate(alert.triggered_at)) +
                (alert.last_price != null ? ' · last ' + escapeHtml(formatPrice(alert.last_price)) : '') + '</small></a>';
        }).join('') + '</div>' : '<div class="miq-dashboard-mini-empty">No newly triggered alerts.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>Monitoring</span><h2>Triggered alerts</h2></div><a href="workspace?tab=alerts">View all</a></div>' + body + '</section>';
    }

    function renderCommunitySummary() {
        var ideas = workspaceItems('ideas').slice(0, 3);
        var body = ideas.length
            ? '<div class="miq-dashboard-compact-list">' + ideas.map(function (item) {
                return '<a href="workspace?tab=ideas"><strong>' + escapeHtml(item.title) + '</strong><small>' + escapeHtml([item.direction, item.status].filter(Boolean).join(' · ')) + '</small></a>';
            }).join('') + '</div>'
            : '<div class="miq-dashboard-mini-empty">Your submitted and draft ideas will appear here.</div>';
        return '<section class="miq-workspace-panel miq-dashboard-compact"><div class="miq-dashboard-panel-heading"><div><span>Community</span><h2>Your ideas</h2></div><a href="community">Open community</a></div>' + body + '</section>';
    }

    function renderPrivacySummary() {
        return '<section class="miq-workspace-panel miq-dashboard-compact miq-dashboard-privacy"><div class="miq-dashboard-panel-heading"><div><span>Account</span><h2>Private by default</h2></div><a href="account_settings">Settings</a></div><p>Saved charts, scripts, screens, and drafts remain private unless you explicitly share or submit them.</p></section>';
    }

    function renderOverview() {
        var actions = '<div class="miq-dashboard-actions">' +
            '<a class="btn btn-primary" href="tool?tab=3">Open Advanced Chart</a>' +
            '<a class="btn btn-outline-primary" href="screener">Run Stock Screener</a>' +
            '<a class="btn btn-outline-primary" href="market">View Markets</a>' +
            '<a class="btn btn-outline-primary" href="account_sso.php?target=new-post">Write an Article</a>' +
            (communityEnabled ? '<a class="btn btn-outline-primary" href="community">Community Ideas</a>' : '') + '</div>';
        var metrics = '<section class="miq-dashboard-metrics miq-workspace-panel-wide" aria-label="Workspace totals">' +
            dashboardMetric(workspaceCount('watchlist_items'), 'Watchlist stocks', 'Track daily movers', 'workspace?tab=watchlists') +
            dashboardMetric(workspaceCount('active_alerts'), 'Active alerts', 'Monitor targets', 'workspace?tab=alerts') +
            dashboardMetric(workspaceCount('notes'), 'Research notes', 'Private journal', 'workspace?tab=notes') +
            dashboardMetric(workspaceCount('screener_presets'), 'Saved screens', 'Run a preset', 'workspace?tab=presets') +
            dashboardMetric(workspaceCount('charts'), 'Saved charts', 'Open and manage', 'workspace?tab=charts') +
            dashboardMetric(workspaceCount('scripts'), 'Pine scripts', 'Continue coding', 'workspace?tab=scripts') + '</section>';
        return '<section class="miq-dashboard-hero miq-workspace-panel-wide"><div><span class="miq-dashboard-eyebrow">Personalized dashboard</span><h2>Your research at a glance</h2><p>Pick up where you left off, reopen saved tools, or start a new market investigation.</p></div>' + actions + '</section>' +
            metrics +
            '<section class="miq-workspace-panel miq-workspace-panel-wide miq-dashboard-continue"><div class="miq-dashboard-panel-heading"><div><span>Based on your activity</span><h2>Continue researching</h2></div></div>' + renderRecentActivity() + '</section>' +
            renderWatchlistMovers() +
            renderTriggeredAlerts() +
            renderRecentCharts() +
            renderSavedScreens() +
            renderRecentStocks() +
            (communityEnabled ? renderCommunitySummary() : renderPrivacySummary()) +
            (communityEnabled ? '<section class="miq-dashboard-privacy-note miq-workspace-panel-wide"><strong>Private by default.</strong> Saved charts, scripts, screens, and drafts remain private unless you explicitly share or submit them. <a href="account_settings">Review account settings</a></section>' : '');
    }

    function chartRow(item) {
        var openUrl = 'tool?tab=3&stockcode=' + encodeURIComponent(item.code) + '&chart_id=' + encodeURIComponent(item.id);
        return '<article class="miq-asset-row" data-chart-id="' + Number(item.id) + '">' +
            '<div class="miq-asset-main"><a class="miq-asset-title" href="' + openUrl + '">' + escapeHtml(item.name) + '</a>' +
            '<span class="miq-asset-badge">' + escapeHtml(item.kind === 'workspace' ? 'Synced workspace' : 'Named chart') + '</span>' +
            assetMeta([item.code, 'revision ' + item.revision, 'updated ' + humanDate(item.updated_at)]) + '</div>' +
            '<div class="miq-asset-actions">' +
            '<a class="btn btn-sm btn-primary" href="' + openUrl + '">Open</a>' +
            '<button class="btn btn-sm btn-outline-primary" type="button" data-asset-action="rename-chart">Rename</button>' +
            '<button class="btn btn-sm btn-outline-primary" type="button" data-asset-action="duplicate-chart">Duplicate</button>' +
            '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="chart-versions">Versions</button>' +
            '<button class="btn btn-sm btn-outline-danger" type="button" data-asset-action="delete-chart">Delete</button></div>' +
            '<form class="miq-asset-inline-form" data-rename-chart-form hidden><label>New chart name<input class="form-control" name="name" maxlength="120" value="' + escapeHtml(item.name) + '" required></label><button class="btn btn-sm btn-primary" type="submit">Save</button><button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="cancel-inline">Cancel</button></form>' +
            '<div class="miq-asset-versions" data-chart-versions hidden></div></article>';
    }

    function renderCharts() {
        var controls = '<form class="miq-asset-filter" data-chart-filter><input class="form-control" type="search" name="search" value="' + escapeHtml(chartsState.search) + '" placeholder="Search chart name or stock"><select class="form-control" name="kind"><option value="">All charts</option><option value="workspace"' + (chartsState.kind === 'workspace' ? ' selected' : '') + '>Synced workspaces</option><option value="named"' + (chartsState.kind === 'named' ? ' selected' : '') + '>Named charts</option></select><button class="btn btn-outline-primary" type="submit">Filter</button><a class="btn btn-primary" href="tool?tab=3">New chart</a></form>';
        var rows = chartsState.items.length ? chartsState.items.map(chartRow).join('') : '<div class="miq-empty-state">No matching charts yet.</div>';
        var more = chartsState.items.length < chartsState.total ? '<button class="btn btn-outline-primary miq-load-more" type="button" data-asset-action="load-more-charts">Load more</button>' : '';
        return panel('Saved charts', controls + '<div class="miq-asset-list">' + rows + '</div>' + more, true);
    }

    function scriptRow(item) {
        var symbol = item.code || 'SPY';
        var openUrl = 'tool?tab=3&stockcode=' + encodeURIComponent(symbol) + '&script_id=' + encodeURIComponent(item.id);
        return '<article class="miq-asset-row" data-script-id="' + Number(item.id) + '">' +
            '<div class="miq-asset-main"><a class="miq-asset-title" href="' + openUrl + '">' + escapeHtml(item.name) + '</a>' +
            '<span class="miq-asset-badge">' + escapeHtml(item.status || 'draft') + '</span>' +
            assetMeta([item.code ? 'test symbol ' + item.code : '', 'revision ' + item.revision, 'updated ' + humanDate(item.updated_at)]) + '</div>' +
            '<div class="miq-asset-actions">' +
            '<a class="btn btn-sm btn-primary" href="' + openUrl + '">Open in chart</a>' +
            '<button class="btn btn-sm btn-outline-primary" type="button" data-asset-action="rename-script">Rename</button>' +
            '<button class="btn btn-sm btn-outline-primary" type="button" data-asset-action="duplicate-script">Duplicate</button>' +
            '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="download-script">Download</button>' +
            '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="script-versions">Versions</button>' +
            (item.status === 'archived'
                ? '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="unarchive-script">Unarchive</button>'
                : '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="archive-script">Archive</button>') +
            '<button class="btn btn-sm btn-outline-danger" type="button" data-asset-action="delete-script">Delete</button></div>' +
            '<form class="miq-asset-inline-form" data-rename-script-form hidden><label>New script name<input class="form-control" name="name" maxlength="120" value="' + escapeHtml(item.name) + '" required></label><button class="btn btn-sm btn-primary" type="submit">Save</button><button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="cancel-inline">Cancel</button></form>' +
            '<div class="miq-asset-versions" data-script-versions hidden></div></article>';
    }

    function renderScripts() {
        var controls = '<form class="miq-asset-filter" data-script-filter><input class="form-control" type="search" name="search" value="' + escapeHtml(scriptsState.search) + '" placeholder="Search script name or symbol"><select class="form-control" name="status"><option value="">All statuses</option><option value="draft"' + (scriptsState.status === 'draft' ? ' selected' : '') + '>Draft</option><option value="published"' + (scriptsState.status === 'published' ? ' selected' : '') + '>Published</option><option value="archived"' + (scriptsState.status === 'archived' ? ' selected' : '') + '>Archived</option></select><button class="btn btn-outline-primary" type="submit">Filter</button><a class="btn btn-primary" href="tool?tab=3">New script</a><button class="btn btn-outline-secondary" type="button" data-asset-action="import-recent-scripts">Import browser recents</button></form>';
        var rows = scriptsState.items.length ? scriptsState.items.map(scriptRow).join('') : '<div class="miq-empty-state">No matching Pine scripts yet.</div>';
        var more = scriptsState.items.length < scriptsState.total ? '<button class="btn btn-outline-primary miq-load-more" type="button" data-asset-action="load-more-scripts">Load more</button>' : '';
        return panel('Pine scripts', controls + '<p class="miq-asset-note">A chart keeps an embedded script snapshot for reproducible sharing. My Scripts stores the reusable account copy.</p><div class="miq-asset-list">' + rows + '</div>' + more, true);
    }

    function renderWatchlists() {
        var quotes = quoteMap();
        var create = '<form class="miq-management-create" data-watchlist-create><input class="form-control" name="name" maxlength="120" placeholder="New watchlist name" required><button class="btn btn-primary" type="submit">Create watchlist</button></form>';
        var lists = workspaceItems('watchlists');
        var body = lists.length ? '<div class="miq-management-stack">' + lists.map(function (list) {
            var items = Array.isArray(list.items) ? list.items : [];
            var rows = items.length ? items.map(function (item, index) {
                var quote = quotes[String(item.code || '').toUpperCase()] || {};
                var change = Number(quote.change_pct || 0);
                var changeClass = change > 0 ? 'is-up' : (change < 0 ? 'is-down' : '');
                return '<div class="miq-watchlist-row" data-watchlist-code="' + escapeHtml(item.code) + '"><a href="stockinfo?code=' +
                    encodeURIComponent(item.code) + '"><strong>' + escapeHtml(item.code) + '</strong><small>' +
                    escapeHtml(quote.name_en || quote.name_tc || quote.exchange || '') + '</small></a><span>' +
                    escapeHtml(quote.close == null ? '—' : quote.close) + '</span><span class="' + changeClass + '">' +
                    (change > 0 ? '+' : '') + change.toFixed(2) + '%</span><div class="miq-management-actions">' +
                    '<button class="btn btn-sm btn-link" type="button" data-workspace-action="watchlist-up" ' + (index === 0 ? 'disabled' : '') + ' aria-label="Move ' + escapeHtml(item.code) + ' up">↑</button>' +
                    '<button class="btn btn-sm btn-link" type="button" data-workspace-action="watchlist-down" ' + (index === items.length - 1 ? 'disabled' : '') + ' aria-label="Move ' + escapeHtml(item.code) + ' down">↓</button>' +
                    '<button class="btn btn-sm btn-outline-danger" type="button" data-workspace-action="watchlist-remove">Remove</button></div></div>';
            }).join('') : '<div class="miq-empty-state">No stocks in this watchlist yet.</div>';
            return '<article class="miq-management-card" data-watchlist-id="' + Number(list.id) + '"><div class="miq-management-heading"><div><h3>' +
                escapeHtml(list.name) + '</h3><small>' + items.length + ' stock' + (items.length === 1 ? '' : 's') +
                ' · updated ' + escapeHtml(humanDate(list.updated_at)) + '</small></div><div class="miq-management-actions">' +
                '<button class="btn btn-sm btn-outline-primary" type="button" data-workspace-action="watchlist-rename-show">Rename</button>' +
                '<button class="btn btn-sm btn-outline-danger" type="button" data-workspace-action="watchlist-delete">Delete</button></div></div>' +
                '<form class="miq-management-create" data-watchlist-rename hidden><input class="form-control" name="name" maxlength="120" value="' +
                escapeHtml(list.name) + '" required><button class="btn btn-primary" type="submit">Save name</button><button class="btn btn-outline-secondary" type="button" data-workspace-action="watchlist-rename-cancel">Cancel</button></form>' +
                '<div class="miq-watchlist-table">' + rows + '</div><form class="miq-management-create" data-watchlist-add><input class="form-control" name="code" maxlength="40" placeholder="Stock code, e.g. AAPL" required><button class="btn btn-outline-primary" type="submit">Add stock</button></form></article>';
        }).join('') + '</div>' : '<div class="miq-empty-state">Create your first watchlist to monitor stocks from the dashboard.</div>';
        return panel('Watchlists', '<p class="miq-asset-note">Create multiple named lists, add stocks from here or any stock page, and order each list for your workflow.</p>' + create + body, true);
    }

    function noteSelectOptions(items, selectedId, labelKey) {
        return '<option value="">None</option>' + items.map(function (item) {
            return '<option value="' + Number(item.id) + '"' + (Number(selectedId) === Number(item.id) ? ' selected' : '') + '>' +
                escapeHtml(item[labelKey] || item.name) + '</option>';
        }).join('');
    }

    function renderNotes() {
        var notes = workspaceItems('notes');
        var editing = notes.find(function (note) { return Number(note.id) === Number(editingNoteId); }) || {};
        var form = '<form class="miq-note-editor" data-note-form><input type="hidden" name="id" value="' + (editing.id ? Number(editing.id) : '') + '">' +
            '<div class="miq-form-row"><label>Stock code<input class="form-control" name="stock_code" maxlength="40" value="' + escapeHtml(editing.stock_code || '') + '" placeholder="AAPL"></label>' +
            '<label>Saved chart<select class="form-control" name="chart_id">' + noteSelectOptions(workspaceItems('charts'), editing.chart_id, 'name') + '</select></label>' +
            '<label>Pine script<select class="form-control" name="script_id">' + noteSelectOptions(workspaceItems('scripts'), editing.script_id, 'name') + '</select></label></div>' +
            '<label>Title<input class="form-control" name="title" maxlength="160" value="' + escapeHtml(editing.title || '') + '" required></label>' +
            '<label>Research note<textarea class="form-control" name="body" rows="7" maxlength="20000" required>' + escapeHtml(editing.body || '') + '</textarea></label>' +
            '<div class="miq-management-actions"><button class="btn btn-primary" type="submit">' + (editing.id ? 'Update note' : 'Save note') + '</button>' +
            (editing.id ? '<button class="btn btn-outline-secondary" type="button" data-workspace-action="note-cancel">Cancel edit</button>' : '') + '</div></form>';
        var rows = notes.length ? '<div class="miq-management-stack">' + notes.map(function (note) {
            var linked = [note.stock_code, note.chart_name, note.script_name].filter(Boolean).join(' · ') || 'Private research';
            return '<article class="miq-management-card" data-note-id="' + Number(note.id) + '"><div class="miq-management-heading"><div><h3>' +
                escapeHtml(note.title) + '</h3><small>' + escapeHtml(linked) + ' · updated ' + escapeHtml(humanDate(note.updated_at)) +
                '</small></div><div class="miq-management-actions"><button class="btn btn-sm btn-outline-primary" type="button" data-workspace-action="note-edit">Edit</button>' +
                '<button class="btn btn-sm btn-outline-danger" type="button" data-workspace-action="note-delete">Delete</button></div></div><p class="miq-note-copy">' +
                escapeHtml(note.body) + '</p></article>';
        }).join('') + '</div>' : '<div class="miq-empty-state">No research notes yet. Link your first note to a stock, chart, or Pine script.</div>';
        return panel('Private research journal', form + rows, true);
    }

    function renderAlerts() {
        var alerts = workspaceItems('alerts');
        var form = '<form class="miq-alert-create" data-alert-form><input class="form-control" name="code" maxlength="40" placeholder="Stock code" required>' +
            '<select class="form-control" name="condition_type"><option value="above">At or above</option><option value="below">At or below</option></select>' +
            '<input class="form-control" name="target_price" type="number" min="0.0001" max="1000000000000" step="any" placeholder="Target price" required><button class="btn btn-primary" type="submit">Create alert</button></form>';
        var rows = alerts.length ? '<div class="miq-management-stack">' + alerts.map(function (alert) {
            var next = alert.status === 'active' ? 'disabled' : 'active';
            return '<article class="miq-management-card miq-alert-row" data-alert-id="' + Number(alert.id) + '"><div><a href="stockinfo?code=' +
                encodeURIComponent(alert.code) + '"><strong>' + escapeHtml(alert.code) + '</strong></a><span>' +
                (alert.condition_type === 'above' ? 'At or above ' : 'At or below ') + escapeHtml(formatPrice(alert.target_price)) +
                '</span><small>Last price ' + escapeHtml(alert.last_price == null ? 'not checked' : formatPrice(alert.last_price)) +
                (alert.triggered_at ? ' · triggered ' + escapeHtml(humanDate(alert.triggered_at)) : '') + '</small></div><span class="miq-alert-status is-' +
                escapeHtml(alert.status) + '">' + escapeHtml(alert.status) + '</span><div class="miq-management-actions"><button class="btn btn-sm btn-outline-primary" type="button" data-workspace-action="alert-status" data-next-status="' +
                next + '">' + (alert.status === 'active' ? 'Pause' : 'Reactivate') + '</button><button class="btn btn-sm btn-outline-danger" type="button" data-workspace-action="alert-delete">Delete</button></div></article>';
        }).join('') + '</div>' : '<div class="miq-empty-state">No price alerts yet.</div>';
        return panel('Price alerts', '<p class="miq-asset-note">Alerts are checked by the scheduled server job and appear in your notifications when triggered. <a href="account_settings#miq-notification-settings">Manage push notification preferences</a>.</p>' + form + rows, true);
    }

    function renderNotifications() {
        var notifications = workspaceItems('notifications');
        var toolbar = '<div class="miq-management-toolbar"><span>' + workspaceCount('notifications_unread') + ' unread</span><div class="miq-management-actions"><a class="btn btn-sm btn-outline-secondary" href="account_settings#miq-notification-settings">Push settings</a><button class="btn btn-sm btn-outline-primary" type="button" data-workspace-action="notifications-read-all">Mark all read</button></div></div>';
        var rows = notifications.length ? '<div class="miq-notification-list">' + notifications.map(function (notification) {
            var contentHtml = '<strong>' + escapeHtml(notification.title) + '</strong><span>' + escapeHtml(notification.message) + '</span><small>' + escapeHtml(humanDate(notification.created_at)) + '</small>';
            return '<article class="miq-notification-row' + (notification.read_at ? '' : ' is-unread') + '" data-notification-id="' + Number(notification.id) + '">' +
                (notification.link_url ? '<a href="' + escapeHtml(notification.link_url) + '" data-workspace-action="notification-open">' + contentHtml + '</a>' : '<div>' + contentHtml + '</div>') +
                (!notification.read_at ? '<button class="btn btn-sm btn-link" type="button" data-workspace-action="notification-read">Mark read</button>' : '') + '</article>';
        }).join('') + '</div>' : '<div class="miq-empty-state">No notifications yet.</div>';
        return panel('Notifications', toolbar + rows, true);
    }

    function renderBookmarks() {
        var bookmarks = workspaceItems('bookmarks');
        var rows = bookmarks.length ? '<div class="miq-management-stack">' + bookmarks.map(function (bookmark) {
            return '<article class="miq-management-card" data-bookmark-idea="' + Number(bookmark.idea_id) + '"><div class="miq-management-heading"><div><a href="community?idea=' +
                Number(bookmark.idea_id) + '"><h3>' + escapeHtml(bookmark.title) + '</h3></a><small>' +
                escapeHtml([bookmark.code, bookmark.direction, bookmark.timeframe].filter(Boolean).join(' · ')) + '</small></div>' +
                '<button class="btn btn-sm btn-outline-danger" type="button" data-workspace-action="bookmark-remove">Remove bookmark</button></div></article>';
        }).join('') + '</div>' : '<div class="miq-empty-state">Bookmark useful published ideas to find them here.</div>';
        return panel('Community bookmarks', rows, true);
    }

    function renderSimpleList(title, items, renderer) {
        if (!items || !items.length) return panel(title, '<div class="miq-empty-state">Nothing saved here yet.</div>', true);
        return panel(title, '<ul class="miq-workspace-list">' + items.map(function (item) { return '<li>' + renderer(item) + '</li>'; }).join('') + '</ul>', true);
    }

    function render(tab) {
        if (!workspace) return;
        if (!communityEnabled && (tab === 'ideas' || tab === 'bookmarks')) tab = 'overview';
        if (tab === 'watchlists') content.innerHTML = renderWatchlists();
        else if (tab === 'notes') content.innerHTML = renderNotes();
        else if (tab === 'alerts') content.innerHTML = renderAlerts();
        else if (tab === 'notifications') content.innerHTML = renderNotifications();
        else if (tab === 'bookmarks') content.innerHTML = renderBookmarks();
        else if (tab === 'charts') content.innerHTML = renderCharts();
        else if (tab === 'scripts') content.innerHTML = renderScripts();
        else if (tab === 'presets') {
            content.innerHTML = renderSimpleList('Screener presets', workspace.screener_presets, function (item) {
                return '<a href="screener?preset=' + encodeURIComponent(item.client_key) + '"><strong>' + escapeHtml(item.name) + '</strong></a>' +
                    (item.is_default === true || item.is_default === 1 || item.is_default === '1' ? '<span class="miq-asset-badge">Default</span>' : '') +
                    assetMeta(['revision ' + Number(item.revision || 1), 'updated ' + humanDate(item.updated_at)]);
            });
        } else if (tab === 'searches') {
            content.innerHTML = renderSimpleList('Recent searches', recentSearches(), function (item) {
                return '<a href="stockinfo?code=' + encodeURIComponent(item.code) + '">' + escapeHtml(item.code) + '</a>' + assetMeta([item.display_name || item.exchange || '', humanDate(item.searched_at)]);
            });
        } else if (tab === 'watchlists') {
            content.innerHTML = renderSimpleList('Watchlists', workspace.watchlists, function (item) {
                return '<strong>' + escapeHtml(item.name) + '</strong><span>' + (item.items || []).map(function (stock) { return '<a href="stockinfo?code=' + encodeURIComponent(stock.code) + '">' + escapeHtml(stock.code) + '</a>'; }).join(' · ') + '</span>';
            });
        } else if (tab === 'ideas') {
            content.innerHTML = renderSimpleList('Community ideas', workspace.ideas, function (item) {
                return '<strong>' + escapeHtml(item.title) + '</strong>' + assetMeta([item.direction, item.status, humanDate(item.updated_at)]);
            });
        } else content.innerHTML = renderOverview();
        Array.prototype.forEach.call(document.querySelectorAll('[data-workspace-tab]'), function (button) {
            button.classList.toggle('active', button.getAttribute('data-workspace-tab') === tab);
        });
    }

    function loadCharts(reset) {
        if (chartsState.loading) return Promise.resolve();
        chartsState.loading = true;
        if (reset) {
            chartsState.items = [];
            chartsState.page = 0;
        }
        var nextPage = chartsState.page + 1;
        return window.MIQAccount.request('list_charts', { page: nextPage, limit: 50, search: chartsState.search, kind: chartsState.kind }, 'GET').then(function (body) {
            chartsState.items = chartsState.items.concat(body.charts || []);
            chartsState.page = nextPage;
            chartsState.total = Number(body.total || 0);
            chartsState.loading = false;
            if (activeTab === 'charts') render('charts');
        }).catch(function (error) {
            chartsState.loading = false;
            showStatus(error.message, 'danger');
        });
    }

    function loadScripts(reset) {
        if (scriptsState.loading) return Promise.resolve();
        scriptsState.loading = true;
        if (reset) {
            scriptsState.items = [];
            scriptsState.page = 0;
        }
        var nextPage = scriptsState.page + 1;
        return window.MIQAccount.request('list_scripts', { page: nextPage, limit: 50, search: scriptsState.search, status: scriptsState.status }, 'GET').then(function (body) {
            scriptsState.items = scriptsState.items.concat(body.scripts || []);
            scriptsState.page = nextPage;
            scriptsState.total = Number(body.total || 0);
            scriptsState.loading = false;
            if (activeTab === 'scripts') render('scripts');
        }).catch(function (error) {
            scriptsState.loading = false;
            showStatus(error.message, 'danger');
        });
    }

    function refreshWorkspaceAndTab(message) {
        if (message) showStatus(message, 'success');
        return window.MIQAccount.request('workspace', {}, 'GET').then(function (body) {
            workspace = body.workspace;
            if (activeTab === 'charts') return loadCharts(true);
            if (activeTab === 'scripts') return loadScripts(true);
            render(activeTab);
            return null;
        });
    }

    function closestAsset(target, selector) {
        return target && target.closest ? target.closest(selector) : null;
    }

    function renderVersions(container, versions, type, asset) {
        container.hidden = false;
        if (!versions.length) {
            container.innerHTML = '<span>No explicit versions yet. Autosaves update the current draft without creating versions.</span>';
            return;
        }
        container.innerHTML = '<strong>Restorable versions</strong>' + versions.map(function (version) {
            return '<button class="btn btn-sm btn-outline-secondary" type="button" data-asset-action="restore-' + type + '-version" data-version-id="' + Number(version.id) + '" data-asset-revision="' + Number(asset.revision) + '">Revision ' + Number(version.revision) + ' · ' + escapeHtml(humanDate(version.created_at)) + '</button>';
        }).join('');
    }

    function downloadText(filename, text) {
        var url = URL.createObjectURL(new Blob([text], { type: 'text/plain;charset=utf-8' }));
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 0);
    }

    function safeFileName(name) {
        return String(name || 'pine-script').trim().replace(/[^a-z0-9._-]+/gi, '-').replace(/^-+|-+$/g, '') || 'pine-script';
    }

    function importRecentScripts() {
        var recent = [];
        try { recent = JSON.parse(window.localStorage.getItem('sce-pine-recent-scripts') || '[]'); } catch (error) { recent = []; }
        if (!Array.isArray(recent) || !recent.length) {
            showStatus('No browser-recent Pine scripts were found.', 'info');
            return;
        }
        var chain = Promise.resolve();
        var imported = 0;
        recent.slice(0, 8).forEach(function (item) {
            if (!item || !String(item.code || '').trim()) return;
            chain = chain.then(function () {
                return window.MIQAccount.saveScript({
                    asset_key: window.MIQAccount.makeAssetKey(),
                    name: item.title || 'Imported Pine script',
                    source_code: item.code,
                    status: 'draft',
                    create_version: true
                }).then(function () { imported += 1; });
            });
        });
        chain.then(function () { return refreshWorkspaceAndTab(imported + ' browser script(s) imported.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
    }

    content.addEventListener('submit', function (event) {
        var watchlistCreate = event.target.closest('[data-watchlist-create]');
        var watchlistRename = event.target.closest('[data-watchlist-rename]');
        var watchlistAdd = event.target.closest('[data-watchlist-add]');
        var noteForm = event.target.closest('[data-note-form]');
        var alertForm = event.target.closest('[data-alert-form]');
        var chartFilter = event.target.closest('[data-chart-filter]');
        var scriptFilter = event.target.closest('[data-script-filter]');
        var chartRename = event.target.closest('[data-rename-chart-form]');
        var scriptRename = event.target.closest('[data-rename-script-form]');
        if (watchlistCreate) {
            event.preventDefault();
            window.MIQAccount.action('create_watchlist', { name: watchlistCreate.elements.name.value.trim() })
                .then(function () { return refreshWorkspaceAndTab('Watchlist created.'); })
                .catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (watchlistRename) {
            event.preventDefault();
            var renameList = closestAsset(watchlistRename, '[data-watchlist-id]');
            window.MIQAccount.action('rename_watchlist', {
                watchlist_id: Number(renameList.getAttribute('data-watchlist-id')),
                name: watchlistRename.elements.name.value.trim()
            }).then(function () { return refreshWorkspaceAndTab('Watchlist renamed.'); })
                .catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (watchlistAdd) {
            event.preventDefault();
            var addList = closestAsset(watchlistAdd, '[data-watchlist-id]');
            window.MIQAccount.action('add_watchlist_item', {
                watchlist_id: Number(addList.getAttribute('data-watchlist-id')),
                code: watchlistAdd.elements.code.value.trim().toUpperCase()
            }).then(function () { return refreshWorkspaceAndTab('Stock added to watchlist.'); })
                .catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (noteForm) {
            event.preventDefault();
            window.MIQAccount.action('save_note', {
                id: Number(noteForm.elements.id.value || 0),
                stock_code: noteForm.elements.stock_code.value.trim().toUpperCase(),
                chart_id: Number(noteForm.elements.chart_id.value || 0),
                script_id: Number(noteForm.elements.script_id.value || 0),
                title: noteForm.elements.title.value.trim(),
                body: noteForm.elements.body.value.trim()
            }).then(function () {
                editingNoteId = 0;
                return refreshWorkspaceAndTab('Research note saved.');
            }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (alertForm) {
            event.preventDefault();
            window.MIQAccount.action('save_alert', {
                code: alertForm.elements.code.value.trim().toUpperCase(),
                condition_type: alertForm.elements.condition_type.value,
                target_price: alertForm.elements.target_price.value
            }).then(function () {
                offerNotificationContext('price_alerts', 'price_alert');
                return refreshWorkspaceAndTab('Price alert created.');
            })
                .catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (chartFilter) {
            event.preventDefault();
            chartsState.search = chartFilter.elements.search.value.trim();
            chartsState.kind = chartFilter.elements.kind.value;
            loadCharts(true);
        } else if (scriptFilter) {
            event.preventDefault();
            scriptsState.search = scriptFilter.elements.search.value.trim();
            scriptsState.status = scriptFilter.elements.status.value;
            loadScripts(true);
        } else if (chartRename) {
            event.preventDefault();
            var chartRowElement = closestAsset(chartRename, '[data-chart-id]');
            var chartId = Number(chartRowElement.getAttribute('data-chart-id'));
            var chartAsset = chartsState.items.filter(function (item) { return Number(item.id) === chartId; })[0];
            window.MIQAccount.action('rename_chart', { id: chartId, name: chartRename.elements.name.value.trim(), expected_revision: chartAsset && chartAsset.revision }).then(function () { return refreshWorkspaceAndTab('Chart renamed.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (scriptRename) {
            event.preventDefault();
            var scriptRowElement = closestAsset(scriptRename, '[data-script-id]');
            var scriptId = Number(scriptRowElement.getAttribute('data-script-id'));
            var scriptAsset = scriptsState.items.filter(function (item) { return Number(item.id) === scriptId; })[0];
            window.MIQAccount.action('rename_script', { id: scriptId, name: scriptRename.elements.name.value.trim(), expected_revision: scriptAsset && scriptAsset.revision }).then(function () { return refreshWorkspaceAndTab('Script renamed.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        }
    });

    content.addEventListener('click', function (event) {
        var workspaceButton = event.target.closest('[data-workspace-action]');
        if (workspaceButton) {
            var workspaceAction = workspaceButton.getAttribute('data-workspace-action');
            var watchlistElement = closestAsset(workspaceButton, '[data-watchlist-id]');
            var watchlistId = watchlistElement ? Number(watchlistElement.getAttribute('data-watchlist-id')) : 0;
            var watchlistRow = closestAsset(workspaceButton, '[data-watchlist-code]');
            var watchlistCode = watchlistRow ? watchlistRow.getAttribute('data-watchlist-code') : '';
            var noteElement = closestAsset(workspaceButton, '[data-note-id]');
            var noteId = noteElement ? Number(noteElement.getAttribute('data-note-id')) : 0;
            var alertElement = closestAsset(workspaceButton, '[data-alert-id]');
            var alertId = alertElement ? Number(alertElement.getAttribute('data-alert-id')) : 0;
            if (workspaceAction === 'watchlist-rename-show') {
                watchlistElement.querySelector('[data-watchlist-rename]').hidden = false;
            } else if (workspaceAction === 'watchlist-rename-cancel') {
                watchlistElement.querySelector('[data-watchlist-rename]').hidden = true;
            } else if (workspaceAction === 'watchlist-delete') {
                if (window.confirm('Delete this watchlist and all of its items?')) {
                    window.MIQAccount.action('delete_watchlist', { watchlist_id: watchlistId })
                        .then(function () { return refreshWorkspaceAndTab('Watchlist deleted.'); })
                        .catch(function (error) { showStatus(error.message, 'danger'); });
                }
            } else if (workspaceAction === 'watchlist-remove') {
                window.MIQAccount.action('remove_watchlist_item', { watchlist_id: watchlistId, code: watchlistCode })
                    .then(function () { return refreshWorkspaceAndTab(watchlistCode + ' removed.'); })
                    .catch(function (error) { showStatus(error.message, 'danger'); });
            } else if (workspaceAction === 'watchlist-up' || workspaceAction === 'watchlist-down') {
                var list = workspaceItems('watchlists').find(function (item) { return Number(item.id) === watchlistId; });
                var codes = list ? list.items.map(function (item) { return item.code; }) : [];
                var codeIndex = codes.indexOf(watchlistCode);
                var swapIndex = workspaceAction === 'watchlist-up' ? codeIndex - 1 : codeIndex + 1;
                if (codeIndex >= 0 && swapIndex >= 0 && swapIndex < codes.length) {
                    var swapped = codes[swapIndex];
                    codes[swapIndex] = codes[codeIndex];
                    codes[codeIndex] = swapped;
                    window.MIQAccount.action('reorder_watchlist_items', { watchlist_id: watchlistId, codes: codes })
                        .then(function () { return refreshWorkspaceAndTab(); })
                        .catch(function (error) { showStatus(error.message, 'danger'); });
                }
            } else if (workspaceAction === 'note-edit') {
                editingNoteId = noteId;
                render('notes');
                var editor = content.querySelector('[data-note-form]');
                if (editor) editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else if (workspaceAction === 'note-cancel') {
                editingNoteId = 0;
                render('notes');
            } else if (workspaceAction === 'note-delete') {
                if (window.confirm('Delete this private research note?')) {
                    window.MIQAccount.action('delete_note', { id: noteId }).then(function () {
                        if (editingNoteId === noteId) editingNoteId = 0;
                        return refreshWorkspaceAndTab('Research note deleted.');
                    }).catch(function (error) { showStatus(error.message, 'danger'); });
                }
            } else if (workspaceAction === 'alert-status') {
                window.MIQAccount.action('set_alert_status', { id: alertId, status: workspaceButton.getAttribute('data-next-status') })
                    .then(function () { return refreshWorkspaceAndTab('Price alert updated.'); })
                    .catch(function (error) { showStatus(error.message, 'danger'); });
            } else if (workspaceAction === 'alert-delete') {
                if (window.confirm('Delete this price alert?')) {
                    window.MIQAccount.action('delete_alert', { id: alertId })
                        .then(function () { return refreshWorkspaceAndTab('Price alert deleted.'); })
                        .catch(function (error) { showStatus(error.message, 'danger'); });
                }
            } else if (workspaceAction === 'notifications-read-all') {
                window.MIQAccount.action('mark_notification_read', { id: 0 }).then(function () {
                    return refreshWorkspaceAndTab('Notifications marked read.');
                }).catch(function (error) { showStatus(error.message, 'danger'); });
            } else if (workspaceAction === 'notification-read') {
                var notification = closestAsset(workspaceButton, '[data-notification-id]');
                window.MIQAccount.action('mark_notification_read', { id: Number(notification.getAttribute('data-notification-id')) })
                    .then(function () { return refreshWorkspaceAndTab(); })
                    .catch(function (error) { showStatus(error.message, 'danger'); });
            } else if (workspaceAction === 'notification-open') {
                var notificationRow = closestAsset(workspaceButton, '[data-notification-id]');
                window.MIQAccount.action('mark_notification_read', { id: Number(notificationRow.getAttribute('data-notification-id')) }).catch(function () {});
            } else if (workspaceAction === 'bookmark-remove') {
                var bookmark = closestAsset(workspaceButton, '[data-bookmark-idea]');
                window.MIQAccount.action('bookmark_idea', { idea_id: Number(bookmark.getAttribute('data-bookmark-idea')), bookmarked: false })
                    .then(function () { return refreshWorkspaceAndTab('Bookmark removed.'); })
                    .catch(function (error) { showStatus(error.message, 'danger'); });
            }
            if (workspaceAction !== 'notification-open') event.preventDefault();
            return;
        }
        var button = event.target.closest('[data-asset-action]');
        if (!button) return;
        var action = button.getAttribute('data-asset-action');
        var chartElement = closestAsset(button, '[data-chart-id]');
        var scriptElement = closestAsset(button, '[data-script-id]');
        var chartId = chartElement ? Number(chartElement.getAttribute('data-chart-id')) : 0;
        var scriptId = scriptElement ? Number(scriptElement.getAttribute('data-script-id')) : 0;
        if (action === 'load-more-charts') loadCharts(false);
        else if (action === 'load-more-scripts') loadScripts(false);
        else if (action === 'rename-chart') chartElement.querySelector('[data-rename-chart-form]').hidden = false;
        else if (action === 'rename-script') scriptElement.querySelector('[data-rename-script-form]').hidden = false;
        else if (action === 'cancel-inline') button.closest('.miq-asset-inline-form').hidden = true;
        else if (action === 'duplicate-chart') {
            window.MIQAccount.action('duplicate_chart', { id: chartId }).then(function () { return refreshWorkspaceAndTab('Chart duplicated.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'delete-chart') {
            if (window.confirm('Delete this saved chart and its versions?')) window.MIQAccount.action('delete_chart', { id: chartId }).then(function () { return refreshWorkspaceAndTab('Chart deleted.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'chart-versions') {
            window.MIQAccount.request('list_chart_versions', { id: chartId }, 'GET').then(function (body) {
                renderVersions(chartElement.querySelector('[data-chart-versions]'), body.versions || [], 'chart', chartsState.items.filter(function (item) { return Number(item.id) === chartId; })[0]);
            }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'restore-chart-version') {
            window.MIQAccount.action('restore_chart_version', { id: chartId, version_id: Number(button.getAttribute('data-version-id')), expected_revision: Number(button.getAttribute('data-asset-revision')) }).then(function () { return refreshWorkspaceAndTab('Chart version restored.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'duplicate-script') {
            window.MIQAccount.action('duplicate_script', { id: scriptId }).then(function () { return refreshWorkspaceAndTab('Script duplicated.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'archive-script') {
            var archiveAsset = scriptsState.items.filter(function (item) { return Number(item.id) === scriptId; })[0];
            window.MIQAccount.action('archive_script', { id: scriptId, expected_revision: archiveAsset && archiveAsset.revision }).then(function () { return refreshWorkspaceAndTab('Script archived.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'unarchive-script') {
            var unarchiveAsset = scriptsState.items.filter(function (item) { return Number(item.id) === scriptId; })[0];
            window.MIQAccount.action('unarchive_script', { id: scriptId, expected_revision: unarchiveAsset && unarchiveAsset.revision }).then(function () { return refreshWorkspaceAndTab('Script returned to drafts.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'delete-script') {
            if (window.confirm('Delete this Pine script and its versions?')) window.MIQAccount.action('delete_script', { id: scriptId }).then(function () { return refreshWorkspaceAndTab('Script deleted.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'download-script') {
            window.MIQAccount.getScript({ id: scriptId }).then(function (script) { downloadText(safeFileName(script.name) + '.pine', script.source_code); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'script-versions') {
            window.MIQAccount.request('list_script_versions', { id: scriptId }, 'GET').then(function (body) {
                renderVersions(scriptElement.querySelector('[data-script-versions]'), body.versions || [], 'script', scriptsState.items.filter(function (item) { return Number(item.id) === scriptId; })[0]);
            }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'restore-script-version') {
            window.MIQAccount.action('restore_script_version', { id: scriptId, version_id: Number(button.getAttribute('data-version-id')), expected_revision: Number(button.getAttribute('data-asset-revision')) }).then(function () { return refreshWorkspaceAndTab('Script version restored.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'import-recent-scripts') importRecentScripts();
    });

    function load() {
        if (!window.MIQAccount) return;
        window.MIQAccount.request('workspace', {}, 'GET').then(function (body) {
            workspace = body.workspace;
            if (activeTab === 'charts') return loadCharts(true);
            if (activeTab === 'scripts') return loadScripts(true);
            render(activeTab);
            return null;
        }).catch(function (error) {
            content.innerHTML = '<div class="miq-empty-state">' + escapeHtml(error.message) + '</div>';
            showStatus(error.message, 'danger');
        });
    }

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) load();
    });

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-workspace-tab]'), function (button) {
            button.addEventListener('click', function () {
                activeTab = button.getAttribute('data-workspace-tab');
                window.history.replaceState(null, '', 'workspace?tab=' + encodeURIComponent(activeTab));
                if (activeTab === 'charts' && !chartsState.page) loadCharts(true);
                else if (activeTab === 'scripts' && !scriptsState.page) loadScripts(true);
                else render(activeTab);
            });
        });
        load();
    });
}());
