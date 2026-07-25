(function () {
    'use strict';

    var state = window.__MIQ_ACCOUNT__ || { loggedIn: false };
    var localSearchKey = '360miq-account-recent-searches';
    var pendingPulseVoteKey = '360miq-pending-community-vote';
    var pendingPulseVoteMaxAge = 24 * 60 * 60 * 1000;
    var sentimentTrendRequests = {};
    var pulseDebugEnabled = new URLSearchParams(window.location.search).get('community_debug') === '1';
    var pulseMarketLabels = {
        NYSE: 'NYSE',
        NASDAQ: 'Nasdaq',
        LSE: 'London',
        TSX: 'Toronto TSX',
        ASX: 'Australia',
        NSE: 'India NSE',
        TYO: 'Tokyo',
        HKEX: 'Hong Kong',
        SHSE: 'Shanghai',
        SZSE: 'Shenzhen'
    };

    function pulseDebug(message, details) {
        if (!pulseDebugEnabled || !window.console) return;
        window.console.info('[360MiQ Community Pulse] ' + message, details || '');
    }

    function livePageContext() {
        var contextType = String(state.contextType || '').toLowerCase();
        var contextKey = String(state.contextKey || '').trim().toUpperCase();

        if (window.__STOCKINFO_PAGE_CONFIG) {
            contextType = 'stock';
            if (typeof window.stockcode !== 'undefined' && window.stockcode) {
                contextKey = String(window.stockcode).trim().toUpperCase();
            } else if (window.__STOCKINFO_PAGE_CONFIG.stockcode) {
                contextKey = String(window.__STOCKINFO_PAGE_CONFIG.stockcode).trim().toUpperCase();
            }
        } else if (window.__MARKET_PAGE_CONFIG && window.__MARKET_PAGE_CONFIG.data) {
            contextType = 'market';
            contextKey = String(window.__MARKET_PAGE_CONFIG.data).trim().toUpperCase();
        }

        return {
            contextType: contextType,
            contextKey: contextKey
        };
    }

    function applyLivePulseContext(pulse) {
        var liveContext = livePageContext();
        var contextType = pulse.getAttribute('data-context-type') || 'site';
        var contextKey = pulse.getAttribute('data-context-key') || 'site';
        var hasLiveContext = (liveContext.contextType === 'stock' || liveContext.contextType === 'market') && liveContext.contextKey;

        if (hasLiveContext) {
            contextType = liveContext.contextType;
            contextKey = liveContext.contextKey;
        }

        if ((contextType !== 'stock' && contextType !== 'market') || !contextKey || contextKey === 'site') {
            contextType = 'site';
            contextKey = 'site';
        }

        state.contextType = contextType;
        state.contextKey = contextKey;
        pulse.setAttribute('data-context-type', contextType);
        pulse.setAttribute('data-context-key', contextKey);

        var periodEnd = pulse.getAttribute('data-period-end') || '';
        var subject = 'Global market';
        var title = 'Global market outlook for the next 30 days' + (periodEnd ? ' ending ' + periodEnd : '') + '?';
        var ariaLabel = 'Thirty-day global community outlook' + (periodEnd ? ' ending ' + periodEnd : '');

        if (contextType === 'stock') {
            subject = contextKey;
            title = 'Your view on ' + contextKey + ' for the next 30 days' + (periodEnd ? ' ending ' + periodEnd : '') + '?';
            ariaLabel = 'Thirty-day community outlook for ' + contextKey + (periodEnd ? ' ending ' + periodEnd : '');
        } else if (contextType === 'market') {
            subject = pulseMarketLabels[contextKey] || contextKey;
            title = 'Your view on ' + subject + ' for the next 30 days' + (periodEnd ? ' ending ' + periodEnd : '') + '?';
            ariaLabel = 'Thirty-day community outlook for ' + subject + (periodEnd ? ' ending ' + periodEnd : '');
        }

        var titleElement = pulse.querySelector('#miq-community-pulse-title');
        var actions = pulse.querySelector('.miq-community-pulse-actions');
        var chart = pulse.querySelector('[data-sentiment-chart]');
        var ideasLink = pulse.querySelector('.miq-community-pulse-link');
        if (titleElement) titleElement.textContent = title;
        if (actions) actions.setAttribute('aria-label', ariaLabel);
        if (chart) {
            chart.setAttribute('data-context-type', contextType);
            chart.setAttribute('data-context-key', contextKey);
            chart.setAttribute('data-subject', subject);
        }
        if (ideasLink) {
            ideasLink.href = contextType === 'stock' ? 'community?code=' + encodeURIComponent(contextKey) : 'community';
        }

        return {
            contextType: contextType,
            contextKey: contextKey,
            subject: subject,
            title: title,
            periodEnd: periodEnd
        };
    }

    function inspectPulse() {
        var pulse = document.getElementById('miq-community-pulse');
        if (!pulse) {
            var missingPulseScript = document.querySelector('script[src*="assets/js/account.js"]');
            return {
                exists: false,
                accountScript: missingPulseScript ? missingPulseScript.src : null
            };
        }

        var styles = window.getComputedStyle(pulse);
        var rect = pulse.getBoundingClientRect();
        var pulseTitle = pulse.querySelector('#miq-community-pulse-title');
        var accountScript = document.querySelector('script[src*="assets/js/account.js"]');
        return {
            exists: true,
            page: {
                path: window.location.pathname,
                stockcode: typeof window.stockcode === 'undefined' ? null : window.stockcode,
                stockConfig: window.__STOCKINFO_PAGE_CONFIG ? window.__STOCKINFO_PAGE_CONFIG.stockcode : null,
                marketConfig: window.__MARKET_PAGE_CONFIG ? window.__MARKET_PAGE_CONFIG.data : null
            },
            account: {
                loggedIn: !!state.loggedIn,
                contextType: state.contextType || null,
                contextKey: state.contextKey || null,
                apiUrl: state.apiUrl || null
            },
            pulse: {
                contextType: pulse.getAttribute('data-context-type'),
                contextKey: pulse.getAttribute('data-context-key'),
                title: pulseTitle ? pulseTitle.textContent : null,
                display: styles.display,
                visibility: styles.visibility,
                opacity: styles.opacity,
                width: Math.round(rect.width),
                height: Math.round(rect.height)
            },
            accountScript: accountScript ? accountScript.src : null
        };
    }

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

    var googleButtonInitialized = false;
    var googleButtonClientId = '';
    var googleButtonMode = '';

    function googleButtonIsDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    function googleButtonCallback(mode) {
        return mode === 'link' ? handleGoogleLinkCredential : handleGoogleCredential;
    }

    function renderGoogleButtons() {
        var targets = document.querySelectorAll('.miq-google-button[data-google-client-id]');
        if (!targets.length || !window.google || !window.google.accounts || !window.google.accounts.id) {
            return false;
        }

        var primary = targets[0];
        var clientId = primary.getAttribute('data-google-client-id') || '';
        var mode = primary.getAttribute('data-google-mode') || 'login';
        if (!clientId) return false;

        if (!googleButtonInitialized || googleButtonClientId !== clientId || googleButtonMode !== mode) {
            window.google.accounts.id.initialize({
                client_id: clientId,
                callback: googleButtonCallback(mode),
                auto_select: false
            });
            googleButtonInitialized = true;
            googleButtonClientId = clientId;
            googleButtonMode = mode;
        }

        var buttonTheme = googleButtonIsDark() ? 'filled_blue' : 'outline';
        Array.prototype.forEach.call(targets, function (target) {
            while (target.firstChild) target.removeChild(target.firstChild);
            window.google.accounts.id.renderButton(target, {
                type: 'standard',
                theme: buttonTheme,
                size: target.getAttribute('data-google-size') || 'large',
                text: 'continue_with',
                shape: 'rectangular',
                logo_alignment: 'left'
            });
            target.setAttribute('data-google-rendered-theme', buttonTheme);
        });
        return true;
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

    function sentimentSvgElement(name, attributes, text) {
        var element = document.createElementNS('http://www.w3.org/2000/svg', name);
        Object.keys(attributes || {}).forEach(function (key) {
            element.setAttribute(key, attributes[key]);
        });
        if (text != null) element.textContent = text;
        return element;
    }

    function sentimentLinePath(points, field, minimumSample, xForIndex, yForValue) {
        var commands = [];
        var drawing = false;
        points.forEach(function (point, index) {
            var value = Number(point[field]);
            if (Number(point.total) < minimumSample || !Number.isFinite(value)) {
                drawing = false;
                return;
            }
            commands.push((drawing ? 'L' : 'M') + xForIndex(index).toFixed(2) + ' ' + yForValue(value).toFixed(2));
            drawing = true;
        });
        return commands.join(' ');
    }

    function clearElement(element) {
        while (element && element.firstChild) element.removeChild(element.firstChild);
    }

    function renderSentimentChart(container, trend) {
        var compact = container.getAttribute('data-chart-mode') === 'compact';
        var plot = container.querySelector('[data-sentiment-plot]');
        var status = container.querySelector('[data-sentiment-status]');
        var legend = container.querySelector('[data-sentiment-legend]');
        var subject = container.getAttribute('data-subject') || 'Market';
        var minimumSample = Number(trend && trend.minimum_sample) || 10;
        var latest = trend && trend.latest ? trend.latest : { total: 0 };

        if (!trend || !trend.available) {
            if (compact) {
                container.hidden = true;
            } else if (status) {
                status.textContent = 'Sentiment trend history is not available yet.';
            }
            return;
        }

        if (!trend.meets_minimum) {
            if (compact) {
                container.hidden = true;
            } else if (status) {
                status.textContent = 'The trend will appear after ' + minimumSample + ' active votes. Current active votes: ' + Number(latest.total || 0) + '.';
            }
            if (legend) legend.hidden = true;
            clearElement(plot);
            return;
        }

        var points = Array.isArray(trend.points) ? trend.points : [];
        if (compact && points.length > 30) points = points.slice(points.length - 30);
        var eligiblePoints = points.filter(function (point) { return Number(point.total) >= minimumSample; });
        if (compact && eligiblePoints.length < 2) {
            container.hidden = true;
            return;
        }

        var width = compact ? 220 : 760;
        var height = compact ? 48 : 280;
        var padding = compact ? { left: 3, right: 3, top: 4, bottom: 4 } : { left: 46, right: 16, top: 14, bottom: 34 };
        var plotWidth = width - padding.left - padding.right;
        var plotHeight = height - padding.top - padding.bottom;
        var xForIndex = function (index) {
            return padding.left + (points.length > 1 ? (index / (points.length - 1)) * plotWidth : plotWidth / 2);
        };
        var percentageY = function (value) { return padding.top + ((100 - value) / 100) * plotHeight; };
        var scoreY = function (value) { return padding.top + ((100 - value) / 200) * plotHeight; };
        var svg = sentimentSvgElement('svg', {
            viewBox: '0 0 ' + width + ' ' + height,
            role: 'img',
            'aria-label': compact
                ? subject + ' rolling 30-day sentiment score trend'
                : subject + ' rolling 30-day bullish, neutral, and bearish vote trend'
        });
        svg.classList.add('miq-sentiment-svg');
        svg.appendChild(sentimentSvgElement('title', {}, svg.getAttribute('aria-label')));

        if (compact) {
            svg.appendChild(sentimentSvgElement('line', {
                x1: padding.left,
                y1: scoreY(0),
                x2: width - padding.right,
                y2: scoreY(0),
                class: 'miq-sentiment-grid-line'
            }));
            var scorePath = sentimentLinePath(points, 'score', minimumSample, xForIndex, scoreY);
            if (scorePath) {
                svg.appendChild(sentimentSvgElement('path', { d: scorePath, class: 'miq-sentiment-line is-score' }));
            }
        } else {
            [0, 50, 100].forEach(function (value) {
                var y = percentageY(value);
                svg.appendChild(sentimentSvgElement('line', {
                    x1: padding.left,
                    y1: y,
                    x2: width - padding.right,
                    y2: y,
                    class: 'miq-sentiment-grid-line'
                }));
                svg.appendChild(sentimentSvgElement('text', {
                    x: padding.left - 8,
                    y: y + 4,
                    'text-anchor': 'end',
                    class: 'miq-sentiment-axis-label'
                }, value + '%'));
            });

            [
                ['bullish_pct', 'is-bullish', 'Bullish'],
                ['neutral_pct', 'is-neutral', 'Neutral'],
                ['bearish_pct', 'is-bearish', 'Bearish']
            ].forEach(function (series) {
                var path = sentimentLinePath(points, series[0], minimumSample, xForIndex, percentageY);
                if (path) svg.appendChild(sentimentSvgElement('path', { d: path, class: 'miq-sentiment-line ' + series[1] }));
                points.forEach(function (point, index) {
                    if (Number(point.total) < minimumSample) return;
                    var circle = sentimentSvgElement('circle', {
                        cx: xForIndex(index),
                        cy: percentageY(Number(point[series[0]])),
                        r: 3,
                        class: 'miq-sentiment-point ' + series[1]
                    });
                    circle.appendChild(sentimentSvgElement('title', {}, point.date + ': ' + series[2] + ' ' + Number(point[series[0]]).toFixed(1) + '% (' + Number(point.total) + ' active votes)'));
                    svg.appendChild(circle);
                });
            });

            if (points.length) {
                [0, Math.floor((points.length - 1) / 2), points.length - 1].filter(function (index, position, indexes) {
                    return indexes.indexOf(index) === position;
                }).forEach(function (index) {
                    svg.appendChild(sentimentSvgElement('text', {
                        x: xForIndex(index),
                        y: height - 9,
                        'text-anchor': index === 0 ? 'start' : (index === points.length - 1 ? 'end' : 'middle'),
                        class: 'miq-sentiment-axis-label'
                    }, points[index].date));
                });
            }
        }

        clearElement(plot);
        plot.appendChild(svg);
        container.hidden = false;
        container.classList.add('is-ready');
        if (legend) legend.hidden = false;
        if (status) {
            if (compact) {
                var score = Number(latest.score || 0);
                status.textContent = '30-day trend: ' + (score > 0 ? '+' : '') + score.toFixed(0) + ' · ' + Number(latest.total || 0) + ' votes';
            } else {
                status.textContent = 'Latest: ' + Number(latest.bullish_pct || 0).toFixed(1) + '% bullish, ' + Number(latest.neutral_pct || 0).toFixed(1) + '% neutral, ' + Number(latest.bearish_pct || 0).toFixed(1) + '% bearish · ' + Number(latest.total || 0) + ' active votes · outlook ending ' + (trend.period_end || '') + '.';
            }
        }
    }

    function sentimentTrendRequest(contextType, contextKey, timeframe, days, force) {
        var cacheKey = [contextType, contextKey, timeframe, days].join('|');
        if (force) delete sentimentTrendRequests[cacheKey];
        if (!sentimentTrendRequests[cacheKey]) {
            sentimentTrendRequests[cacheKey] = jsonRequest('pulse_trend', {
                context_type: contextType,
                context_key: contextKey,
                timeframe: timeframe,
                days: days
            }, 'GET').then(function (body) {
                return body.trend || null;
            }).catch(function (error) {
                delete sentimentTrendRequests[cacheKey];
                throw error;
            });
        }
        return sentimentTrendRequests[cacheKey];
    }

    function loadSentimentChart(container, force) {
        var contextType = container.getAttribute('data-context-type') || 'site';
        var contextKey = container.getAttribute('data-context-key') || 'site';
        var timeframe = container.getAttribute('data-timeframe') || '30d';
        var days = container.getAttribute('data-chart-mode') === 'compact' ? 30 : 90;
        return sentimentTrendRequest(contextType, contextKey, timeframe, days, force).then(function (trend) {
            renderSentimentChart(container, trend);
        }).catch(function (error) {
            var status = container.querySelector('[data-sentiment-status]');
            if (container.getAttribute('data-chart-mode') === 'compact') container.hidden = true;
            else if (status) status.textContent = 'The sentiment trend could not be loaded.';
            pulseDebug('Trend request failed', {
                contextType: contextType,
                contextKey: contextKey,
                status: error && error.status,
                message: error && error.message
            });
        });
    }

    function renderSentimentCharts(force, contextType, contextKey) {
        Array.prototype.forEach.call(document.querySelectorAll('[data-sentiment-chart]'), function (container) {
            if (contextType && (container.getAttribute('data-context-type') !== contextType || container.getAttribute('data-context-key') !== contextKey)) return;
            loadSentimentChart(container, !!force);
        });
    }

    function rememberPendingPulseVote(vote) {
        vote.createdAt = Date.now();
        try {
            window.localStorage.setItem(pendingPulseVoteKey, JSON.stringify(vote));
        } catch (error) {
            pulseDebug('Pending vote could not be stored', { message: error && error.message });
        }
    }

    function pendingPulseVote() {
        var vote = null;
        try {
            vote = JSON.parse(window.localStorage.getItem(pendingPulseVoteKey) || 'null');
        } catch (error) {
            vote = null;
        }
        if (!vote || !vote.createdAt || Date.now() - Number(vote.createdAt) > pendingPulseVoteMaxAge) {
            try { window.localStorage.removeItem(pendingPulseVoteKey); } catch (error) { /* optional storage */ }
            return null;
        }
        return vote;
    }

    function clearPendingPulseVote() {
        try { window.localStorage.removeItem(pendingPulseVoteKey); } catch (error) { /* optional storage */ }
    }

    function updatePulseCounts(pulse, counts) {
        var total = 0;
        ['bullish', 'bearish', 'neutral'].forEach(function (direction) {
            var value = Number(counts && counts[direction]) || 0;
            var target = pulse.querySelector('[data-count="' + direction + '"]');
            if (target) target.textContent = value;
            total += value;
        });
        var zeroState = pulse.querySelector('[data-pulse-zero-state]');
        if (zeroState) zeroState.hidden = total !== 0;
        return total;
    }

    function setPulseBusy(pulse, busy) {
        Array.prototype.forEach.call(pulse.querySelectorAll('[data-pulse-vote]'), function (button) {
            button.disabled = !!busy;
        });
    }

    function selectPulseDirection(pulse, direction) {
        Array.prototype.forEach.call(pulse.querySelectorAll('[data-pulse-vote]'), function (button) {
            button.setAttribute('aria-pressed', button.getAttribute('data-pulse-vote') === direction ? 'true' : 'false');
        });
    }

    function showPulseExplanation(pulse, direction, message) {
        var form = pulse.querySelector('[data-pulse-explanation]');
        var status = pulse.querySelector('[data-pulse-explanation-status]');
        if (!form) return;
        form.setAttribute('data-direction', direction);
        form.hidden = false;
        if (status) status.textContent = message || 'Your vote is counted. Add context if you want to explain it.';
    }

    function submitPulseVote(pulse, context, timeframe, direction, options) {
        options = options || {};
        setPulseBusy(pulse, true);
        return jsonRequest('vote', {
            context_type: context.contextType,
            context_key: context.contextKey,
            timeframe: timeframe,
            direction: direction
        }).then(function (body) {
            setPulseBusy(pulse, false);
            updatePulseCounts(pulse, body.counts || {});
            selectPulseDirection(pulse, direction);
            pulse.classList.add('has-voted');
            showPulseExplanation(
                pulse,
                direction,
                options.replayed ? 'Your saved vote was added after sign-in.' : 'Your vote is counted.'
            );
            renderSentimentCharts(true, context.contextType, context.contextKey);
            pulseDebug('Vote saved', {
                contextType: context.contextType,
                contextKey: context.contextKey,
                direction: direction,
                replayed: !!options.replayed
            });
            return body;
        }, function (error) {
            setPulseBusy(pulse, false);
            throw error;
        });
    }

    function replayPendingPulseVote(pulse, context, timeframe) {
        if (!state.loggedIn) return;
        var pending = pendingPulseVote();
        if (!pending) return;
        if (
            pending.contextType !== context.contextType ||
            String(pending.contextKey).toUpperCase() !== String(context.contextKey).toUpperCase() ||
            pending.timeframe !== timeframe ||
            ['bullish', 'neutral', 'bearish'].indexOf(pending.direction) === -1
        ) return;

        clearPendingPulseVote();
        submitPulseVote(pulse, context, timeframe, pending.direction, { replayed: true }).catch(function (error) {
            var status = pulse.querySelector('[data-pulse-explanation-status]');
            showPulseExplanation(pulse, pending.direction, '');
            if (status) status.textContent = error.message;
            pulseDebug('Saved vote replay failed', {
                status: error && error.status,
                message: error && error.message
            });
        });
    }

    function bindPulseExplanation(pulse, context) {
        var form = pulse.querySelector('[data-pulse-explanation]');
        if (!form) return;
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var input = form.querySelector('[data-pulse-explanation-input]');
            var button = form.querySelector('[data-pulse-explanation-submit]');
            var status = form.querySelector('[data-pulse-explanation-status]');
            var explanation = input ? input.value.trim() : '';
            var direction = form.getAttribute('data-direction') || '';
            if (!explanation) {
                if (status) status.textContent = 'Add one sentence before submitting.';
                if (input) input.focus();
                return;
            }
            if (!state.loggedIn || !direction) {
                if (status) status.textContent = 'Sign in and vote before sharing an explanation.';
                return;
            }

            var directionLabel = direction.charAt(0).toUpperCase() + direction.slice(1);
            var subject = context.contextType === 'site' ? 'the global market' : context.subject;
            var timeframe = context.periodEnd ? 'Next 30 days ending ' + context.periodEnd : 'Next 30 days';
            if (button) button.disabled = true;
            if (status) status.textContent = 'Submitting for moderation...';
            saveIdea({
                code: context.contextType === 'stock' ? context.contextKey : '',
                title: directionLabel + ' on ' + subject + ' for the next 30 days',
                direction: direction,
                timeframe: timeframe,
                thesis: explanation,
                catalyst: '',
                risk: '',
                disclosure: ''
            }, true).then(function () {
                if (input) {
                    input.value = '';
                    input.disabled = true;
                }
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Submitted';
                }
                if (status) status.textContent = 'Submitted for moderation. It will appear after review.';
            }).catch(function (error) {
                if (button) button.disabled = false;
                if (status) status.textContent = error.message;
            });
        });
    }

    function renderPulse() {
        var pulse = document.getElementById('miq-community-pulse');
        if (!pulse) {
            pulseDebug('Pulse element was not rendered');
            return;
        }
        if (!state.apiUrl) {
            pulseDebug('Account API URL is missing', inspectPulse());
            return;
        }
        var resolvedContext = applyLivePulseContext(pulse);
        var contextType = resolvedContext.contextType;
        var contextKey = resolvedContext.contextKey;
        var timeframe = pulse.getAttribute('data-timeframe') || '30d';
        pulseDebug('Resolved context', inspectPulse());
        var countsRequest = jsonRequest('pulse', { context_type: contextType, context_key: contextKey, timeframe: timeframe }, 'GET').then(function (body) {
            var counts = body.counts || { bullish: 0, bearish: 0, neutral: 0 };
            updatePulseCounts(pulse, counts);
            pulse.classList.add('is-ready');
            pulseDebug('Counts loaded', {
                contextType: contextType,
                contextKey: contextKey,
                counts: counts
            });
        }).catch(function (error) {
            pulse.classList.add('is-ready');
            pulseDebug('Counts request failed', {
                contextType: contextType,
                contextKey: contextKey,
                status: error && error.status,
                message: error && error.message
            });
        });

        bindPulseExplanation(pulse, resolvedContext);
        Array.prototype.forEach.call(pulse.querySelectorAll('[data-pulse-vote]'), function (button) {
            button.setAttribute('aria-pressed', 'false');
            button.addEventListener('click', function () {
                var direction = button.getAttribute('data-pulse-vote');
                if (!state.loggedIn) {
                    rememberPendingPulseVote({
                        contextType: contextType,
                        contextKey: contextKey,
                        timeframe: timeframe,
                        direction: direction
                    });
                    window.location.href = 'account.php?view=login&return_to=' + encodeURIComponent(window.location.pathname + window.location.search);
                    return;
                }
                submitPulseVote(pulse, resolvedContext, timeframe, direction).catch(function (error) { window.alert(error.message); });
            });
        });
        countsRequest.then(function () {
            replayPendingPulseVote(pulse, resolvedContext, timeframe);
        });
    }

    window.miqHandleGoogleCredential = handleGoogleCredential;
    window.miqHandleGoogleLinkCredential = handleGoogleLinkCredential;
    window.miqInitGoogleButtons = renderGoogleButtons;
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
        localSearches: localSearches,
        inspectPulse: inspectPulse
    };

    document.documentElement.addEventListener('themechange', function () {
        renderGoogleButtons();
    });

    document.addEventListener('DOMContentLoaded', function () {
        bindSearchTracking();
        bindDisplayNameSuggestions();
        renderGoogleButtons();
        renderPulse();
        renderSentimentCharts(false);
        mergeLocalSearches();
        pulseDebug('Initialization complete', inspectPulse());
    });
}());
