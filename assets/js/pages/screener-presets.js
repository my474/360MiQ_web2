(function () {
    'use strict';

    var FILTER_KEYS = [
        'market', 'sector', 'industry', 'marketcap', 'polar_ta', 'polar_va',
        'polar_fa', 'polar_trendgauge', 'channel_pos', 'channel_trend',
        'pe_stdev', 'pe_trend', 'pb_stdev', 'pb_trend', 'fscore', 'zscore',
        'mscore', 'ma10', 'ma20', 'ma50', 'ma100', 'ma200', 'ma250',
        'rsi14d', 'rsi14w', 'macdd', 'macdw', 'highlow', 'volume'
    ];
    var FILTER_CONTROLS = [
        ['sector', 'Sector', 'All'],
        ['industry', 'Industry', 'All'],
        ['marketcap', 'Market_Cap', 'All'],
        ['polar_ta', 'Technical_Polar', 'All'],
        ['polar_va', 'Valuation_Polar', 'All'],
        ['polar_fa', 'Fundamental_Polar', 'All'],
        ['polar_trendgauge', 'Trend_Gauge', 'All'],
        ['channel_pos', 'Price_Channel_Pos', 'All'],
        ['channel_trend', 'Price_Channel_Trend', 'All'],
        ['pe_stdev', 'PE_Band_STDEV_σ', 'All'],
        ['pe_trend', 'PE_Band_Trend', 'All'],
        ['pb_stdev', 'PB_Band_STDEV_σ', 'All'],
        ['pb_trend', 'PB_Band_Trend', 'All'],
        ['fscore', 'F-Score', 'All'],
        ['zscore', 'Z-Score', 'All'],
        ['mscore', 'M-Score', 'All'],
        ['ma10', 'MA10', 'None'],
        ['ma20', 'MA20', 'None'],
        ['ma50', 'MA50', 'None'],
        ['ma100', 'MA100', 'None'],
        ['ma200', 'MA200', 'None'],
        ['ma250', 'MA250', 'None'],
        ['rsi14d', 'RSI14_Daily', 'None'],
        ['rsi14w', 'RSI14_Weekly', 'None'],
        ['macdd', 'MACD_Daily', 'None'],
        ['macdw', 'MACD_Weekly', 'None'],
        ['highlow', 'High_Low', 'None'],
        ['volume', 'Volume', 'None']
    ];
    var GUEST_KEY = '360miq-screener-presets:guest:v1';
    var USER_KEY_PREFIX = '360miq-screener-presets:user:';
    var PENDING_TABLE_KEY = '360miq-screener-preset-pending-table:v1';
    var GUEST_LIMIT = 10;
    var DEFAULT_ACCOUNT_LIMIT = 50;
    var accountState = window.__MIQ_ACCOUNT__ || {};
    var loggedIn = accountState.loggedIn === true;
    var accountLimit = DEFAULT_ACCOUNT_LIMIT;
    var presets = [];
    var selectedKey = '';
    var ready = false;
    var busy = false;
    var elements = {};

    function storageGet(key) {
        try {
            var value = JSON.parse(window.localStorage.getItem(key) || '[]');
            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    }

    function storageSet(key, value) {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            return false;
        }
    }

    function storageRemove(key) {
        try { window.localStorage.removeItem(key); } catch (error) { /* storage is optional */ }
    }

    function userStorageKey() {
        return USER_KEY_PREFIX + String(accountState.userId || 'unknown') + ':v1';
    }

    function activeStorageKey() {
        return loggedIn ? userStorageKey() : GUEST_KEY;
    }

    function makeKey() {
        if (window.MIQAccount && typeof window.MIQAccount.makeAssetKey === 'function') {
            return window.MIQAccount.makeAssetKey();
        }
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (character) {
            var random = Math.random() * 16 | 0;
            return (character === 'x' ? random : (random & 3 | 8)).toString(16);
        });
    }

    function normalizeConfig(config) {
        config = config && typeof config === 'object' ? config : {};
        var sourceFilters = config.filters && typeof config.filters === 'object' ? config.filters : {};
        var filters = {};
        FILTER_KEYS.forEach(function (key) {
            if (typeof sourceFilters[key] === 'string' && sourceFilters[key].trim() !== '') {
                filters[key] = sourceFilters[key].trim().slice(0, 160);
            }
        });
        var sourceTable = config.table && typeof config.table === 'object' ? config.table : {};
        var order = Array.isArray(sourceTable.order) ? sourceTable.order.slice(0, 3).map(function (item) {
            return [Number(item[0]), String(item[1]).toLowerCase() === 'asc' ? 'asc' : 'desc'];
        }).filter(function (item) {
            return Number.isInteger(item[0]) && item[0] >= 0 && item[0] <= 39;
        }) : [];
        var pageLength = [30, 60, 100, 200].indexOf(Number(sourceTable.pageLength)) >= 0 ? Number(sourceTable.pageLength) : 30;
        var columns = Array.isArray(sourceTable.columns) ? sourceTable.columns.slice(0, 40).map(Boolean) : [];
        return {
            version: 1,
            filters: filters,
            table: {
                order: order.length ? order : [[3, 'desc']],
                pageLength: pageLength,
                columns: columns
            }
        };
    }

    function normalizePreset(preset) {
        if (!preset || typeof preset !== 'object') return null;
        var name = String(preset.name || '').trim().slice(0, 120);
        var clientKey = String(preset.client_key || '');
        if (!name || !clientKey) return null;
        return {
            id: Number(preset.id || 0),
            client_key: clientKey,
            name: name,
            config: normalizeConfig(preset.config),
            is_default: preset.is_default === true || preset.is_default === 1 || preset.is_default === '1',
            revision: Number(preset.revision || 0),
            client_updated_at: preset.client_updated_at || new Date().toISOString(),
            created_at: preset.created_at || null,
            updated_at: preset.updated_at || new Date().toISOString()
        };
    }

    function readLocal(key) {
        return storageGet(key).map(normalizePreset).filter(Boolean);
    }

    function saveLocal() {
        storageSet(activeStorageKey(), presets);
    }

    function setStatus(message, tone) {
        elements.status.textContent = message;
        if (tone) elements.status.dataset.tone = tone;
        else elements.status.removeAttribute('data-tone');
    }

    function selectedPreset() {
        return presets.find(function (preset) { return preset.client_key === selectedKey; }) || null;
    }

    function sortedPresets() {
        return presets.slice().sort(function (left, right) {
            if (left.is_default !== right.is_default) return left.is_default ? -1 : 1;
            return left.name.localeCompare(right.name, undefined, { sensitivity: 'base' });
        });
    }

    function summaryStatus(message, tone) {
        if (message) {
            setStatus(message, tone);
            return;
        }
        if (loggedIn) {
            setStatus('Synced to your account · ' + presets.length + ' of ' + accountLimit, 'success');
        } else {
            setStatus('Saved in this browser · ' + presets.length + ' of ' + GUEST_LIMIT + '. Sign in to sync.');
        }
    }

    function render() {
        var selected = selectedPreset();
        var ordered = sortedPresets();
        elements.select.innerHTML = '';
        if (!ordered.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = 'No saved presets';
            elements.select.appendChild(empty);
            selectedKey = '';
        } else {
            ordered.forEach(function (preset) {
                var option = document.createElement('option');
                option.value = preset.client_key;
                option.textContent = (preset.is_default ? '★ ' : '') + preset.name;
                elements.select.appendChild(option);
            });
            if (!selected) {
                selectedKey = ordered[0].client_key;
                selected = ordered[0];
            }
            elements.select.value = selectedKey;
        }
        if (selected && document.activeElement !== elements.name) {
            elements.name.value = selected.name;
        }
        var hasSelected = !!selected;
        elements.select.disabled = busy || !ready || !ordered.length;
        elements.name.disabled = busy || !ready;
        elements.load.disabled = busy || !ready || !hasSelected;
        elements.save.disabled = busy || !ready;
        elements.update.disabled = busy || !ready || !hasSelected;
        elements.duplicate.disabled = busy || !ready || !hasSelected;
        elements.makeDefault.disabled = busy || !ready || !hasSelected || selected.is_default;
        elements.remove.disabled = busy || !ready || !hasSelected;
        elements.makeDefault.textContent = selected && selected.is_default ? 'Default' : 'Make default';
    }

    function setBusy(value) {
        busy = value;
        render();
    }

    function request(action, payload, method) {
        if (!window.MIQAccount || typeof window.MIQAccount.request !== 'function') {
            return Promise.reject(new Error('The account service is not ready. Refresh and try again.'));
        }
        return window.MIQAccount.request(action, payload || {}, method || 'POST');
    }

    function filterControlValue(groupId) {
        var group = document.getElementById(groupId);
        var button = group && group.querySelector ? group.querySelector('button.btn') : null;
        if (!button) return '';
        var value = String(button.textContent || '').replace(/\s+/g, ' ').trim();
        var separator = value.indexOf(':');
        return (separator >= 0 ? value.slice(separator + 1) : value).trim();
    }

    function filtersFromControls() {
        var marketLabels = {
            'nyse + nasdaq': 'NYSE + NASDAQ',
            'nyse': 'NYSE',
            'nasdaq': 'NASDAQ',
            'nyse arca': 'NYSEARCA',
            'london': 'LSE',
            'australia': 'ASX',
            'toronto tsx': 'TSX',
            'india nse': 'NSE',
            'tokyo': 'TYO',
            'hong kong': 'HKEX',
            'shanghai': 'SHSE',
            'shenzhen': 'SZSE'
        };
        var marketLabel = filterControlValue('Market').toLocaleLowerCase();
        var filters = {};
        if (marketLabels[marketLabel]) filters.market = marketLabels[marketLabel];
        FILTER_CONTROLS.forEach(function (definition) {
            var value = filterControlValue(definition[1]);
            if (value && value.toLocaleLowerCase() !== definition[2].toLocaleLowerCase()) {
                filters[definition[0]] = value;
            }
        });
        return filters.market ? filters : null;
    }

    function currentConfig() {
        var filters = filtersFromControls();
        if (!filters) {
            var query = new URLSearchParams(window.location.search);
            filters = {};
            FILTER_KEYS.forEach(function (key) {
                var value = query.get(key);
                if (value !== null && value.trim() !== '') filters[key] = value.trim();
            });
        }
        if (!filters.market) return null;
        var tableConfig = { order: [[3, 'desc']], pageLength: 30, columns: [] };
        if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#screener_grid')) {
            var table = jQuery('#screener_grid').DataTable();
            tableConfig.order = table.order().map(function (sort) { return [Number(sort[0]), String(sort[1])]; });
            tableConfig.pageLength = Number(table.page.len());
            tableConfig.columns = table.columns().visible().toArray().map(Boolean);
        }
        return normalizeConfig({ filters: filters, table: tableConfig });
    }

    function requireCurrentConfig() {
        var config = currentConfig();
        if (!config) {
            setStatus('The current screener filters could not be read.', 'error');
            return null;
        }
        return config;
    }

    function cleanName() {
        return elements.name.value.trim().slice(0, 120);
    }

    function nameExists(name, exceptKey) {
        var comparable = name.toLocaleLowerCase();
        return presets.some(function (preset) {
            return preset.client_key !== exceptKey && preset.name.toLocaleLowerCase() === comparable;
        });
    }

    function uniqueName(base) {
        var candidate = String(base || 'Screener preset').trim().slice(0, 120);
        var number = 2;
        while (nameExists(candidate, '')) {
            var suffix = ' (' + number + ')';
            candidate = String(base).trim().slice(0, 120 - suffix.length) + suffix;
            number += 1;
        }
        return candidate;
    }

    function replacePreset(nextPreset) {
        var normalized = normalizePreset(nextPreset);
        if (!normalized) return;
        var index = presets.findIndex(function (preset) { return preset.client_key === normalized.client_key; });
        if (normalized.is_default) {
            presets.forEach(function (preset) { preset.is_default = false; });
        }
        if (index >= 0) presets[index] = normalized;
        else presets.push(normalized);
        selectedKey = normalized.client_key;
        saveLocal();
        render();
    }

    function savePreset(record, makeDefault) {
        if (!loggedIn) {
            replacePreset(record);
            summaryStatus('Preset saved in this browser.', 'success');
            return Promise.resolve(record);
        }
        return request('save_screener_preset', {
            client_key: record.client_key,
            name: record.name,
            config: record.config,
            client_updated_at: record.client_updated_at,
            make_default: makeDefault === true
        }).then(function (response) {
            replacePreset(response.preset);
            summaryStatus('Preset synced to your account.', 'success');
            return response.preset;
        });
    }

    function createPreset(config, requestedName, makeDefault) {
        var limit = loggedIn ? accountLimit : GUEST_LIMIT;
        if (presets.length >= limit) {
            setStatus('You have reached the ' + limit + '-preset limit.', 'error');
            return;
        }
        var name = requestedName || cleanName();
        if (!name) {
            setStatus('Enter a name for this preset.', 'error');
            elements.name.focus();
            return;
        }
        if (nameExists(name, '')) {
            setStatus('A preset with that name already exists. Select it and use Save.', 'error');
            return;
        }
        var now = new Date().toISOString();
        var record = {
            client_key: makeKey(),
            name: name,
            config: config,
            is_default: presets.length === 0 || makeDefault === true,
            revision: 0,
            client_updated_at: now,
            updated_at: now
        };
        setBusy(true);
        setStatus(loggedIn ? 'Syncing preset…' : 'Saving preset…');
        savePreset(record, record.is_default).catch(function (error) {
            setStatus(error.message || 'The preset could not be saved.', 'error');
        }).then(function () {
            setBusy(false);
        });
    }

    function handleSaveNew() {
        var config = requireCurrentConfig();
        if (config) createPreset(config);
    }

    function handleUpdate() {
        var selected = selectedPreset();
        var config = requireCurrentConfig();
        if (!selected || !config) return;
        var name = cleanName();
        if (!name) {
            setStatus('Enter a name for this preset.', 'error');
            return;
        }
        if (nameExists(name, selected.client_key)) {
            setStatus('A preset with that name already exists.', 'error');
            return;
        }
        var updated = Object.assign({}, selected, {
            name: name,
            config: config,
            client_updated_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        });
        setBusy(true);
        setStatus(loggedIn ? 'Syncing changes…' : 'Updating preset…');
        savePreset(updated, updated.is_default).catch(function (error) {
            setStatus(error.message || 'The preset could not be updated.', 'error');
        }).then(function () {
            setBusy(false);
        });
    }

    function handleDuplicate() {
        var selected = selectedPreset();
        if (!selected) return;
        var duplicateName = uniqueName(selected.name + ' copy');
        elements.name.value = duplicateName;
        createPreset(normalizeConfig(selected.config), duplicateName, false);
    }

    function handleDefault() {
        var selected = selectedPreset();
        if (!selected || selected.is_default) return;
        setBusy(true);
        setStatus(loggedIn ? 'Syncing default preset…' : 'Setting default preset…');
        var operation = loggedIn
            ? request('set_default_screener_preset', { client_key: selected.client_key })
            : Promise.resolve({ saved: true });
        operation.then(function () {
            presets.forEach(function (preset) { preset.is_default = preset.client_key === selected.client_key; });
            saveLocal();
            render();
            summaryStatus('Default preset updated.', 'success');
        }).catch(function (error) {
            setStatus(error.message || 'The default preset could not be changed.', 'error');
        }).then(function () {
            setBusy(false);
        });
    }

    function handleDelete() {
        var selected = selectedPreset();
        if (!selected) return;
        if (!window.confirm('Delete the screener preset “' + selected.name + '”?')) return;
        setBusy(true);
        setStatus(loggedIn ? 'Deleting synced preset…' : 'Deleting preset…');
        var operation = loggedIn
            ? request('delete_screener_preset', { client_key: selected.client_key })
            : Promise.resolve({ deleted: true });
        operation.then(function () {
            var wasDefault = selected.is_default;
            presets = presets.filter(function (preset) { return preset.client_key !== selected.client_key; });
            if (wasDefault && presets.length && !presets.some(function (preset) { return preset.is_default; })) {
                presets[0].is_default = true;
            }
            selectedKey = '';
            saveLocal();
            render();
            summaryStatus('Preset deleted.', 'success');
        }).catch(function (error) {
            setStatus(error.message || 'The preset could not be deleted.', 'error');
        }).then(function () {
            setBusy(false);
        });
    }

    function loadPreset(preset) {
        if (!preset || !preset.config || !preset.config.filters.market) {
            setStatus('This preset does not contain a valid screen.', 'error');
            return;
        }
        try {
            window.sessionStorage.setItem(PENDING_TABLE_KEY, JSON.stringify({
                client_key: preset.client_key,
                table: preset.config.table,
                created_at: Date.now()
            }));
        } catch (error) { /* table preferences are optional */ }
        var query = new URLSearchParams();
        FILTER_KEYS.forEach(function (key) {
            if (preset.config.filters[key]) query.set(key, preset.config.filters[key]);
        });
        window.location.assign(window.location.pathname + '?' + query.toString().replace(/\+/g, '%20'));
    }

    function restorePendingTable() {
        var pending;
        try {
            pending = JSON.parse(window.sessionStorage.getItem(PENDING_TABLE_KEY) || 'null');
        } catch (error) {
            pending = null;
        }
        if (!pending || !pending.table || Date.now() - Number(pending.created_at || 0) > 300000) {
            try { window.sessionStorage.removeItem(PENDING_TABLE_KEY); } catch (error) { /* optional */ }
            return;
        }
        var attempts = 0;
        var timer = window.setInterval(function () {
            attempts += 1;
            if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#screener_grid')) {
                window.clearInterval(timer);
                var table = jQuery('#screener_grid').DataTable();
                var tableState = normalizeConfig({ filters: { market: new URLSearchParams(window.location.search).get('market') || '' }, table: pending.table }).table;
                if (tableState.columns.length) {
                    tableState.columns.forEach(function (visible, index) {
                        if (index < table.columns().count()) table.column(index).visible(visible, false);
                    });
                }
                table.order(tableState.order);
                table.page.len(tableState.pageLength);
                table.draw(false);
                try { window.sessionStorage.removeItem(PENDING_TABLE_KEY); } catch (error) { /* optional */ }
            } else if (attempts >= 120) {
                window.clearInterval(timer);
            }
        }, 100);
    }

    function mergeGuestPresets() {
        var guestPresets = readLocal(GUEST_KEY);
        if (!loggedIn || !guestPresets.length) return Promise.resolve(false);
        var remaining = guestPresets.slice();
        var imported = false;
        var sequence = Promise.resolve();
        guestPresets.forEach(function (guestPreset) {
            sequence = sequence.then(function () {
                if (presets.some(function (preset) { return preset.client_key === guestPreset.client_key; })) {
                    remaining = remaining.filter(function (preset) { return preset.client_key !== guestPreset.client_key; });
                    storageSet(GUEST_KEY, remaining);
                    return null;
                }
                if (presets.length >= accountLimit) return null;
                var copy = Object.assign({}, guestPreset, {
                    name: uniqueName(guestPreset.name),
                    is_default: !presets.some(function (preset) { return preset.is_default; }) && guestPreset.is_default,
                    client_updated_at: guestPreset.client_updated_at || new Date().toISOString()
                });
                return request('save_screener_preset', {
                    client_key: copy.client_key,
                    name: copy.name,
                    config: copy.config,
                    client_updated_at: copy.client_updated_at,
                    make_default: copy.is_default
                }).then(function (response) {
                    replacePreset(response.preset);
                    remaining = remaining.filter(function (preset) { return preset.client_key !== guestPreset.client_key; });
                    storageSet(GUEST_KEY, remaining);
                    imported = true;
                });
            });
        });
        return sequence.then(function () {
            if (!remaining.length) storageRemove(GUEST_KEY);
            return imported;
        });
    }

    function maybeLoadDefault() {
        var query = new URLSearchParams(window.location.search);
        var requestedKey = query.get('preset');
        if (requestedKey) {
            var requestedPreset = presets.find(function (preset) { return preset.client_key === requestedKey; });
            if (requestedPreset) loadPreset(requestedPreset);
            else setStatus('That screener preset is no longer available.', 'error');
            return;
        }
        if (query.get('market')) return;
        var defaultPreset = presets.find(function (preset) { return preset.is_default; });
        if (defaultPreset) loadPreset(defaultPreset);
    }

    function loadAccountPresets() {
        return request('list_screener_presets', {}, 'GET').then(function (response) {
            accountLimit = Number(response.limit || DEFAULT_ACCOUNT_LIMIT);
            presets = (response.presets || []).map(normalizePreset).filter(Boolean);
            saveLocal();
            return mergeGuestPresets();
        }).then(function (imported) {
            ready = true;
            render();
            summaryStatus(imported ? 'Local presets were synced to your account.' : '', imported ? 'success' : '');
            maybeLoadDefault();
        }).catch(function (error) {
            presets = readLocal(userStorageKey());
            ready = true;
            render();
            setStatus((error.message || 'Could not sync presets.') + ' Showing the last local copy.', 'error');
            maybeLoadDefault();
        });
    }

    function bindEvents() {
        elements.select.addEventListener('change', function () {
            selectedKey = elements.select.value;
            var selected = selectedPreset();
            elements.name.value = selected ? selected.name : '';
            render();
        });
        elements.name.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (selectedPreset()) handleUpdate();
                else handleSaveNew();
            }
        });
        elements.load.addEventListener('click', function () { loadPreset(selectedPreset()); });
        elements.save.addEventListener('click', handleSaveNew);
        elements.update.addEventListener('click', handleUpdate);
        elements.duplicate.addEventListener('click', handleDuplicate);
        elements.makeDefault.addEventListener('click', handleDefault);
        elements.remove.addEventListener('click', handleDelete);
    }

    function init() {
        var root = document.getElementById('miq-screener-presets');
        if (!root) return;
        elements = {
            root: root,
            status: document.getElementById('miq-screener-preset-status'),
            select: document.getElementById('miq-screener-preset-select'),
            name: document.getElementById('miq-screener-preset-name'),
            load: document.getElementById('miq-screener-preset-load'),
            save: document.getElementById('miq-screener-preset-save'),
            update: document.getElementById('miq-screener-preset-update'),
            duplicate: document.getElementById('miq-screener-preset-duplicate'),
            makeDefault: document.getElementById('miq-screener-preset-default'),
            remove: document.getElementById('miq-screener-preset-delete')
        };
        bindEvents();
        restorePendingTable();
        presets = readLocal(activeStorageKey());
        render();
        if (loggedIn) {
            setStatus('Syncing presets…');
            loadAccountPresets();
        } else {
            ready = true;
            render();
            summaryStatus();
            maybeLoadDefault();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
