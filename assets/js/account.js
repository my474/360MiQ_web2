(function () {
    'use strict';

    var state = window.__MIQ_ACCOUNT__ || { loggedIn: false };
    var localSearchKey = '360miq-account-recent-searches';
    var sentimentTrendRequests = {};

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
        }).catch(function () {
            var status = container.querySelector('[data-sentiment-status]');
            if (container.getAttribute('data-chart-mode') === 'compact') container.hidden = true;
            else if (status) status.textContent = 'The sentiment trend could not be loaded.';
        });
    }

    function renderSentimentCharts(force, contextType, contextKey) {
        Array.prototype.forEach.call(document.querySelectorAll('[data-sentiment-chart]'), function (container) {
            if (contextType && (container.getAttribute('data-context-type') !== contextType || container.getAttribute('data-context-key') !== contextKey)) return;
            loadSentimentChart(container, !!force);
        });
    }

    function renderPulse() {
        var pulse = document.getElementById('miq-community-pulse');
        if (!pulse || !state.apiUrl) return;
        var contextType = pulse.getAttribute('data-context-type') || state.contextType || 'site';
        var contextKey = pulse.getAttribute('data-context-key') || state.contextKey || 'site';
        var timeframe = pulse.getAttribute('data-timeframe') || '30d';
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
                    renderSentimentCharts(true, contextType, contextKey);
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
        renderSentimentCharts(false);
        mergeLocalSearches();
    });
}());
