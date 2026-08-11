(function () {
    'use strict';

    var STOCK_CHART_STORAGE_PREFIX = '360miq-tool-stock-chart';
    var RECENT_STOCKS_STORAGE_KEY = STOCK_CHART_STORAGE_PREFIX + ':recent-stocks';
    var RECENT_STOCKS_LIMIT = 12;
    var MAX_SHARE_HASH_LENGTH = 20000;
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
    var currentChartAsset = null;
    var chartSyncState = null;
    var pendingAccountScript = null;
    var requestedAccountAssetHandled = false;

    function accountChartPreferences() {
        var accountState = window.MIQAccount && window.MIQAccount.state;
        return accountState && accountState.loggedIn && accountState.preferences
            ? accountState.preferences
            : {};
    }

    function setAccountSyncStatus(message, isError) {
        var status = document.getElementById('toolStockChartAccountSyncStatus');
        if (!status) return;
        status.textContent = message || '';
        status.classList.toggle('is-error', !!isError);
    }

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

    function marketProfile(exchange, code) {
        var normalizedExchange = cleanMetadataText(exchange).toUpperCase().replace(/\s+/g, '');
        var normalizedCode = normalizeCode(code);
        var profiles = {
            NYSE: { timezone: 'America/New_York', currency: 'USD', session: 'regular', symbolType: 'stock' },
            NASDAQ: { timezone: 'America/New_York', currency: 'USD', session: 'regular', symbolType: 'stock' },
            NYSEARCA: { timezone: 'America/New_York', currency: 'USD', session: 'regular', symbolType: 'fund' },
            LSE: { timezone: 'Europe/London', currency: 'GBP', session: 'regular', symbolType: 'stock' },
            TSX: { timezone: 'America/Toronto', currency: 'CAD', session: 'regular', symbolType: 'stock' },
            ASX: { timezone: 'Australia/Sydney', currency: 'AUD', session: 'regular', symbolType: 'stock' },
            NSE: { timezone: 'Asia/Kolkata', currency: 'INR', session: 'regular', symbolType: 'stock' },
            TYO: { timezone: 'Asia/Tokyo', currency: 'JPY', session: 'regular', symbolType: 'stock' },
            HKEX: { timezone: 'Asia/Hong_Kong', currency: 'HKD', session: 'regular', symbolType: 'stock' },
            SHSE: { timezone: 'Asia/Shanghai', currency: 'CNY', session: 'regular', symbolType: 'stock' },
            SHG: { timezone: 'Asia/Shanghai', currency: 'CNY', session: 'regular', symbolType: 'stock' },
            SZSE: { timezone: 'Asia/Shanghai', currency: 'CNY', session: 'regular', symbolType: 'stock' }
        };
        var inferredExchange = normalizedExchange;
        if (!inferredExchange) {
            if (/\.HK$/.test(normalizedCode)) inferredExchange = 'HKEX';
            else if (/\.(L|LSE)$/.test(normalizedCode)) inferredExchange = 'LSE';
            else if (/\.(TO|TSX)$/.test(normalizedCode)) inferredExchange = 'TSX';
            else if (/\.(AX|ASX)$/.test(normalizedCode)) inferredExchange = 'ASX';
            else if (/\.(NS|NSE)$/.test(normalizedCode)) inferredExchange = 'NSE';
            else if (/\.(T|TYO)$/.test(normalizedCode)) inferredExchange = 'TYO';
            else if (/\.(SS|SH)$/.test(normalizedCode)) inferredExchange = 'SHSE';
            else if (/\.SZ$/.test(normalizedCode)) inferredExchange = 'SZSE';
        }
        return Object.assign({
            exchange: cleanMetadataText(exchange),
            timezone: '',
            currency: '',
            basecurrency: '',
            session: '',
            symbolType: '',
            pointvalue: undefined
        }, profiles[inferredExchange] || {});
    }

    function stockMetadataFromSource(source, fallbackCode) {
        source = source || {};
        var code = normalizeCode(source.code || source.symbol || source.value || fallbackCode || '');
        var profile = marketProfile(source.exchange || source.exchange_from_TXT || '', code);
        return Object.assign(profile, {
            code: code,
            name_en: cleanMetadataText(source.name_en || source.englishName || source.name || ''),
            name_tc: cleanMetadataText(source.name_tc || source.chineseName || source.name_cn || source.name_sc || ''),
            exchange: cleanMetadataText(source.exchange || source.exchange_from_TXT || profile.exchange),
            timezone: cleanMetadataText(source.timezone || source.tz || profile.timezone),
            currency: cleanMetadataText(source.currency || profile.currency),
            basecurrency: cleanMetadataText(source.basecurrency || profile.basecurrency),
            session: cleanMetadataText(source.session || profile.session),
            symbolType: cleanMetadataText(source.symbolType || source.type || profile.symbolType),
            pointvalue: Number(source.pointvalue || profile.pointvalue) || undefined,
            mintick: Number(source.mintick || source.tick_size || 0) || undefined
        });
    }

    function rememberStockMetadata(source, fallbackCode) {
        var metadata = stockMetadataFromSource(source, fallbackCode);
        if (!metadata.code) return metadata;
        var existing = stockMetadataByCode[metadata.code] || {};
        stockMetadataByCode[metadata.code] = {
            code: metadata.code,
            name_en: metadata.name_en || existing.name_en || '',
            name_tc: metadata.name_tc || existing.name_tc || '',
            exchange: metadata.exchange || existing.exchange || '',
            timezone: metadata.timezone || existing.timezone || 'UTC',
            currency: metadata.currency || existing.currency || '',
            basecurrency: metadata.basecurrency || existing.basecurrency || '',
            session: metadata.session || existing.session || 'regular',
            symbolType: metadata.symbolType || existing.symbolType || 'stock',
            pointvalue: Number(metadata.pointvalue || existing.pointvalue) || 1,
            mintick: Number(metadata.mintick || existing.mintick) || undefined
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
            name_tc: incoming.name_tc || cached.name_tc || '',
            exchange: incoming.exchange || cached.exchange || '',
            timezone: incoming.timezone || cached.timezone || 'UTC',
            currency: incoming.currency || cached.currency || '',
            basecurrency: incoming.basecurrency || cached.basecurrency || '',
            session: incoming.session || cached.session || 'regular',
            symbolType: incoming.symbolType || cached.symbolType || 'stock',
            pointvalue: Number(incoming.pointvalue || cached.pointvalue) || 1,
            mintick: Number(incoming.mintick || cached.mintick) || undefined
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
        if (window.MIQAccount && window.MIQAccount.saveSearch) window.MIQAccount.saveSearch(entry.code, entry);
    }

    function hasStockNameMetadata(metadata) {
        return !!(metadata && (metadata.name_en || metadata.name_tc));
    }

    function applyStockMetadata(chart, code, preferred, options) {
        var metadata = rememberStockMetadata(stockMetadataForCode(code, preferred), code);
        if (chart && chart.setSymbolInfo) chart.setSymbolInfo(metadata, options);
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

        engineReadyPromise = loadScript('data-tool-stock-chart-backtest', 'assets/js/stock-chart-engine/pine-backtest-engine.js?v=20260718.5', 'PineBacktestEngine')
            .then(function () {
                return loadScript('data-tool-stock-chart-runtime', 'assets/js/stock-chart-engine/pine-script-runtime.js?v=20260726.1', 'PineScriptRuntime');
            })
            .then(function () {
                window.PineScriptRuntime.setBacktestEngine(window.PineBacktestEngine);
            })
            .then(function () {
                return loadScript('data-tool-stock-chart-engine', 'assets/js/stock-chart-engine/stock-chart-engine.js?v=20260726.1', 'StockChartEngine');
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

    function chartLayoutId(code, asset) {
        return asset && asset.kind === 'named' && asset.asset_key
            ? 'saved-chart-' + asset.asset_key
            : escapeLayoutId(code);
    }

    function chartLocalMetaKey(layoutId) {
        return STOCK_CHART_STORAGE_PREFIX + ':sync:' + layoutId;
    }

    function loadChartLocalMeta(layoutId) {
        try {
            var parsed = JSON.parse(window.localStorage.getItem(chartLocalMetaKey(layoutId)) || 'null');
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function saveChartLocalMeta(layoutId, meta) {
        try {
            window.localStorage.setItem(chartLocalMetaKey(layoutId), JSON.stringify(meta || {}));
        } catch (error) { /* local sync metadata is optional */ }
    }

    function updateCurrentChartLabel() {
        var label = document.getElementById('toolStockChartAssetName');
        if (!label) return;
        label.textContent = currentChartAsset && currentChartAsset.name
            ? currentChartAsset.name
            : (currentCode ? 'Synced workspace: ' + currentCode : '');
        label.setAttribute('data-chart-kind', currentChartAsset && currentChartAsset.kind || 'workspace');
    }

    function hideChartConflict() {
        var panel = document.getElementById('toolStockChartConflict');
        if (panel) panel.hidden = true;
    }

    function showChartConflict(serverChart) {
        if (!chartSyncState) return;
        chartSyncState.conflict = serverChart || null;
        var panel = document.getElementById('toolStockChartConflict');
        if (panel) panel.hidden = false;
        setAccountSyncStatus('Sync conflict: this chart changed on another device.', true);
    }

    function persistChartSyncMetaFor(sync, asset, dirty) {
        if (!sync) return;
        saveChartLocalMeta(sync.layoutId, {
            dirty: !!dirty,
            asset: asset ? {
                id: asset.id,
                asset_key: asset.asset_key,
                name: asset.name,
                code: asset.code,
                kind: asset.kind,
                revision: asset.revision,
                updated_at: asset.updated_at
            } : null,
            updatedAt: new Date().toISOString()
        });
    }

    function persistChartSyncMeta(dirty) {
        if (!chartSyncState) return;
        persistChartSyncMetaFor(chartSyncState, chartSyncState.asset || currentChartAsset, dirty);
    }

    function flushChartCloudSave(targetSync) {
        var sync = targetSync || chartSyncState;
        if (!sync || sync.inFlight || sync.conflict || !sync.pendingDocument) return;
        if (!window.MIQAccount || !window.MIQAccount.state || !window.MIQAccount.state.loggedIn) return;
        var documentState = sync.pendingDocument;
        sync.pendingDocument = null;
        sync.inFlight = true;
        var asset = sync.asset || {};
        if (sync === chartSyncState) setAccountSyncStatus('Saving to your workspace…', false);
        window.MIQAccount.saveChartLayout(sync.code, documentState, {
            id: asset.id || undefined,
            asset_key: asset.asset_key || sync.assetKey,
            name: asset.name || ('Auto: ' + sync.code),
            kind: asset.kind || 'workspace',
            expected_revision: asset.revision || undefined,
            autosave: true,
            clientUpdatedAt: documentState.updatedAt || new Date().toISOString()
        }).then(function (response) {
            sync.asset = response && response.chart ? response.chart : sync.asset;
            if (sync.asset && sync.asset.asset_key) sync.assetKey = sync.asset.asset_key;
            sync.inFlight = false;
            sync.retryDelay = 1500;
            persistChartSyncMetaFor(sync, sync.asset, !!sync.pendingDocument);
            if (sync === chartSyncState) {
                currentChartAsset = sync.asset || currentChartAsset;
                updateCurrentChartLabel();
                setAccountSyncStatus(sync.pendingDocument ? 'Saving newer changes…' : 'Synced to your workspace', false);
            }
            if (sync.pendingDocument) flushChartCloudSave(sync);
        }).catch(function (error) {
            sync.inFlight = false;
            if (error && error.conflict) {
                if (!sync.pendingDocument) sync.pendingDocument = documentState;
                sync.conflict = error.body && error.body.chart || null;
                persistChartSyncMetaFor(sync, sync.asset, true);
                if (sync === chartSyncState) showChartConflict(sync.conflict);
                return;
            }
            if (!sync.pendingDocument) sync.pendingDocument = documentState;
            persistChartSyncMetaFor(sync, sync.asset, true);
            sync.retryDelay = Math.min(Math.max(1500, sync.retryDelay || 1500) * 2, 60000);
            clearTimeout(sync.timer);
            sync.timer = setTimeout(function () { flushChartCloudSave(sync); }, sync.retryDelay);
            if (sync === chartSyncState) setAccountSyncStatus('Saved locally; account sync will retry automatically.', true);
            console.warn('Account chart sync failed:', error.message);
        });
    }

    function queueChartCloudSave(doc) {
        if (!chartSyncState) return;
        chartSyncState.pendingDocument = JSON.parse(JSON.stringify(doc));
        persistChartSyncMeta(true);
        clearTimeout(chartSyncState.timer);
        var sync = chartSyncState;
        chartSyncState.timer = setTimeout(function () { flushChartCloudSave(sync); }, 450);
    }

    function legacyAccountChartStorage(code) {
        var layoutId = escapeLayoutId(code);
        var localKey = STOCK_CHART_STORAGE_PREFIX + ':' + layoutId;
        function localLoad() {
            try {
                var raw = window.localStorage.getItem(localKey);
                return raw ? JSON.parse(raw) : null;
            } catch (error) {
                return null;
            }
        }
        function localSave(doc) {
            try {
                window.localStorage.setItem(localKey, JSON.stringify(doc));
                return true;
            } catch (error) {
                return false;
            }
        }
        return {
            load: localLoad,
            save: function (id, doc) {
                var saved = localSave(doc);
                if (window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn) {
                    setAccountSyncStatus('Saving to your workspace…', false);
                    window.MIQAccount.saveChartLayout(code, doc).then(function () {
                        setAccountSyncStatus('Saved to your workspace', false);
                    }).catch(function (error) {
                        setAccountSyncStatus('Local save only; account sync unavailable', true);
                        console.warn('Account chart sync failed:', error.message);
                    });
                } else {
                    setAccountSyncStatus('Sign in to sync this chart across devices', false);
                }
                return saved;
            },
            remove: function () {
                try { window.localStorage.removeItem(localKey); } catch (error) { /* optional local cache */ }
                return true;
            }
        };
    }

    function accountChartStorageV2(code, asset, selectedLayoutId) {
        var layoutId = selectedLayoutId || chartLayoutId(code, asset);
        var localKey = STOCK_CHART_STORAGE_PREFIX + ':' + layoutId;
        return {
            load: function () {
                try {
                    var raw = window.localStorage.getItem(localKey);
                    return raw ? JSON.parse(raw) : null;
                } catch (error) {
                    return null;
                }
            },
            save: function (id, doc) {
                var saved = false;
                try {
                    window.localStorage.setItem(localKey, JSON.stringify(doc));
                    saved = true;
                } catch (error) { /* local storage can be unavailable */ }
                if (window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn) {
                    if (!chartSyncState || !chartSyncState.suppressRemote) queueChartCloudSave(doc);
                } else {
                    setAccountSyncStatus('Sign in to sync this chart across devices', false);
                }
                return saved;
            },
            remove: function () {
                try { window.localStorage.removeItem(localKey); } catch (error) { /* optional local cache */ }
                try { window.localStorage.removeItem(chartLocalMetaKey(layoutId)); } catch (error) { /* optional metadata */ }
                return true;
            }
        };
    }

    function updateHistory(code, asset) {
        if (!code) return;
        var url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('stockcode', code);
        url.searchParams.set('tab', '3');
        if (asset && asset.kind === 'named' && asset.id) url.searchParams.set('chart_id', asset.id);
        else url.searchParams.delete('chart_id');
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
        if (hash.length > MAX_SHARE_HASH_LENGTH) {
            console.error('The stock chart share URL exceeds the supported size.');
            return false;
        }
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

    function sharedLayoutHasPine(payload) {
        var doc = payload && (payload.document || payload.chart || payload);
        return !!(doc && Array.isArray(doc.indicators) && doc.indicators.some(function (indicator) {
            return indicator && indicator.type === 'PINE_SCRIPT';
        }));
    }

    function layoutWithoutEmbeddedData(payload, includePine) {
        var copy = JSON.parse(JSON.stringify(payload || {}));
        delete copy.data;
        var doc = copy.document || copy.chart || copy;
        if (doc && Array.isArray(doc.indicators)) {
            doc.indicators.forEach(function (indicator) {
                if (indicator && indicator.type === 'PINE_SCRIPT') delete indicator.accountScript;
            });
        }
        if (!includePine && doc && Array.isArray(doc.indicators)) {
            var removed = {};
            doc.indicators.forEach(function (indicator) {
                if (indicator && indicator.type === 'PINE_SCRIPT' && indicator.id) removed[indicator.id] = true;
            });
            var changed = true;
            while (changed) {
                changed = false;
                doc.indicators.forEach(function (indicator) {
                    var source = indicator && indicator.source;
                    if (indicator && indicator.id && source && source.kind === 'indicator' && removed[source.indicatorId] && !removed[indicator.id]) {
                        removed[indicator.id] = true;
                        changed = true;
                    }
                });
            }
            doc.indicators = doc.indicators.filter(function (indicator) {
                return !(indicator && removed[indicator.id]);
            });
            if (Array.isArray(doc.drawings)) {
                doc.drawings = doc.drawings.filter(function (drawing) {
                    return !(drawing && drawing.ownerStudyId && removed[drawing.ownerStudyId]);
                });
            }
        }
        return copy;
    }

    function endpointCandidates(file) {
        var candidates = [file];
        var pathParts = window.location.pathname.split('/').filter(Boolean);
        if (window.location.pathname.slice(-1) === '/' && pathParts.length > 1) candidates.unshift('../' + file);
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

    function requestBars(code) {
        return ajaxTextFromCandidates('db_stockquote_get.php', {
            type: 'get',
            data: { data: code, isIEX: '1' },
            timeout: 20000
        }).then(function (response) {
            var bars = parseStockQuoteResult(response.result);
            if (!bars.length) throw new Error('OHLC history was empty for ' + code + '.');
            return { bars: bars, isFallback: false };
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
        if (stockChart && stockChart.flushAutosave) stockChart.flushAutosave();
        if (chartSyncState) {
            chartSyncState.stopped = true;
            clearTimeout(chartSyncState.timer);
            if (chartSyncState.pendingDocument && !chartSyncState.inFlight) flushChartCloudSave(chartSyncState);
        }
        currentChartAsset = options.accountChart || null;
        setAccountSyncStatus(window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn ? 'Account sync enabled' : 'Sign in to sync this chart across devices', false);
        hideChartConflict();
        var layoutId = chartLayoutId(code, currentChartAsset);
        var syncMeta = loadChartLocalMeta(layoutId);
        chartSyncState = {
            code: code,
            layoutId: layoutId,
            assetKey: currentChartAsset && currentChartAsset.asset_key || syncMeta && syncMeta.asset && syncMeta.asset.asset_key || (window.MIQAccount && window.MIQAccount.makeAssetKey ? window.MIQAccount.makeAssetKey() : ''),
            inFlight: false,
            pendingDocument: null,
            conflict: null,
            timer: null,
            retryDelay: 1500,
            suppressRemote: false,
            stopped: false,
            asset: currentChartAsset
        };
        var shouldLoadStoredLayout = options.load !== false;
        var layoutExisted = (shouldLoadStoredLayout && hasStoredLayout(layoutId)) || !!options.document;
        var chartPreferences = accountChartPreferences();
        var preferenceAutosave = chartPreferences.auto_save_charts !== false;
        var carriedRelativeStrength = options.preserveRelativeStrength === false ? [] : relativeStrengthSnapshots(stockChart);

        if (stockChart && stockChart.destroy) stockChart.destroy();
        stockChart = new StockChartEngine.Chart(container, {
            symbol: code,
            symbolInfo: stockMetadataForCode(code, options.symbolInfo),
            interval: 'daily',
            data: bars,
            layoutId: layoutId,
            storage: window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn ? accountChartStorageV2(code, currentChartAsset, layoutId) : undefined,
            storagePrefix: STOCK_CHART_STORAGE_PREFIX,
            load: shouldLoadStoredLayout,
            autosave: options.autosave !== false && preferenceAutosave,
            document: options.document || undefined,
            theme: currentThemeName(),
            recentStocks: recentStocks(),
            onComparisonSymbolLoad: function (benchmark) {
                return requestBars(benchmark).then(function (payload) {
                    return payload.bars;
                });
            },
            onRecentStockSelect: function (stock) {
                if (stock && stock.code) loadStockChart(stock.code, stock);
            },
            onPineAccountSave: window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn ? saveLinkedPineScript : null,
            onPineAccountLoad: window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn ? loadLinkedPineScript : null
        });
        applyStockMetadata(stockChart, code, options.symbolInfo, { silent: true });
        if (!layoutExisted) {
            if (chartPreferences.chart_type && stockChart.setChartType) stockChart.setChartType(chartPreferences.chart_type);
            if (chartPreferences.chart_period && stockChart.setPeriod) stockChart.setPeriod(chartPreferences.chart_period);
            if (chartPreferences.preferred_timeframe && stockChart.setDateRangePreset) stockChart.setDateRangePreset(chartPreferences.preferred_timeframe);
        }
        if (!options.skipStarterStudies) ensureStarterStudies(stockChart, layoutExisted);
        restoreRelativeStrengthSnapshots(stockChart, carriedRelativeStrength);
        if (options.visibleRange) applyVisibleDateRange(stockChart, options.visibleRange);
        if (currentChartAsset && stockChart.markSaved) stockChart.markSaved();
        if (stockChart.loadRequiredComparisonSymbols) {
            stockChart.loadRequiredComparisonSymbols().catch(function (error) {
                console.warn('Unable to restore Relative Strength benchmark:', error);
            });
        }
        if (options.resetHistory && stockChart.resetHistory) stockChart.resetHistory();
        updateCurrentChartLabel();
        updateHistory(code, currentChartAsset);
        setTimeout(function () {
            if (stockChart && stockChart.resize) stockChart.resize();
        }, 0);
    }

    function applySharedLayout(code, payload, bars, metadata, accountChart, includePine) {
        var savedLayoutExists = hasStoredLayout(chartLayoutId(code, accountChart)) || !!accountChart;
        renderChart(code, bars, {
            load: false,
            autosave: false,
            skipStarterStudies: true,
            preserveRelativeStrength: false,
            symbolInfo: metadata,
            accountChart: accountChart || null
        });
        stockChart.importLayout(layoutWithoutEmbeddedData(payload, includePine));
        applyStockMetadata(stockChart, code, metadata || payload && payload.document && payload.document.symbolInfo, { silent: true });
        stockChart.setTheme(currentThemeName());
        stockChart.updateToolbar();
        if (stockChart.loadRequiredComparisonSymbols) {
            stockChart.loadRequiredComparisonSymbols().catch(function (error) {
                console.warn('Unable to load shared Relative Strength benchmark:', error);
            });
        }
        recordRecentStock(code, metadata || payload && payload.document && payload.document.symbolInfo);
        sharedPreviewActive = true;
        setSharedSaveVisible(true);
        setStatus(
            'Shared chart preview' +
            (sharedLayoutHasPine(payload) && !includePine ? ' (Pine scripts omitted).' : '.') +
            ' Click Save Layout to ' + (savedLayoutExists ? 'replace your saved ' : 'save this ') + code + ' layout.',
            false
        );
    }

    function loadStockChart(rawCode, metadata, options) {
        options = options || {};
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
        updateHistory(code, options.accountChart || null);
        setStatus('Loading ' + code + '...', false);

        var requestId = ++dataSerial;
        if (activeDataRequest && activeDataRequest.readyState !== 4) activeDataRequest.abort();
        ensureEngineReady().then(function () {
            if (requestId !== dataSerial) return;
            return Promise.all([
                requestBars(code),
                requestStockMetadata(code),
                options.accountChart
                    ? Promise.resolve(options.accountChart)
                    : (window.MIQAccount && window.MIQAccount.getChart ? window.MIQAccount.getChart({ code: code }).catch(function () { return null; }) : Promise.resolve(null))
            ]).then(function (results) {
                if (requestId !== dataSerial) return;
                var payload = results[0];
                symbolInfo = rememberStockMetadata(results[1] || symbolInfo, code);
                var accountChart = results[2] || null;
                var selectedLayoutId = chartLayoutId(code, accountChart);
                var localMeta = loadChartLocalMeta(selectedLayoutId);
                var localLayout = null;
                try {
                    localLayout = JSON.parse(window.localStorage.getItem(STOCK_CHART_STORAGE_PREFIX + ':' + selectedLayoutId) || 'null');
                } catch (error) { localLayout = null; }
                var localDirty = !!(localLayout && localMeta && localMeta.dirty);
                var initialConflict = !!(localDirty && accountChart && localMeta.asset && Number(localMeta.asset.revision || 0) !== Number(accountChart.revision || 0));
                var accountLayout = localDirty ? localLayout : (accountChart && accountChart.layout || null);
                renderChart(code, payload.bars, {
                    symbolInfo: symbolInfo,
                    resetHistory: isNewSymbol,
                    visibleRange: previousVisibleDateRange,
                    document: accountLayout || undefined,
                    accountChart: accountChart
                });
                if (initialConflict) showChartConflict(accountChart);
                else if (localDirty) {
                    queueChartCloudSave(localLayout);
                    setAccountSyncStatus('Restored local changes; syncing to your workspace…', false);
                }
                recordRecentStock(code, symbolInfo);
                setStatus(code + ' loaded: ' + payload.bars.length + ' OHLCV bars.', false);
                if (pendingAccountScript) openPendingAccountScript();
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
        var includePine = false;
        if (sharedLayoutHasPine(payload)) {
            includePine = typeof window.confirm === 'function' && window.confirm(
                'This shared chart contains Pine Script source code. Run the shared scripts in the protected Pine runtime?\n\nChoose Cancel to open the chart without Pine scripts.'
            );
        }

        ensureEngineReady().then(function () {
            return Promise.all([
                requestBars(code),
                requestStockMetadata(code),
                window.MIQAccount && window.MIQAccount.state && window.MIQAccount.state.loggedIn && window.MIQAccount.getChart
                    ? window.MIQAccount.getChart({ code: code }).catch(function () { return null; })
                    : Promise.resolve(null)
            ]).then(function (results) {
                var response = results[0];
                var metadata = results[1];
                var accountChart = results[2] || null;
                applySharedLayout(code, payload, response.bars, metadata, accountChart, includePine);
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
        sharedPreviewActive = false;
        setSharedSaveVisible(false);
        setStatus('Shared layout saved for ' + currentCode + '.', false);
    }

    function setSaveAsFormVisible(visible) {
        var form = document.getElementById('toolStockChartSaveAsForm');
        if (!form) return;
        form.hidden = !visible;
        if (visible) {
            var input = document.getElementById('toolStockChartSaveAsName');
            if (input) {
                input.value = currentCode ? currentCode + ' chart' : '';
                input.focus();
                input.select();
            }
        }
    }

    function saveCurrentAsNamed(name) {
        if (!stockChart || !window.MIQAccount || !window.MIQAccount.state.loggedIn) {
            return Promise.reject(new Error('Sign in to save named charts.'));
        }
        name = String(name || '').trim();
        if (!name) return Promise.reject(new Error('Enter a chart name.'));
        var documentState = stockChart.serialize();
        setAccountSyncStatus('Saving named chart…', false);
        return window.MIQAccount.saveChartLayout(currentCode, documentState, {
            asset_key: window.MIQAccount.makeAssetKey(),
            name: name,
            kind: 'named',
            autosave: false,
            create_version: true,
            clientUpdatedAt: documentState.updatedAt || new Date().toISOString()
        }).then(function (response) {
            var asset = response.chart || null;
            if (asset) {
                asset.layout = documentState;
                setSaveAsFormVisible(false);
                return loadStockChart(currentCode, stockMetadataForCode(currentCode), { accountChart: asset });
            }
            return null;
        });
    }

    function createCurrentChartVersion() {
        if (!stockChart || !window.MIQAccount || !window.MIQAccount.state.loggedIn) return;
        var documentState = stockChart.serialize();
        var asset = currentChartAsset || {};
        setAccountSyncStatus('Creating chart version…', false);
        window.MIQAccount.saveChartLayout(currentCode, documentState, {
            id: asset.id || undefined,
            asset_key: asset.asset_key || (chartSyncState && chartSyncState.assetKey),
            name: asset.name || ('Auto: ' + currentCode),
            kind: asset.kind || 'workspace',
            expected_revision: asset.revision || undefined,
            autosave: false,
            create_version: true,
            clientUpdatedAt: documentState.updatedAt || new Date().toISOString()
        }).then(function (response) {
            currentChartAsset = response.chart || currentChartAsset;
            if (chartSyncState) {
                chartSyncState.asset = currentChartAsset;
                chartSyncState.assetKey = currentChartAsset && currentChartAsset.asset_key || chartSyncState.assetKey;
                chartSyncState.pendingDocument = null;
            }
            persistChartSyncMeta(false);
            updateCurrentChartLabel();
            setAccountSyncStatus('Chart version created', false);
        }).catch(function (error) {
            if (error.conflict) showChartConflict(error.body && error.body.chart);
            else setAccountSyncStatus(error.message, true);
        });
    }

    function useServerConflictChart() {
        if (!chartSyncState || !chartSyncState.conflict || !window.MIQAccount) return Promise.resolve();
        var serverSummary = chartSyncState.conflict;
        return window.MIQAccount.getChart({ id: serverSummary.id }).then(function (serverChart) {
            if (!serverChart || !serverChart.layout || !stockChart) throw new Error('The server chart is no longer available.');
            currentChartAsset = serverChart;
            chartSyncState.asset = serverChart;
            chartSyncState.assetKey = serverChart.asset_key || chartSyncState.assetKey;
            chartSyncState.conflict = null;
            chartSyncState.pendingDocument = null;
            chartSyncState.suppressRemote = true;
            try {
                stockChart.importLayout(serverChart.layout);
                stockChart.save();
                clearTimeout(stockChart.autosaveTimer);
                stockChart.autosaveTimer = null;
            } finally {
                chartSyncState.suppressRemote = false;
            }
            persistChartSyncMeta(false);
            hideChartConflict();
            updateCurrentChartLabel();
            setAccountSyncStatus('Using the server version', false);
        }).catch(function (error) {
            setAccountSyncStatus(error.message, true);
        });
    }

    function keepLocalConflictChart() {
        if (!chartSyncState || !chartSyncState.conflict) return;
        var serverChart = chartSyncState.conflict;
        currentChartAsset = Object.assign({}, currentChartAsset || {}, serverChart);
        chartSyncState.asset = currentChartAsset;
        chartSyncState.assetKey = currentChartAsset.asset_key || chartSyncState.assetKey;
        chartSyncState.conflict = null;
        hideChartConflict();
        if (!chartSyncState.pendingDocument && stockChart) chartSyncState.pendingDocument = stockChart.serialize();
        flushChartCloudSave();
    }

    function saveBothConflictCharts() {
        if (!chartSyncState || !chartSyncState.conflict || !stockChart) return;
        var localDocument = stockChart.serialize();
        var name = currentCode + ' conflict copy ' + new Date().toISOString().slice(0, 16).replace('T', ' ');
        window.MIQAccount.saveChartLayout(currentCode, localDocument, {
            asset_key: window.MIQAccount.makeAssetKey(),
            name: name,
            kind: 'named',
            autosave: false,
            create_version: true,
            clientUpdatedAt: localDocument.updatedAt || new Date().toISOString()
        }).then(function () {
            return useServerConflictChart();
        }).catch(function (error) {
            setAccountSyncStatus(error.message, true);
        });
    }

    function saveLinkedPineScript(detail) {
        if (!detail || !window.MIQAccount || !window.MIQAccount.state.loggedIn) return Promise.resolve(null);
        var accountScript = detail.accountScript || {};
        return window.MIQAccount.saveScript({
            id: accountScript.id || undefined,
            asset_key: accountScript.asset_key || undefined,
            expected_revision: accountScript.revision || undefined,
            name: detail.title || 'Untitled script',
            code: currentCode,
            source_code: detail.code || '',
            status: 'draft',
            create_version: !!detail.createVersion
        }).then(function (response) {
            var saved = response.script || null;
            if (saved && stockChart && stockChart.setPineEditorAccountScript) {
                stockChart.setPineEditorAccountScript(saved);
            }
            setAccountSyncStatus('Pine script saved to My Scripts', false);
            return saved;
        }).catch(function (error) {
            setAccountSyncStatus(error.message, true);
            throw error;
        });
    }

    function loadLinkedPineScript(reference) {
        if (!reference || !reference.id || !window.MIQAccount) return Promise.resolve(null);
        return window.MIQAccount.getScript({ id: reference.id });
    }

    function openPendingAccountScript() {
        if (!pendingAccountScript || !stockChart || !stockChart.openPineScriptEditor) return false;
        var script = pendingAccountScript;
        pendingAccountScript = null;
        stockChart.openPineScriptEditor({
            title: script.name,
            code: script.source_code,
            accountScript: {
                id: script.id,
                asset_key: script.asset_key,
                revision: script.revision,
                name: script.name
            }
        });
        return true;
    }

    function loadRequestedAccountAsset() {
        var params = new URLSearchParams(window.location.search);
        var chartId = Number(params.get('chart_id') || 0);
        var scriptId = Number(params.get('script_id') || 0);
        if (!window.MIQAccount || !window.MIQAccount.state.loggedIn || (!chartId && !scriptId)) return false;
        if (chartId) {
            window.MIQAccount.getChart({ id: chartId }).then(function (chart) {
                if (!chart) throw new Error('Saved chart not found.');
                loadStockChart(chart.code, null, { accountChart: chart });
            }).catch(function (error) { setStatus(error.message, true); });
            return true;
        }
        window.MIQAccount.getScript({ id: scriptId }).then(function (script) {
            if (!script) throw new Error('Pine script not found.');
            pendingAccountScript = script;
            loadStockChart(script.code || initialCode());
        }).catch(function (error) { setStatus(error.message, true); });
        return true;
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
        if (!requestedAccountAssetHandled) {
            requestedAccountAssetHandled = true;
            if (loadRequestedAccountAsset()) return;
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
        var saveAsButton = document.getElementById('toolStockChartSaveAs');
        if (saveAsButton) saveAsButton.addEventListener('click', function () { setSaveAsFormVisible(true); });
        var saveAsCancel = document.getElementById('toolStockChartSaveAsCancel');
        if (saveAsCancel) saveAsCancel.addEventListener('click', function () { setSaveAsFormVisible(false); });
        var saveAsForm = document.getElementById('toolStockChartSaveAsForm');
        if (saveAsForm) {
            saveAsForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var nameField = document.getElementById('toolStockChartSaveAsName');
                saveCurrentAsNamed(nameField && nameField.value).catch(function (error) {
                    setAccountSyncStatus(error.message, true);
                });
            });
        }
        var versionButton = document.getElementById('toolStockChartCreateVersion');
        if (versionButton) versionButton.addEventListener('click', createCurrentChartVersion);
        var keepLocalButton = document.getElementById('toolStockChartConflictKeepLocal');
        if (keepLocalButton) keepLocalButton.addEventListener('click', keepLocalConflictChart);
        var useServerButton = document.getElementById('toolStockChartConflictUseServer');
        if (useServerButton) useServerButton.addEventListener('click', useServerConflictChart);
        var saveBothButton = document.getElementById('toolStockChartConflictSaveBoth');
        if (saveBothButton) saveBothButton.addEventListener('click', saveBothConflictCharts);

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

    window.addEventListener('pagehide', function () {
        if (stockChart && stockChart.flushAutosave) stockChart.flushAutosave();
    });
}());
