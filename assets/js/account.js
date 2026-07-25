(function () {
    'use strict';

    var state = window.__MIQ_ACCOUNT__ || { loggedIn: false };
    var localSearchKey = '360miq-account-recent-searches';

    function jsonRequest(action, payload, method) {
        payload = payload || {};
        method = method || 'POST';
        var options = {
            method: method,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        };

        if (method === 'GET') {
            var query = new URLSearchParams(payload);
            return fetch(state.apiUrl + '?action=' + encodeURIComponent(action) + '&' + query.toString(), options).then(parseResponse);
        }

        payload.action = action;
        payload.csrf_token = state.csrfToken || '';
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(payload);
        return fetch(state.apiUrl, options).then(parseResponse);
    }

    function parseResponse(response) {
        return response.text().then(function (text) {
            var body = {};
            try { body = text ? JSON.parse(text) : {}; } catch (error) { body = {}; }
            if (!response.ok || body.error) {
                var requestError = new Error(body.error || 'The account request could not be completed.');
                requestError.status = response.status;
                requestError.body = body;
                requestError.conflict = !!body.conflict;
                throw requestError;
            }
            return body;
        });
    }

    function makeAssetKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') return window.crypto.randomUUID();
        var values = new Uint8Array(16);
        if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
            window.crypto.getRandomValues(values);
        } else {
            for (var index = 0; index < values.length; index += 1) values[index] = Math.floor(Math.random() * 256);
        }
        values[6] = (values[6] & 15) | 64;
        values[8] = (values[8] & 63) | 128;
        var hex = Array.prototype.map.call(values, function (value) { return ('0' + value.toString(16)).slice(-2); }).join('');
        return hex.slice(0, 8) + '-' + hex.slice(8, 12) + '-' + hex.slice(12, 16) + '-' + hex.slice(16, 20) + '-' + hex.slice(20);
    }

    function localSearches() {
        try {
            var items = JSON.parse(window.localStorage.getItem(localSearchKey) || '[]');
            return Array.isArray(items) ? items : [];
        } catch (error) {
            return [];
        }
    }

    function rememberLocalSearch(item) {
        var next = [item].concat(localSearches().filter(function (entry) {
            return String(entry.code).toUpperCase() !== String(item.code).toUpperCase();
        })).slice(0, 20);
        try { window.localStorage.setItem(localSearchKey, JSON.stringify(next)); } catch (error) { /* storage is optional */ }
    }

    function saveSearch(code, metadata) {
        code = String(code || '').trim().toUpperCase();
        if (!code) return Promise.resolve(null);
        var item = {
            code: code,
            exchange: metadata && (metadata.exchange || metadata.exchange_from_TXT) || '',
            display_name: metadata && (metadata.name_en || metadata.name || metadata.name_tc) || '',
            searched_at: new Date().toISOString()
        };
        rememberLocalSearch(item);
        if (!state.loggedIn) return Promise.resolve(item);
        return jsonRequest('save_search', item).catch(function () { return item; });
    }

    function saveChartLayout(code, layout, nameOrOptions) {
        if (!state.loggedIn) return Promise.resolve({ saved: false, reason: 'login_required' });
        var options = typeof nameOrOptions === 'object' && nameOrOptions ? nameOrOptions : { name: nameOrOptions };
        var payload = {
            code: String(code || '').trim().toUpperCase(),
            name: options.name || ('Auto: ' + String(code || '').trim().toUpperCase()),
            layout: layout,
            autosave: options.autosave !== false,
            kind: options.kind || (options.autosave === false ? 'named' : 'workspace'),
            client_updated_at: options.clientUpdatedAt || new Date().toISOString()
        };
        ['id', 'asset_key', 'expected_revision', 'visibility', 'create_version'].forEach(function (key) {
            if (options[key] !== undefined && options[key] !== null && options[key] !== '') payload[key] = options[key];
        });
        return jsonRequest('save_chart', payload);
    }

    function getChart(criteria) {
        if (!state.loggedIn) return Promise.resolve(null);
        criteria = criteria || {};
        return jsonRequest('get_chart', criteria, 'GET').then(function (body) {
            return body.chart || null;
        });
    }

    function preloadChartLayout(code) {
        return getChart({ code: String(code || '').trim().toUpperCase() }).then(function (chart) {
            return chart ? chart.layout : null;
        }).catch(function () { return null; });
    }

    function saveScript(script) {
        if (!state.loggedIn) return Promise.resolve({ saved: false, reason: 'login_required' });
        script = Object.assign({}, script || {});
        if (!script.asset_key && !script.id) script.asset_key = makeAssetKey();
        if (!script.client_updated_at) script.client_updated_at = new Date().toISOString();
        return jsonRequest('save_script', script);
    }

    function getScript(criteria) {
        if (!state.loggedIn) return Promise.resolve(null);
        return jsonRequest('get_script', criteria || {}, 'GET').then(function (body) {
            return body.script || null;
        });
    }

    function accountAction(action, payload, method) {
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required.'));
        return jsonRequest(action, payload || {}, method || 'POST');
    }

    function saveIdea(idea, submit) {
        if (!state.loggedIn) return Promise.resolve({ saved: false, reason: 'login_required' });
        idea = idea || {};
        idea.submit = !!submit;
        return jsonRequest('save_idea', idea);
    }

    function mergeLocalSearches() {
        if (!state.loggedIn) return;
        localSearches().slice(0, 20).forEach(function (item) { saveSearch(item.code, item); });
    }

    function handleGoogleCredential(response) {
        var field = document.getElementById('google-credential');
        var form = document.getElementById('google-login-form');
        if (!field || !form || !response || !response.credential) return;
        field.value = response.credential;
        form.submit();
    }

    function handleGoogleLinkCredential(response) {
        var field = document.getElementById('google-link-credential');
        var form = document.getElementById('google-link-form');
        if (!field || !form || !response || !response.credential) return;
        field.value = response.credential;
        form.submit();
    }

    function bindDisplayNameSuggestions() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-display-name-suggestion]'), function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-display-name-target') || 'display_name';
                var input = document.getElementById(targetId);
                if (!input) return;
                input.value = button.getAttribute('data-display-name-suggestion') || '';
                input.focus();
            });
        });
    }

    function bindSearchTracking() {
        Array.prototype.forEach.call(document.querySelectorAll('form[action="stockinfo"]'), function (form) {
            form.addEventListener('submit', function () {
                var input = form.querySelector('input[name="code"]');
                if (input) saveSearch(input.value, null);
            });
        });
    }

    function renderPulse() {
        var pulse = document.getElementById('miq-community-pulse');
        if (!pulse || !state.apiUrl) return;
        var contextType = pulse.getAttribute('data-context-type') || state.contextType || 'site';
        var contextKey = pulse.getAttribute('data-context-key') || state.contextKey || 'site';
        var timeframe = pulse.getAttribute('data-timeframe') || '1m';
        jsonRequest('pulse', { context_type: contextType, context_key: contextKey, timeframe: timeframe }, 'GET').then(function (body) {
            var counts = body.counts || { bullish: 0, bearish: 0, neutral: 0 };
            pulse.querySelector('[data-count="bullish"]').textContent = counts.bullish || 0;
            pulse.querySelector('[data-count="bearish"]').textContent = counts.bearish || 0;
            pulse.querySelector('[data-count="neutral"]').textContent = counts.neutral || 0;
            pulse.classList.add('is-ready');
        }).catch(function () { pulse.classList.add('is-ready'); });

        Array.prototype.forEach.call(pulse.querySelectorAll('[data-pulse-vote]'), function (button) {
            button.addEventListener('click', function () {
                if (!state.loggedIn) {
                    window.location.href = 'account.php?view=login&return_to=' + encodeURIComponent(window.location.pathname + window.location.search);
                    return;
                }
                jsonRequest('vote', { context_type: contextType, context_key: contextKey, timeframe: timeframe, direction: button.getAttribute('data-pulse-vote') }).then(function (body) {
                    var counts = body.counts || {};
                    ['bullish', 'bearish', 'neutral'].forEach(function (direction) {
                        var target = pulse.querySelector('[data-count="' + direction + '"]');
                        if (target) target.textContent = counts[direction] || 0;
                    });
                    pulse.classList.add('has-voted');
                }).catch(function (error) { window.alert(error.message); });
            });
        });
    }

    window.miqHandleGoogleCredential = handleGoogleCredential;
    window.miqHandleGoogleLinkCredential = handleGoogleLinkCredential;
    window.MIQAccount = {
        state: state,
        request: jsonRequest,
        action: accountAction,
        makeAssetKey: makeAssetKey,
        saveSearch: saveSearch,
        saveChartLayout: saveChartLayout,
        getChart: getChart,
        preloadChartLayout: preloadChartLayout,
        saveScript: saveScript,
        getScript: getScript,
        saveIdea: saveIdea,
        localSearches: localSearches
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindSearchTracking();
        bindDisplayNameSuggestions();
        renderPulse();
        mergeLocalSearches();
    });
}());
