(function () {
    'use strict';
    var content = document.getElementById('miq-workspace-content');
    var status = document.getElementById('miq-workspace-status');
    var workspace = null;
    var activeTab = new URLSearchParams(window.location.search).get('tab') || 'overview';

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function showStatus(message, type) {
        if (!status) return;
        status.hidden = !message;
        status.className = 'alert alert-' + (type || 'info');
        status.textContent = message || '';
    }

    function panel(title, body, wide) {
        return '<section class="miq-workspace-panel' + (wide ? ' miq-workspace-panel-wide' : '') + '"><h2>' + title + '</h2>' + body + '</section>';
    }

    function render(tab) {
        if (!workspace) return;
        var data = workspace;
        var items = [];
        if (tab === 'charts') {
            items.push(panel('Saved charts', list(data.charts, function (item) { return '<a href="/tool?tab=3&stockcode=' + encodeURIComponent(item.code) + '">' + escapeHtml(item.name) + '</a><span>' + escapeHtml(item.code) + ' · updated ' + escapeHtml(item.updated_at) + '</span>'; }), true));
        } else if (tab === 'scripts') {
            items.push(panel('Pine scripts', list(data.scripts, function (item) { return '<strong>' + escapeHtml(item.name) + '</strong><span>' + escapeHtml(item.status) + ' · revision ' + escapeHtml(item.revision) + '</span>'; }), true));
        } else if (tab === 'searches') {
            items.push(panel('Recent searches', list(data.searches, function (item) { return '<a href="/stockinfo?code=' + encodeURIComponent(item.code) + '">' + escapeHtml(item.code) + '</a><span>' + escapeHtml(item.display_name || item.exchange || '') + '</span>'; }), true));
        } else if (tab === 'watchlists') {
            items.push(panel('Watchlists', list(data.watchlists, function (item) { return '<strong>' + escapeHtml(item.name) + '</strong><span>' + (item.items || []).map(function (stock) { return '<a href="/stockinfo?code=' + encodeURIComponent(stock.code) + '">' + escapeHtml(stock.code) + '</a>'; }).join(' · ') + '</span>'; }), true));
        } else if (tab === 'ideas') {
            items.push(panel('Community ideas', list(data.ideas, function (item) { return '<strong>' + escapeHtml(item.title) + '</strong><span>' + escapeHtml(item.direction) + ' · ' + escapeHtml(item.status) + '</span>'; }), true));
        } else {
            items.push(panel('Saved charts', '<p>' + data.charts.length + ' saved chart(s)</p><a href="/workspace?tab=charts">Open saved charts</a>'));
            items.push(panel('Pine scripts', '<p>' + data.scripts.length + ' script(s)</p><a href="/workspace?tab=scripts">Open Pine scripts</a>'));
            items.push(panel('Recent searches', '<p>' + data.searches.length + ' recent search(es)</p><a href="/workspace?tab=searches">Open search history</a>'));
            items.push(panel('Watchlists', '<p>' + data.watchlists.length + ' watchlist(s)</p><a href="/workspace?tab=watchlists">Open watchlists</a>'));
            items.push(panel('Community ideas', '<p>' + data.ideas.length + ' idea(s)</p><a href="/community">Open community ideas</a>'));
            items.push(panel('Privacy by default', '<p>Charts, scripts, and drafts stay private until you explicitly share or submit them.</p>', true));
        }
        content.innerHTML = items.join('');
        Array.prototype.forEach.call(document.querySelectorAll('[data-workspace-tab]'), function (button) {
            button.classList.toggle('active', button.getAttribute('data-workspace-tab') === tab);
        });
    }

    function list(items, renderer) {
        if (!items || !items.length) return '<div class="miq-empty-state">Nothing saved here yet.</div>';
        return '<ul class="miq-workspace-list">' + items.map(function (item) { return '<li>' + renderer(item) + '</li>'; }).join('') + '</ul>';
    }

    function load() {
        if (!window.MIQAccount) return;
        window.MIQAccount.request('workspace', {}, 'GET').then(function (body) {
            workspace = body.workspace;
            render(activeTab);
        }).catch(function (error) {
            content.innerHTML = '<div class="miq-empty-state">' + escapeHtml(error.message) + '</div>';
            showStatus(error.message, 'danger');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-workspace-tab]'), function (button) {
            button.addEventListener('click', function () {
                activeTab = button.getAttribute('data-workspace-tab');
                render(activeTab);
                window.history.replaceState(null, '', '/workspace?tab=' + encodeURIComponent(activeTab));
            });
        });
        load();
    });
}());
