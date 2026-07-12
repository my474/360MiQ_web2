(function () {
    'use strict';

    var STOCK_CHART_STORAGE_PREFIX = '360miq-tool-stock-chart';
    var activeAutocompleteRequest = null;
    var autocompleteSerial = 0;
    var activeDataRequest = null;
    var dataSerial = 0;
    var stockChart = null;
    var currentCode = '';
    var engineReadyPromise = null;

    function normalizeCode(value) {
        var code = String(value || '').trim();
        if (code.indexOf('|') >= 0) code = code.split('|')[0];
        code = code.split(/\s+/)[0] || code;
        return code.toUpperCase();
    }

    function isValidCode(code) {
        return /^[A-Z0-9&.-]{1,15}$/.test(code) && (code.match(/\./g) || []).length <= 1;
    }

    function setStatus(message, isError) {
        var status = document.getElementById('toolStockChartStatus');
        if (!status) return;
        status.textContent = message || '';
        status.classList.toggle('is-error', !!isError);
    }

    function ensureEngineReady() {
        if (window.StockChartEngine) return Promise.resolve(window.StockChartEngine);
        if (engineReadyPromise) return engineReadyPromise;

        engineReadyPromise = new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[data-tool-stock-chart-engine]');
            if (existing) {
                existing.addEventListener('load', function () {
                    if (window.StockChartEngine) resolve(window.StockChartEngine);
                    else {
                        engineReadyPromise = null;
                        reject(new Error('StockChartEngine was not registered after script load.'));
                    }
                }, { once: true });
                existing.addEventListener('error', function () {
                    engineReadyPromise = null;
                    reject(new Error('Unable to load stock-chart-engine.js.'));
                }, { once: true });
                return;
            }

            var script = document.createElement('script');
            script.src = 'assets/js/stock-chart-engine/stock-chart-engine.js?v=20260712.1';
            script.async = false;
            script.setAttribute('data-tool-stock-chart-engine', 'true');
            script.onload = function () {
                if (window.StockChartEngine) resolve(window.StockChartEngine);
                else {
                    engineReadyPromise = null;
                    reject(new Error('StockChartEngine was not registered after script load.'));
                }
            };
            script.onerror = function () {
                engineReadyPromise = null;
                reject(new Error('Unable to load assets/js/stock-chart-engine/stock-chart-engine.js.'));
            };
            document.head.appendChild(script);
        });

        return engineReadyPromise;
    }

    function escapeLayoutId(code) {
        return 'stock-chart-' + code.replace(/[^A-Z0-9.-]/g, '_');
    }

    function hasStoredLayout(layoutId) {
        try {
            return !!window.localStorage.getItem(STOCK_CHART_STORAGE_PREFIX + ':' + layoutId);
        } catch (error) {
            return false;
        }
    }

    function updateHistory(code) {
        if (!code) return;
        var url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('stockcode', code);
        url.searchParams.set('tab', '3');
        window.history.replaceState(null, '', url.toString());
    }

    function decodeStockDate(token, previousDate) {
        var raw = String(token || '').trim();
        if (!raw) return '';
        if (raw.indexOf(':') >= 0) return raw;
        if (raw.indexOf('-') >= 0 && raw.length >= 10) return raw.substring(0, 10);

        var daydict = {
            0: '00', 1: '01', 2: '02', 3: '03', 4: '04', 5: '05', 6: '06', 7: '07', 8: '08', 9: '09',
            A: '10', B: '11', C: '12', D: '13', E: '14', F: '15', G: '16', H: '17', I: '18', J: '19',
            K: '20', L: '21', M: '22', N: '23', O: '24', P: '25', Q: '26', R: '27', S: '28', T: '29',
            U: '30', V: '31'
        };
        var previousDigits = String(previousDate || '').replace(/-/g, '');
        var digits = raw.replace(/-/g, '');
        var last = raw.substring(raw.length - 1);
        var day = daydict[last];

        if (day && raw.length === 1 && previousDigits.length >= 6) {
            digits = previousDigits.substring(0, 6) + day;
        } else if (day && raw.length === 3 && previousDigits.length >= 4) {
            digits = previousDigits.substring(0, 4) + raw.substring(0, 2) + day;
        } else if (day && raw.length === 5) {
            digits = '20' + raw.substring(0, 4) + day;
        }

        if (!/^\d{8}$/.test(digits)) return '';
        return digits.substring(0, 4) + '-' + digits.substring(4, 6) + '-' + digits.substring(6, 8);
    }

    function parseStockQuoteResult(result) {
        var bars = [];
        var previousDate = '';
        String(result || '').split(/\r?\n|\r/).forEach(function (line) {
            if (!line || line.indexOf(',') < 0) return;
            var row = line.split(',');
            if (row.length < 6) return;

            var date = decodeStockDate(row[0], previousDate);
            if (!date) return;
            previousDate = date.substring(0, 10);

            var open = Number(row[1]);
            var high = Number(row[2]);
            var low = Number(row[3]);
            var close = Number(row[4]);
            var volume = Number(row[5]);

            if (![open, high, low, close].every(Number.isFinite)) return;
            bars.push({
                time: date,
                open: open,
                high: high,
                low: low,
                close: close,
                volume: Number.isFinite(volume) ? volume : 0
            });
        });
        return bars;
    }

    function ensureStarterStudies(chart, layoutExisted) {
        if (layoutExisted || !chart || !chart.document || chart.document.indicators.length) return;
        try {
            chart.addIndicator('VOLUME', { placement: 'source' });
            chart.addIndicator('RSI', { placement: 'new' });
        } catch (error) {
            console.error('Unable to add starter studies:', error);
        }
    }

    function renderChart(code, bars) {
        var container = document.getElementById('toolStockChart');
        if (!container || !window.StockChartEngine) return;
        var layoutId = escapeLayoutId(code);
        var layoutExisted = hasStoredLayout(layoutId);

        if (stockChart && stockChart.destroy) stockChart.destroy();
        stockChart = new StockChartEngine.Chart(container, {
            symbol: code,
            interval: 'daily',
            data: bars,
            layoutId: layoutId,
            storagePrefix: STOCK_CHART_STORAGE_PREFIX
        });
        stockChart.document.symbol = code;
        stockChart.updateToolbar();
        ensureStarterStudies(stockChart, layoutExisted);
        setTimeout(function () {
            if (stockChart && stockChart.resize) stockChart.resize();
        }, 0);
    }

    function loadStockChart(rawCode) {
        var code = normalizeCode(rawCode);
        var input = document.getElementById('toolStockChartCode');
        if (input) input.value = code;

        if (!isValidCode(code)) {
            setStatus('Enter a valid stock code.', true);
            return;
        }

        currentCode = code;
        updateHistory(code);
        setStatus('Loading ' + code + '...', false);

        var requestId = ++dataSerial;
        if (activeDataRequest && activeDataRequest.readyState !== 4) activeDataRequest.abort();
        ensureEngineReady().then(function () {
            if (requestId !== dataSerial) return;
            activeDataRequest = $.ajax({
                url: 'db_stockquote_get.php',
                type: 'get',
                data: { data: code, isIEX: '1' },
                timeout: 20000,
                success: function (result) {
                    if (requestId !== dataSerial) return;
                    var bars = parseStockQuoteResult(result);
                    if (!bars.length) {
                        setStatus('No chart history found for ' + code + '.', true);
                        return;
                    }
                    renderChart(code, bars);
                    setStatus(code + ' loaded: ' + bars.length + ' bars.', false);
                },
                error: function (xhr, status) {
                    if (status === 'abort' || requestId !== dataSerial) return;
                    setStatus('Could not load chart history for ' + code + '.', true);
                }
            });
        }).catch(function (error) {
            if (requestId !== dataSerial) return;
            console.error(error);
            setStatus('Could not load stock chart engine asset.', true);
        });
    }

    function preloadEngine() {
        if (window.StockChartEngine) return;
        setStatus('Loading chart engine...', false);
        ensureEngineReady().then(function () {
            setStatus('', false);
        }).catch(function (error) {
            console.error(error);
            setStatus('Could not load stock chart engine asset.', true);
        });
    }

    function formatAutocompleteItem(item) {
        var parts = [item.code, item.name_tc, item.name_en, item.exchange].filter(function (part) {
            return part !== null && part !== undefined && String(part).trim() !== '';
        });
        return parts.join(' | ');
    }

    function initAutocomplete() {
        var $input = $('#toolStockChartCode');
        if (!$input.length || !$input.autocomplete) return;

        $input.autocomplete({
            source: function (request, response) {
                var term = (request.term || '').trim();
                var requestId = ++autocompleteSerial;
                if (activeAutocompleteRequest && activeAutocompleteRequest.readyState !== 4) {
                    activeAutocompleteRequest.abort();
                }
                activeAutocompleteRequest = $.ajax({
                    url: 'db_autocomplete.php',
                    type: 'post',
                    dataType: 'json',
                    data: { search: term, exchange: '' },
                    success: function (data) {
                        if (requestId !== autocompleteSerial) return;
                        if (!Array.isArray(data)) {
                            response([]);
                            return;
                        }
                        response(data.map(function (item) {
                            return {
                                label: formatAutocompleteItem(item),
                                value: item.code,
                                code: item.code,
                                name_tc: item.name_tc,
                                name_en: item.name_en,
                                exchange: item.exchange
                            };
                        }));
                    },
                    error: function (xhr, status) {
                        if (status !== 'abort' && requestId === autocompleteSerial) response([]);
                    }
                });
            },
            delay: 220,
            minLength: 0,
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.code || ui.item.value);
                loadStockChart(ui.item.code || ui.item.value);
            },
            open: function () {
                $('.ui-autocomplete').css('z-index', 10000);
            }
        });

        var autocomplete = $input.data('ui-autocomplete');
        if (autocomplete) {
            autocomplete._renderItem = function (ul, item) {
                return $('<li>')
                    .append($('<div>').text(item.label))
                    .appendTo(ul);
            };
        }

        $input.on('focus', function () {
            $(this).autocomplete('search', $(this).val() || '');
        });

        $input.on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadStockChart(this.value);
            }
        });
    }

    function initialCode() {
        var params = new URLSearchParams(window.location.search);
        var configured = window.__TOOL_PAGE_CONFIG && window.__TOOL_PAGE_CONFIG.stockChartCodefromURL;
        if (configured) return normalizeCode(configured);
        if (params.get('stockcode')) return normalizeCode(params.get('stockcode'));
        if (params.get('tab') === '3' && params.get('code')) return normalizeCode(params.get('code'));
        return 'SPY';
    }

    function ensureLoaded() {
        var code = normalizeCode(document.getElementById('toolStockChartCode').value || initialCode());
        if (!stockChart || code !== currentCode) loadStockChart(code);
        else if (stockChart.resize) setTimeout(function () { stockChart.resize(); }, 0);
    }

    $(function () {
        var input = document.getElementById('toolStockChartCode');
        var loadButton = document.getElementById('toolStockChartLoad');
        if (!input || !loadButton) return;

        input.value = initialCode();
        initAutocomplete();
        preloadEngine();
        loadButton.addEventListener('click', function () {
            loadStockChart(input.value);
        });

        $('.nav-tabs a[href="#tab-3"]').on('shown.bs.tab', function () {
            ensureLoaded();
        });

        var params = new URLSearchParams(window.location.search);
        if (params.get('tab') === '3' || window.location.hash === '#tab-3') {
            setTimeout(ensureLoaded, 150);
        }
    });

    window.ToolStockChart = {
        load: loadStockChart
    };
}());
