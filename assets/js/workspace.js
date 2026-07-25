(function () {
    'use strict';

    var content = document.getElementById('miq-workspace-content');
    var status = document.getElementById('miq-workspace-status');
    var workspace = null;
    var communityEnabled = !document.body || document.body.getAttribute('data-community-enabled') !== 'false';
    var activeTab = new URLSearchParams(window.location.search).get('tab') || 'overview';
    if (!communityEnabled && activeTab === 'ideas') activeTab = 'overview';
    if (activeTab === 'watchlists') activeTab = 'overview';
    var chartsState = { items: [], page: 0, total: 0, search: '', kind: '', loading: false };
    var scriptsState = { items: [], page: 0, total: 0, search: '', status: '', loading: false };

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
        return '<section class="miq-workspace-panel' + (wide ? ' miq-workspace-panel-wide' : '') + '"><h2>' + escapeHtml(title) + '</h2>' + body + '</section>';
    }

    function humanDate(value) {
        if (!value) return '';
        var parsed = new Date(String(value).replace(' ', 'T') + (String(value).indexOf('Z') >= 0 ? '' : 'Z'));
        return isNaN(parsed.getTime()) ? String(value) : parsed.toLocaleString();
    }

    function assetMeta(parts) {
        return '<span class="miq-asset-meta">' + parts.filter(Boolean).map(escapeHtml).join(' · ') + '</span>';
    }

    function renderOverview() {
        var counts = workspace.counts || {};
        var panels = [
            panel('Saved charts', '<p>' + Number(counts.charts == null ? workspace.charts.length : counts.charts) + ' chart(s)</p><a href="workspace?tab=charts">Manage saved charts</a>'),
            panel('Pine scripts', '<p>' + Number(counts.scripts == null ? workspace.scripts.length : counts.scripts) + ' script(s)</p><a href="workspace?tab=scripts">Manage Pine scripts</a>'),
            panel('Recent searches', '<p>' + Number(counts.searches == null ? workspace.searches.length : counts.searches) + ' recent search(es)</p><a href="workspace?tab=searches">Open search history</a>')
        ];
        if (communityEnabled) {
            panels.push(panel('Community ideas', '<p>' + Number(counts.ideas == null ? workspace.ideas.length : counts.ideas) + ' idea(s)</p><a href="community">Open community ideas</a>'));
        }
        panels.push(panel('Privacy by default', '<p>Charts, scripts, and drafts stay private until you explicitly share or submit them.</p>', true));
        return panels.join('');
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

    function renderSimpleList(title, items, renderer) {
        if (!items || !items.length) return panel(title, '<div class="miq-empty-state">Nothing saved here yet.</div>', true);
        return panel(title, '<ul class="miq-workspace-list">' + items.map(function (item) { return '<li>' + renderer(item) + '</li>'; }).join('') + '</ul>', true);
    }

    function render(tab) {
        if (!workspace) return;
        if (!communityEnabled && tab === 'ideas') tab = 'overview';
        if (tab === 'charts') content.innerHTML = renderCharts();
        else if (tab === 'scripts') content.innerHTML = renderScripts();
        else if (tab === 'searches') {
            content.innerHTML = renderSimpleList('Recent searches', workspace.searches, function (item) {
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
        var chartFilter = event.target.closest('[data-chart-filter]');
        var scriptFilter = event.target.closest('[data-script-filter]');
        var chartRename = event.target.closest('[data-rename-chart-form]');
        var scriptRename = event.target.closest('[data-rename-script-form]');
        if (chartFilter) {
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
            window.MIQAccount.action('archive_script', { id: scriptId }).then(function () { return refreshWorkspaceAndTab('Script archived.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
        } else if (action === 'unarchive-script') {
            window.MIQAccount.action('unarchive_script', { id: scriptId }).then(function () { return refreshWorkspaceAndTab('Script returned to drafts.'); }).catch(function (error) { showStatus(error.message, 'danger'); });
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
