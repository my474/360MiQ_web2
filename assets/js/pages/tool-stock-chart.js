(function () {
    'use strict';

    var STOCK_CHART_STORAGE_PREFIX = '360miq-tool-stock-chart';
    var RECENT_STOCKS_STORAGE_KEY = STOCK_CHART_STORAGE_PREFIX + ':recent-stocks';
    var RECENT_STOCKS_LIMIT = 12;
    var activeAutocompleteRequest = null;
    var autocompleteSerial = 0;
    var activeDataRequest = null;
    var dataSerial = 0;
    var stockChart = null;
    var currentCode = '';
    var engineReadyPromise = null;
    var shareLayoutPayload = null;
    var shareLayoutApplied = false;
    var shareLayoutLoading = false;
    var sharedPreviewActive = false;
    var stockMetadataByCode = {};

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

    function setSharedSaveVisible(visible) {
        var button = document.getElementById('toolStockChartSaveShared');
        if (!button) return;
        button.hidden = !visible;
    }

    function closeStockAutocomplete() {
        var $input = $('#toolStockChartCode');
        autocompleteSerial += 1;
        if (activeAutocompleteRequest && activeAutocompleteRequest.readyState !== 4) {
            activeAutocompleteRequest.abort();
        }
        activeAutocompleteRequest = null;
        if ($input.length && $input.autocomplete) $input.autocomplete('close');
    }

    function barDateKey(value) {
        return String(value == null ? '' : value).substring(0, 10);
    }

    function visibleDateRangeForChart(chart) {
        if (!chart || !Array.isArray(chart.bars) || !chart.bars.length) return null;
        var savedRange = chart.document && chart.document.visibleRange;
        if (savedRange && savedRange.from != null && savedRange.to != null) {
            var savedFrom = barDateKey(savedRange.from);
            var savedTo = barDateKey(savedRange.to);
            if (savedFrom && savedTo) return { from: savedFrom, to: savedTo };
        }
        if (chart.visibleIndexRange) {
            var range = chart.visibleIndexRange();
            if (range && range.from >= 0 && range.to >= range.from) {
                return {
                    from: barDateKey(chart.bars[range.from].time),
                    to: barDateKey(chart.bars[range.to].time)
                };
            }
        }
        return null;
    }

    function applyVisibleDateRange(chart, dateRange) {
        if (!chart || !dateRange || !Array.isArray(chart.bars) || !chart.bars.length) return false;
        var bars = chart.bars;
        var from = barDateKey(dateRange.from);
        var to = barDateKey(dateRange.to);
        if (!from || !to) return false;
        if (from > to) {
            var swapped = from;
            from = to;
            to = swapped;
        }

        var firstDate = barDateKey(bars[0].time);
        var lastDate = barDateKey(bars[bars.length - 1].time);
        if (to < firstDate || from > lastDate) {
            if (chart.fitContent) chart.fitContent();
            if (chart.draw) chart.draw();
            return false;
        }

        var fromIndex = 0;
        var toIndex = bars.length - 1;
        for (var i = 0; i < bars.length; i += 1) {
            if (barDateKey(bars[i].time) >= from) {
                fromIndex = i;
                break;
            }
        }
        for (var j = bars.length - 1; j >= 0; j -= 1) {
            if (barDateKey(bars[j].time) <= to) {
                toIndex = j;
                break;
            }
        }
        if (toIndex < fromIndex) return false;

        if (chart.setVisibleIndexRange) chart.setVisibleIndexRange(fromIndex, toIndex);
        else chart.document.visibleRange = { from: bars[fromIndex].time, to: bars[toIndex].time };
        if (chart.document && chart.document.settings) chart.document.settings.dateRangePreset = null;
        if (chart.draw) chart.draw();
        return true;
    }

    function cleanMetadataText(value) {
        return String(value || '').trim();
    }

    function stockMetadataFromSource(source, fallbackCode) {
        source = source || {};
        var code = normalizeCode(source.code || source.symbol || source.value || fallbackCode || '');
        return {
            code: code,
            name_en: cleanMetadataText(source.name_en || source.englishName || source.name || ''),
            name_tc: cleanMetadataText(source.name_tc || source.chineseName || source.name_cn || source.name_sc || '')
        };
    }

    function rememberStockMetadata(source, fallbackCode) {
        var metadata = stockMetadataFromSource(source, fallbackCode);
        if (!metadata.code) return metadata;
        var existing = stockMetadataByCode[metadata.code] || {};
        stockMetadataByCode[metadata.code] = {
            code: metadata.code,
            name_en: metadata.name_en || existing.name_en || '',
            name_tc: metadata.name_tc || existing.name_tc || ''
        };
        return stockMetadataByCode[metadata.code];
    }

    function stockMetadataForCode(code, preferred) {
        var normalizedCode = normalizeCode(code);
        var cached = stockMetadataByCode[normalizedCode] || {};
        var incoming = stockMetadataFromSource(preferred, normalizedCode);
        return {
            code: normalizedCode,
            name_en: incoming.name_en || cached.name_en || '',
            name_tc: incoming.name_tc || cached.name_tc || ''
        };
    }

    function recentStocks() {
        try {
            var saved = JSON.parse(window.localStorage.getItem(RECENT_STOCKS_STORAGE_KEY) || '[]');
            if (!Array.isArray(saved)) return [];
            return saved.map(function (item) {
                return stockMetadataForCode(item && item.code, item);
            }).filter(function (item) {
                return isValidCode(item.code);
            }).slice(0, RECENT_STOCKS_LIMIT);
        } catch (error) {
            return [];
        }
    }

    function recordRecentStock(code, metadata) {
        var entry = stockMetadataForCode(code, metadata);
        if (!isValidCode(entry.code)) return;
        var next = [entry].concat(recentStocks().filter(function (item) {
            return item.code !== entry.code;
        })).slice(0, RECENT_STOCKS_LIMIT);
        try {
            window.localStorage.setItem(RECENT_STOCKS_STORAGE_KEY, JSON.stringify(next));
        } catch (error) {
            return;
        }
        if (stockChart && stockChart.setRecentStocks) stockChart.setRecentStocks(next);
    }

    function hasStockNameMetadata(metadata) {
        return !!(metadata && (metadata.name_en || metadata.name_tc));
    }

    function applyStockMetadata(chart, code, preferred) {
        var metadata = rememberStockMetadata(stockMetadataForCode(code, preferred), code);
        if (chart && chart.setSymbolInfo) chart.setSymbolInfo(metadata);
        else if (chart && chart.document) {
            chart.document.symbol = metadata.code;
            chart.document.symbolInfo = metadata;
            if (chart.updateToolbar) chart.updateToolbar();
        }
        return metadata;
    }

    function ensureEngineReady() {
        if (window.StockChartEngine && window.PineScriptRuntime) return Promise.resolve(window.StockChartEngine);
        if (engineReadyPromise) return engineReadyPromise;

        function loadScript(attribute, source, globalName) {
            if (window[globalName]) return Promise.resolve();
            return new Promise(function (resolve, reject) {
                var existing = document.querySelector('script[' + attribute + ']');
                if (existing) {
                    existing.addEventListener('load', function () {
                        if (window[globalName]) resolve();
                        else reject(new Error(globalName + ' was not registered after script load.'));
                    }, { once: true });
                    existing.addEventListener('error', function () {
                        reject(new Error('Unable to load ' + source + '.'));
                    }, { once: true });
                    return;
                }
                var script = document.createElement('script');
                script.src = source;
                script.async = false;
                script.setAttribute(attribute.replace(/^data-/, 'data-'), 'true');
                script.onload = function () {
                    if (window[globalName]) resolve();
                    else reject(new Error(globalName + ' was not registered after script load.'));
                };
                script.onerror = function () { reject(new Error('Unable to load ' + source + '.')); };
                document.head.appendChild(script);
            });
        }

        engineReadyPromise = loadScript('data-tool-stock-chart-runtime', 'assets/js/stock-chart-engine/pine-script-runtime.js?v=20260717.4', 'PineScriptRuntime')
            .then(function () {
                return loadScript('data-tool-stock-chart-engine', 'assets/js/stock-chart-engine/stock-chart-engine.js?v=20260717.25', 'StockChartEngine');
            })
            .then(function () { return window.StockChartEngine; })
            .catch(function (error) {
                engineReadyPromise = null;
                throw error;
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

    function currentThemeName() {
        if (document.documentElement.getAttribute('data-theme') === 'dark') return 'dark';
        if (window.ThemeController && window.ThemeController.isDark && window.ThemeController.isDark()) return 'dark';
        return 'light';
    }

    function getShareLayoutPayload() {
        if (shareLayoutPayload !== null) return shareLayoutPayload;
        shareLayoutPayload = false;
        var marker = '#sce-layout=';
        var hash = window.location.hash || '';
        if (hash.indexOf(marker) !== 0) return false;
        try {
            shareLayoutPayload = JSON.parse(decodeURIComponent(hash.substring(marker.length)));
        } catch (error) {
            console.error('Unable to parse stock chart share URL:', error);
            shareLayoutPayload = false;
        }
        return shareLayoutPayload;
    }

    function codeFromLayoutPayload(payload) {
        var doc = payload && (payload.document || payload.chart || payload);
        return normalizeCode(doc && doc.symbol || 'SPY');
    }

    function layoutWithoutEmbeddedData(payload) {
        var copy = JSON.parse(JSON.stringify(payload || {}));
        delete copy.data;
        return copy;
    }

    function endpointCandidates(file) {
        var candidates = [file, '/' + file];
        if (window.location.pathname.slice(-1) === '/') candidates.unshift('../' + file);
        return candidates.filter(function (candidate, index) {
            return candidates.indexOf(candidate) === index;
        });
    }

    function ajaxTextFromCandidates(file, options) {
        options = options || {};
        var candidates = endpointCandidates(file);
        var lastError = null;

        return new Promise(function (resolve, reject) {
            function attempt(index) {
                if (index >= candidates.length) {
                    reject(lastError || new Error('Unable to load ' + file + '.'));
                    return;
                }

                activeDataRequest = $.ajax({
                    url: candidates[index],
                    type: options.type || 'get',
                    data: options.data || {},
                    timeout: options.timeout || 20000,
                    success: function (result) {
                        resolve({ result: result, url: candidates[index] });
                    },
                    error: function (xhr, status, error) {
                        if (status === 'abort') {
                            reject(new Error('Request aborted.'));
                            return;
                        }
                        lastError = new Error(file + ' failed at ' + candidates[index] + ' with status ' + (xhr && xhr.status ? xhr.status : status || error || 'unknown') + '.');
                        attempt(index + 1);
                    }
                });
            }

            attempt(0);
        });
    }

    function requestStockMetadata(code) {
        var normalizedCode = normalizeCode(code);
        var cached = stockMetadataByCode[normalizedCode];
        if (hasStockNameMetadata(cached)) return Promise.resolve(cached);

        var candidates = endpointCandidates('db_autocomplete.php');
        var lastError = null;
        return new Promise(function (resolve) {
            function attempt(index) {
                if (index >= candidates.length) {
                    if (lastError) console.warn(lastError);
                    resolve(stockMetadataForCode(normalizedCode));
                    return;
                }

                $.ajax({
                    url: candidates[index],
                    type: 'post',
                    dataType: 'json',
                    data: { search: normalizedCode, exchange: '' },
                    timeout: 12000,
                    success: function (data) {
                        if (!Array.isArray(data)) {
                            resolve(stockMetadataForCode(normalizedCode));
                            return;
                        }
                        var exact = data.filter(function (item) {
                            return normalizeCode(item && item.code) === normalizedCode;
                        })[0];
                        resolve(rememberStockMetadata(exact || data[0], normalizedCode));
                    },
                    error: function (xhr, status, error) {
                        if (status === 'abort') {
                            resolve(stockMetadataForCode(normalizedCode));
                            return;
                        }
                        lastError = new Error('db_autocomplete.php failed at ' + candidates[index] + ' with status ' + (xhr && xhr.status ? xhr.status : status || error || 'unknown') + '.');
                        attempt(index + 1);
                    }
                });
            }

            attempt(0);
        });
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

    function parseCloseHistoryResult(result) {
        var bars = [];
        var previousDate = '';
        var previousName = '';
        var previousClose = null;

        String(result || '').split(/\r?\n|\r/).forEach(function (line) {
            if (!line || line.indexOf(',') < 0) return;
            if ((line.match(/,/g) || []).length === 1) line = ',' + line;

            var row = line.split(',');
            if (row.length !== 3) return;
            if (row[0] !== '') previousName = row[0];
            else row[0] = previousName;

            var date = decodeStockDate(row[1], previousDate);
            var close = Number(row[2]);
            if (!date || !Number.isFinite(close)) return;

            var open = previousClose === null ? close : previousClose;
            bars.push({
                time: date,
                open: open,
                high: Math.max(open, close),
                low: Math.min(open, close),
                close: close,
                volume: 0
            });
            previousDate = date.substring(0, 10);
            previousClose = close;
        });

        return bars;
    }

    function requestBars(code) {
        return ajaxTextFromCandidates('db_stockquote_get.php', {
            type: 'get',
            data: { data: code, isIEX: '1' },
            timeout: 20000
        }).then(function (response) {
            var bars = parseStockQuoteResult(response.result);
            if (!bars.length) throw new Error('OHLC history was empty for ' + code + '.');
            return { bars: bars, isFallback: false };
        }).catch(function (primaryError) {
            console.warn(primaryError);
            return ajaxTextFromCandidates('db_top10_get.php', {
                type: 'get',
                data: { data: code, isIEX: '1' },
                timeout: 20000
            }).then(function (response) {
                var bars = parseCloseHistoryResult(response.result);
                if (!bars.length) throw new Error('Close history was empty for ' + code + '.');
                return { bars: bars, isFallback: true };
            });
        });
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

    function relativeStrengthSnapshots(chart) {
        if (!chart || !chart.document || !Array.isArray(chart.document.indicators)) return [];
        return chart.document.indicators.filter(function (indicator) {
            return indicator && indicator.type === 'RELATIVE_STRENGTH';
        }).map(function (indicator) {
            return {
                inputs: JSON.parse(JSON.stringify(indicator.inputs || {})),
                styles: JSON.parse(JSON.stringify(indicator.styles || {})),
                visible: indicator.visible !== false
            };
        });
    }

    function restoreRelativeStrengthSnapshots(chart, snapshots) {
        if (!chart || !snapshots.length || !chart.document || chart.document.indicators.some(function (indicator) {
            return indicator.type === 'RELATIVE_STRENGTH';
        })) return;
        snapshots.forEach(function (snapshot) {
            chart.addIndicator('RELATIVE_STRENGTH', {
                placement: 'new',
                inputs: snapshot.inputs,
                styles: snapshot.styles,
                visible: snapshot.visible
            });
        });
    }

    function renderChart(code, bars, options) {
        options = options || {};
        var container = document.getElementById('toolStockChart');
        if (!container || !window.StockChartEngine) return;
        var layoutId = escapeLayoutId(code);
        var shouldLoadStoredLayout = options.load !== false;
        var layoutExisted = shouldLoadStoredLayout && hasStoredLayout(layoutId);
        var carriedRelativeStrength = options.preserveRelativeStrength === false ? [] : relativeStrengthSnapshots(stockChart);

        if (stockChart && stockChart.destroy) stockChart.destroy();
        stockChart = new StockChartEngine.Chart(container, {
            symbol: code,
            symbolInfo: stockMetadataForCode(code, options.symbolInfo),
            interval: 'daily',
            data: bars,
            layoutId: layoutId,
            storagePrefix: STOCK_CHART_STORAGE_PREFIX,
            load: shouldLoadStoredLayout,
            autosave: options.autosave !== false,
            theme: currentThemeName(),
            recentStocks: recentStocks(),
            onComparisonSymbolLoad: function (benchmark) {
                return requestBars(benchmark).then(function (payload) {
                    return payload.bars;
                });
            },
            onRecentStockSelect: function (stock) {
                if (stock && stock.code) loadStockChart(stock.code, stock);
            }
        });
        applyStockMetadata(stockChart, code, options.symbolInfo);
        if (!options.skipStarterStudies) ensureStarterStudies(stockChart, layoutExisted);
        restoreRelativeStrengthSnapshots(stockChart, carriedRelativeStrength);
        if (options.visibleRange) applyVisibleDateRange(stockChart, options.visibleRange);
        if (stockChart.loadRequiredComparisonSymbols) {
            stockChart.loadRequiredComparisonSymbols().catch(function (error) {
                console.warn('Unable to restore Relative Strength benchmark:', error);
            });
        }
        if (options.resetHistory && stockChart.resetHistory) stockChart.resetHistory();
        setTimeout(function () {
            if (stockChart && stockChart.resize) stockChart.resize();
        }, 0);
    }

    function applySharedLayout(code, payload, bars, metadata) {
        var savedLayoutExists = hasStoredLayout(escapeLayoutId(code));
        renderChart(code, bars, {
            load: false,
            autosave: false,
            skipStarterStudies: true,
            preserveRelativeStrength: false,
            symbolInfo: metadata
        });
        stockChart.importLayout(layoutWithoutEmbeddedData(payload));
        applyStockMetadata(stockChart, code, metadata || payload && payload.document && payload.document.symbolInfo);
        stockChart.setTheme(currentThemeName());
        stockChart.updateToolbar();
        if (stockChart.loadRequiredComparisonSymbols) {
            stockChart.loadRequiredComparisonSymbols().catch(function (error) {
                console.warn('Unable to load shared Relative Strength benchmark:', error);
            });
        }
        recordRecentStock(code, metadata || payload && payload.document && payload.document.symbolInfo);
        if (!savedLayoutExists) {
            stockChart.options.autosave = true;
            stockChart.document.settings.autosave = true;
            stockChart.save();
            stockChart.scheduleAutosave();
            sharedPreviewActive = false;
            setSharedSaveVisible(false);
            setStatus('Shared layout saved for ' + code + '.', false);
            return;
        }
        sharedPreviewActive = true;
        setSharedSaveVisible(true);
        setStatus('Shared chart preview. Click Save Layout to replace your saved ' + code + ' layout.', false);
    }

    function loadStockChart(rawCode, metadata) {
        var code = normalizeCode(rawCode);
        var isNewSymbol = code !== currentCode;
        var previousVisibleDateRange = isNewSymbol ? visibleDateRangeForChart(stockChart) : null;
        var input = document.getElementById('toolStockChartCode');
        if (input) input.value = code;
        closeStockAutocomplete();
        var symbolInfo = rememberStockMetadata(metadata, code);
        sharedPreviewActive = false;
        setSharedSaveVisible(false);

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
            return Promise.all([
                requestBars(code),
                requestStockMetadata(code)
            ]).then(function (results) {
                if (requestId !== dataSerial) return;
                var payload = results[0];
                symbolInfo = rememberStockMetadata(results[1] || symbolInfo, code);
                renderChart(code, payload.bars, {
                    symbolInfo: symbolInfo,
                    resetHistory: isNewSymbol,
                    visibleRange: previousVisibleDateRange
                });
                recordRecentStock(code, symbolInfo);
                setStatus(code + ' loaded: ' + payload.bars.length + ' bars' + (payload.isFallback ? ' (close history fallback).' : '.'), false);
            });
        }).catch(function (error) {
            if (requestId !== dataSerial) return;
            console.error(error);
            setStatus('Could not load chart history for ' + code + '.', true);
        });
    }

    function loadSharedChart(payload) {
        if (shareLayoutLoading) return;
        var code = codeFromLayoutPayload(payload);
        var input = document.getElementById('toolStockChartCode');
        if (input) input.value = code;
        closeStockAutocomplete();
        currentCode = code;
        shareLayoutApplied = true;
        shareLayoutLoading = true;
        setStatus('Loading shared chart...', false);

        ensureEngineReady().then(function () {
            return Promise.all([
                requestBars(code),
                requestStockMetadata(code)
            ]).then(function (results) {
                var response = results[0];
                var metadata = results[1];
                applySharedLayout(code, payload, response.bars, metadata);
                shareLayoutLoading = false;
                return null;
            });
        }).catch(function (error) {
            console.error(error);
            setStatus('Could not load shared chart.', true);
            shareLayoutApplied = false;
            shareLayoutLoading = false;
            sharedPreviewActive = false;
            setSharedSaveVisible(false);
        });
    }

    function saveSharedLayout() {
        if (!stockChart || !sharedPreviewActive) return;
        stockChart.options.autosave = true;
        stockChart.document.settings.autosave = true;
        stockChart.save();
        stockChart.scheduleAutosave();
        sharedPreviewActive = false;
        setSharedSaveVisible(false);
        setStatus('Shared layout saved for ' + currentCode + '.', false);
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
        return parts.join(' ' + String.fromCharCode(9679) + ' ');
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
                rememberStockMetadata(ui.item, ui.item.code || ui.item.value);
                loadStockChart(ui.item.code || ui.item.value, ui.item);
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
            if (event.key === 'Enter' || event.which === 13) {
                event.preventDefault();
                closeStockAutocomplete();
                loadStockChart(this.value);
            }
        });
    }

    function initialCode() {
        var params = new URLSearchParams(window.location.search);
        var sharePayload = getShareLayoutPayload();
        if (sharePayload) return codeFromLayoutPayload(sharePayload);
        var configured = window.__TOOL_PAGE_CONFIG && window.__TOOL_PAGE_CONFIG.stockChartCodefromURL;
        if (configured) return normalizeCode(configured);
        if (params.get('stockcode')) return normalizeCode(params.get('stockcode'));
        if (params.get('tab') === '3' && params.get('code')) return normalizeCode(params.get('code'));
        return 'SPY';
    }

    function ensureLoaded() {
        var sharePayload = getShareLayoutPayload();
        if (sharePayload && (!shareLayoutApplied || shareLayoutLoading)) {
            loadSharedChart(sharePayload);
            return;
        }
        var code = normalizeCode(document.getElementById('toolStockChartCode').value || initialCode());
        if (!stockChart || code !== currentCode) loadStockChart(code);
        else if (stockChart.resize) setTimeout(function () { stockChart.resize(); }, 0);
    }

    function activateStockChartTab() {
        var $tab = $('.nav-tabs a[href="#tab-3"]');
        if ($tab.length && typeof $tab.tab === 'function') {
            $tab.tab('show');
            return true;
        }

        var tabLink = document.querySelector('.nav-tabs a[href="#tab-3"]');
        var tabPane = document.getElementById('tab-3');
        if (!tabLink || !tabPane) return false;

        Array.prototype.forEach.call(document.querySelectorAll('.nav-tabs a'), function (link) {
            link.classList.remove('active');
            link.setAttribute('aria-selected', 'false');
        });
        Array.prototype.forEach.call(document.querySelectorAll('.tab-pane'), function (pane) {
            pane.classList.remove('active', 'show');
        });

        tabLink.classList.add('active');
        tabLink.setAttribute('aria-selected', 'true');
        tabPane.classList.add('active', 'show');
        return true;
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
        var saveSharedButton = document.getElementById('toolStockChartSaveShared');
        if (saveSharedButton) {
            saveSharedButton.addEventListener('click', saveSharedLayout);
        }

        var stockChartTabShown = false;
        $('.nav-tabs a[href="#tab-3"]').on('shown.bs.tab', function () {
            stockChartTabShown = true;
            ensureLoaded();
        });

        var params = new URLSearchParams(window.location.search);
        if (params.get('tab') === '3' || window.location.hash === '#tab-3' || getShareLayoutPayload()) {
            activateStockChartTab();
            setTimeout(function () {
                if (!stockChartTabShown) ensureLoaded();
            }, 150);
        }
    });

    window.ToolStockChart = {
        load: loadStockChart
    };
}());
