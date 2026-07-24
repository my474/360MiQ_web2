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
                throw new Error(body.error || 'The account request could not be completed.');
            }
            return body;
        });
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

    function saveChartLayout(code, layout, name) {
        if (!state.loggedIn) return Promise.resolve({ saved: false, reason: 'login_required' });
        return jsonRequest('save_chart', {
            code: String(code || '').trim().toUpperCase(),
            name: name || ('Auto: ' + String(code || '').trim().toUpperCase()),
            layout: layout,
            autosave: true
        });
    }

    function preloadChartLayout(code) {
        if (!state.loggedIn) return Promise.resolve(null);
        return jsonRequest('get_chart', { code: String(code || '').trim().toUpperCase() }, 'GET').then(function (body) {
            return body.chart ? body.chart.layout : null;
        }).catch(function () { return null; });
    }

    function saveScript(script) {
        if (!state.loggedIn) return Promise.resolve({ saved: false, reason: 'login_required' });
        return jsonRequest('save_script', script || {});
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
        jsonRequest('pulse', { context_type: contextType, context_key: contextKey }, 'GET').then(function (body) {
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
                jsonRequest('vote', { context_type: contextType, context_key: contextKey, direction: button.getAttribute('data-pulse-vote') }).then(function (body) {
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
        saveSearch: saveSearch,
        saveChartLayout: saveChartLayout,
        preloadChartLayout: preloadChartLayout,
        saveScript: saveScript,
        saveIdea: saveIdea,
        localSearches: localSearches
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindSearchTracking();
        renderPulse();
        mergeLocalSearches();
    });
}());
