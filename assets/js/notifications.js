(function () {
    'use strict';

    var state = window.__MIQ_ACCOUNT__ || {};
    var settingsPanel = null;
    var settings = null;
    var firebaseLoadPromise = null;
    var messaging = null;
    var foregroundBound = false;
    var startupPromise = null;
    var androidPermissionRequest = null;
    var notificationSettingsLoadPromise = null;
    var activeContextInvite = null;
    var contextOffersBound = false;
    var tokenStorageKey = 'miq-notification-web-token';
    var webOptInStorageKey = 'miq-notification-web-enabled';
    var webOptInUserStorageKey = 'miq-notification-web-user-id';
    var webInstallationStorageKey = 'miq-notification-web-installation-v1';
    var webDeviceStorageKey = 'miq-notification-web-device-id';
    var androidOptInStorageKey = 'miq-notification-android-enabled';
    var androidOptInUserStorageKey = 'miq-notification-android-user-id';
    var androidInstallationStorageKey = 'miq-notification-android-installation-v1';
    var androidDeviceStorageKey = 'miq-notification-android-device-id';
    var contextStoragePrefix = 'miq-notification-context-v1';
    var contextSnoozeMilliseconds = 30 * 24 * 60 * 60 * 1000;
    var contextStyleVersion = '20260812.2';

    function storageGet(key) {
        try { return window.localStorage ? window.localStorage.getItem(key) || '' : ''; } catch (error) { return ''; }
    }

    function storageSet(key, value) {
        try { if (window.localStorage) window.localStorage.setItem(key, String(value)); } catch (error) {}
    }

    function storageRemove(key) {
        try { if (window.localStorage) window.localStorage.removeItem(key); } catch (error) {}
    }

    function normalizedContextCategory(category) {
        return category === 'price_alerts' || category === 'community_replies' ? category : '';
    }

    function contextRecord(key) {
        var value = storageGet(key);
        if (!value) return {};
        try {
            var parsed = JSON.parse(value);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) { return {}; }
    }

    function contextCooldownActive(record, now) {
        var currentTime = typeof now === 'undefined' ? Date.now() : Number(now);
        return !!record && Number(record.nextAt || 0) > currentTime;
    }

    function contextStorageKey(scope, category, channel) {
        var userId = currentUserStorageId() || 'guest';
        return [contextStoragePrefix, channel || 'web', userId, scope, category || 'all'].join(':');
    }

    function markContextCooldown(key, denied) {
        storageSet(key, JSON.stringify({
            nextAt: Date.now() + contextSnoozeMilliseconds,
            denied: !!denied
        }));
    }

    function contextOfferPolicy(category, snapshot) {
        if (!normalizedContextCategory(category) || !snapshot || !snapshot.loggedIn || !snapshot.supported || !snapshot.configured) return '';
        if (snapshot.channelEnabled && snapshot.preferenceEnabled) return '';
        if (snapshot.categoryCooldown) return '';
        if (!snapshot.channelEnabled && snapshot.globalCooldown) return '';
        if (!snapshot.channelEnabled && snapshot.blocked) return 'blocked';
        return snapshot.channelEnabled ? 'preference' : 'channel';
    }

    function contextCopy(category, source, plan, channel) {
        if (plan === 'blocked') {
            return {
                title: channel === 'android' ? 'App notifications are blocked' : 'Browser notifications are blocked',
                body: channel === 'android'
                    ? 'Allow notifications for 360MiQ in Android settings, then return to notification settings.'
                    : 'Allow notifications for 360MiQ in your browser site settings, then return to notification settings.'
            };
        }
        if (category === 'price_alerts') {
            return {
                title: 'Get this price alert on this device',
                body: 'Enable push notifications so 360MiQ can tell you when the alert triggers.'
            };
        }
        var body = 'Enable community reply notifications so you can keep up with discussions you follow.';
        if (source === 'idea') body = 'Enable community reply notifications so you know when people respond to your idea.';
        else if (source === 'reply') body = 'Enable community reply notifications so you know when the discussion continues.';
        else if (source === 'bookmark') body = 'Enable community reply notifications for updates on this bookmarked idea.';
        return { title: 'Keep up with community replies', body: body };
    }

    function notificationSettingsUrl() {
        try {
            var workspace = new URL(state.workspaceUrl || '/workspace', window.location.origin);
            return new URL('account_settings#miq-notification-settings', workspace).toString();
        } catch (error) {
            return '/account_settings#miq-notification-settings';
        }
    }

    function ensureContextStyles() {
        if (!document.head || document.querySelector('link[data-miq-notification-context-style]')) return;
        var base = String(state.assetBaseUrl || '/assets').replace(/\/$/, '');
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = base + '/css/notification-invite.css?v=' + contextStyleVersion;
        link.setAttribute('data-miq-notification-context-style', 'true');
        document.head.appendChild(link);
    }

    function contextElement(tag, className, text) {
        var element = document.createElement(tag);
        if (className) element.className = className;
        if (typeof text !== 'undefined') element.textContent = text;
        return element;
    }

    function closeContextInvite() {
        var current = activeContextInvite;
        if (!current) return;
        activeContextInvite = null;
        if (current.keyHandler) document.removeEventListener('keydown', current.keyHandler);
        current.node.classList.remove('is-visible');
        current.node.classList.add('is-leaving');
        window.setTimeout(function () {
            if (current.node.parentNode) current.node.parentNode.removeChild(current.node);
        }, 180);
    }

    function contextPermissionFailure(error, channel) {
        if (channel === 'web' && typeof Notification !== 'undefined' && Notification.permission === 'denied') return true;
        return /blocked|denied|not granted|disabled in android settings/i.test(String(error && error.message || error || ''));
    }

    function showContextInvite(category, source, plan, snapshot) {
        if (activeContextInvite || !document.body) return false;
        ensureContextStyles();
        var copy = contextCopy(category, source, plan, snapshot.channel);
        var invite = contextElement('aside', 'miq-notification-invite');
        var identifier = 'miq-notification-invite-' + Date.now();
        invite.setAttribute('role', 'dialog');
        invite.setAttribute('aria-modal', 'false');
        invite.setAttribute('aria-labelledby', identifier + '-title');
        invite.setAttribute('aria-describedby', identifier + '-body');

        var close = contextElement('button', 'miq-notification-invite-close', '\u00d7');
        close.type = 'button';
        close.setAttribute('aria-label', 'Not now');
        var icon = contextElement('span', 'miq-notification-invite-icon');
        icon.setAttribute('aria-hidden', 'true');
        var iconGlyph = contextElement('i', 'fas fa-bell');
        icon.appendChild(iconGlyph);
        var content = contextElement('div', 'miq-notification-invite-content');
        var title = contextElement('strong', 'miq-notification-invite-title', copy.title);
        title.id = identifier + '-title';
        var body = contextElement('p', 'miq-notification-invite-body', copy.body);
        body.id = identifier + '-body';
        var status = contextElement('p', 'miq-notification-invite-status', '');
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        var actions = contextElement('div', 'miq-notification-invite-actions');
        var primary = null;
        var manage = contextElement('a', 'miq-notification-invite-settings', 'Notification settings');
        manage.href = notificationSettingsUrl();
        var later = contextElement('button', 'miq-notification-invite-later', 'Not now');
        later.type = 'button';

        if (plan === 'blocked') {
            manage.className = 'miq-notification-invite-primary';
            manage.textContent = 'Open notification settings';
            actions.appendChild(manage);
        } else {
            var primaryLabel = plan === 'preference'
                ? (category === 'price_alerts' ? 'Turn on price alerts' : 'Turn on community updates')
                : (snapshot.channel === 'android' ? 'Enable app notifications' : 'Enable browser notifications');
            primary = contextElement('button', 'miq-notification-invite-primary', primaryLabel);
            primary.type = 'button';
            actions.appendChild(primary);
            actions.appendChild(manage);
        }
        actions.appendChild(later);
        content.appendChild(title);
        content.appendChild(body);
        content.appendChild(status);
        content.appendChild(actions);
        invite.appendChild(close);
        invite.appendChild(icon);
        invite.appendChild(content);

        var categoryKey = contextStorageKey('category', category, snapshot.channel);
        markContextCooldown(categoryKey, plan === 'blocked');
        if (plan !== 'preference') markContextCooldown(contextStorageKey('global', '', snapshot.channel), plan === 'blocked');

        close.addEventListener('click', closeContextInvite);
        later.addEventListener('click', closeContextInvite);
        var keyHandler = function (event) {
            if (event.key === 'Escape') closeContextInvite();
        };
        document.addEventListener('keydown', keyHandler);
        activeContextInvite = { node: invite, keyHandler: keyHandler };
        document.body.appendChild(invite);
        var reveal = function () { invite.classList.add('is-visible'); };
        if (typeof window.requestAnimationFrame === 'function') window.requestAnimationFrame(reveal);
        else window.setTimeout(reveal, 0);

        if (primary) primary.addEventListener('click', function () {
            primary.disabled = true;
            status.classList.remove('is-error');
            status.textContent = plan === 'preference' ? 'Saving your preference\u2026' : 'Enabling notifications\u2026';
            // Keep permission requests on this direct click path so browser and
            // Android user-activation requirements remain satisfied.
            enableContextNotifications(category, snapshot).then(function () {
                invite.classList.add('is-success');
                status.textContent = category === 'price_alerts'
                    ? 'Price alert notifications are enabled.'
                    : 'Community reply notifications are enabled.';
                window.setTimeout(closeContextInvite, 1400);
            }).catch(function (error) {
                var denied = contextPermissionFailure(error, snapshot.channel);
                status.classList.add('is-error');
                status.textContent = denied
                    ? 'Notifications are blocked. Open notification settings to allow them.'
                    : String(error && error.message || 'Notifications could not be enabled.');
                if (denied) {
                    markContextCooldown(categoryKey, true);
                    primary.hidden = true;
                    manage.className = 'miq-notification-invite-primary';
                    manage.textContent = 'Open notification settings';
                } else {
                    primary.disabled = false;
                }
            });
        });
        return true;
    }

    function randomInstallationId(prefix) {
        var value = '';
        try {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') value = window.crypto.randomUUID();
        } catch (error) {}
        if (!value) {
            value = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + '-' + Math.random().toString(36).slice(2);
        }
        return String(prefix || 'install') + '-' + value;
    }

    function installationId(channel, supplied) {
        var key = channel === 'android' ? androidInstallationStorageKey : webInstallationStorageKey;
        var prefix = channel === 'android' ? 'android' : 'web';
        var value = String(supplied || storageGet(key) || '').replace(/[^A-Za-z0-9._:-]/g, '').slice(0, 128);
        if (value.length < 16) value = randomInstallationId(prefix);
        storageSet(key, value);
        return value;
    }

    function getConfig() {
        return state.notificationConfig || {};
    }

    function currentUserStorageId() {
        return state.loggedIn && parseInt(state.userId, 10) > 0 ? String(parseInt(state.userId, 10)) : '';
    }

    function optInBelongsToCurrentUser(channel) {
        var enabledKey = channel === 'android' ? androidOptInStorageKey : webOptInStorageKey;
        var userKey = channel === 'android' ? androidOptInUserStorageKey : webOptInUserStorageKey;
        var userId = currentUserStorageId();
        return userId !== '' && storageGet(enabledKey) === '1' && storageGet(userKey) === userId;
    }

    function markOptedIn(channel) {
        var userId = currentUserStorageId();
        if (!userId) return;
        storageSet(channel === 'android' ? androidOptInStorageKey : webOptInStorageKey, '1');
        storageSet(channel === 'android' ? androidOptInUserStorageKey : webOptInUserStorageKey, userId);
    }

    function displayCount(count) {
        count = Math.max(0, parseInt(count, 10) || 0);
        return count > 99 ? '99+' : String(count);
    }

    function updateUnread(count) {
        count = Math.max(0, parseInt(count, 10) || 0);
        if (state.loggedIn === false) count = 0;
        state.unreadNotifications = count;
        Array.prototype.forEach.call(document.querySelectorAll('[data-miq-account-unread-badge]'), function (badge) {
            badge.textContent = displayCount(count);
            badge.hidden = count < 1;
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-miq-account-trigger]'), function (trigger) {
            var base = trigger.getAttribute('data-account-aria-base') || 'Account menu';
            trigger.setAttribute('aria-label', count > 0 ? base + ', ' + count + ' unread notifications' : base);
        });
    }

    function dispatchAccountState() {
        if (typeof window.dispatchEvent !== 'function' || typeof window.CustomEvent !== 'function') return;
        window.dispatchEvent(new window.CustomEvent('miq:account-state', { detail: state }));
    }

    function applyAccountState(payload) {
        if (payload && typeof payload === 'object') Object.assign(state, payload);
        updateUnread(state.loggedIn ? state.unreadNotifications : 0);
        dispatchAccountState();
        return state;
    }

    function bootstrapAccountState() {
        if (window.__MIQ_ACCOUNT_BOOTSTRAP_PROMISE__) {
            return Promise.resolve(window.__MIQ_ACCOUNT_BOOTSTRAP_PROMISE__).then(applyAccountState).catch(function () { return state; });
        }
        if (!state.bootstrapRequired || !state.bootstrapUrl || typeof window.fetch !== 'function') {
            return Promise.resolve(applyAccountState(state));
        }
        window.__MIQ_ACCOUNT_BOOTSTRAP_PROMISE__ = fetch(state.bootstrapUrl, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' }
        }).then(function (response) {
            if (!response.ok) throw new Error('The account session could not be loaded.');
            return response.json();
        }).then(function (payload) {
            state.bootstrapRequired = false;
            return applyAccountState(payload);
        });
        return window.__MIQ_ACCOUNT_BOOTSTRAP_PROMISE__.catch(function () { return state; });
    }

    function applyResponseCounts(payload) {
        if (!payload || typeof payload !== 'object') return;
        if (typeof payload.unread !== 'undefined') updateUnread(payload.unread);
        else if (typeof payload.unread_count !== 'undefined') updateUnread(payload.unread_count);
        else if (typeof payload.notifications_unread !== 'undefined') updateUnread(payload.notifications_unread);
        else if (payload.data && typeof payload.data.unread_count !== 'undefined') updateUnread(payload.data.unread_count);
        else if (payload.workspace && payload.workspace.counts) updateUnread(payload.workspace.counts.notifications_unread);
        if (payload.csrf_token) state.csrfToken = payload.csrf_token;
    }

    function request(action, method, payload) {
        method = method || 'GET';
        var url = state.apiUrl || '/account_api.php';
        var options = {
            method: method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' }
        };
        if (method === 'GET') {
            url += (url.indexOf('?') === -1 ? '?' : '&') + 'action=' + encodeURIComponent(action);
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(Object.assign({}, payload || {}, {
                action: action,
                csrf_token: state.csrfToken || ''
            }));
        }
        return fetch(url, options).then(function (response) {
            return response.text().then(function (text) {
                var data = {};
                try { data = text ? JSON.parse(text) : {}; } catch (error) { data = {}; }
                applyResponseCounts(data);
                if (!response.ok) {
                    if (response.status === 401) applyAccountState({ loggedIn: false, unreadNotifications: 0, csrfToken: '' });
                    throw new Error(data.error || 'The notification request failed.');
                }
                return data;
            });
        });
    }

    function setStatus(message, isError) {
        if (!settingsPanel) return;
        var status = settingsPanel.querySelector('[data-miq-notification-status]');
        if (status) {
            status.textContent = message || '';
            status.classList.toggle('text-danger', !!isError);
        }
    }

    function preferenceInputs() {
        return Array.prototype.slice.call(settingsPanel ? settingsPanel.querySelectorAll('[data-miq-notification-preference]') : []);
    }

    function rememberDevice(channel, payload) {
        var id = payload && payload.device ? parseInt(payload.device.id, 10) : 0;
        if (id > 0) storageSet(channel === 'android' ? androidDeviceStorageKey : webDeviceStorageKey, id);
    }

    function clearLocalRegistration(channel) {
        if (channel === 'android') {
            storageRemove(androidOptInStorageKey);
            storageRemove(androidOptInUserStorageKey);
            storageRemove(androidDeviceStorageKey);
            try { if (androidBridge()) callAndroidBridge('deleteToken'); } catch (error) {}
            return Promise.resolve();
        }
        storageRemove(webOptInStorageKey);
        storageRemove(webOptInUserStorageKey);
        storageRemove(webDeviceStorageKey);
        storageRemove(tokenStorageKey);
        if (messaging && typeof messaging.deleteToken === 'function') return messaging.deleteToken().catch(function () {});
        return Promise.resolve();
    }

    function renderDevices(devices) {
        if (!settingsPanel) return;
        var container = settingsPanel.querySelector('[data-miq-notification-devices]');
        if (!container) return;
        container.textContent = '';
        devices = Array.isArray(devices) ? devices : [];
        if (!devices.length) {
            var empty = document.createElement('p');
            empty.className = 'miq-asset-note';
            empty.textContent = 'No push devices are registered.';
            container.appendChild(empty);
            return;
        }
        var heading = document.createElement('p');
        heading.className = 'miq-asset-note';
        heading.textContent = 'Registered push devices';
        container.appendChild(heading);
        devices.forEach(function (device) {
            var row = document.createElement('div');
            row.className = 'miq-notification-device';
            var copy = document.createElement('div');
            copy.className = 'miq-notification-device-copy';
            var title = document.createElement('strong');
            title.textContent = device.channel === 'android' ? 'Android app' : 'Browser';
            copy.appendChild(title);
            var detail = document.createElement('small');
            detail.textContent = (device.label || device.app_version || 'Active device') + (device.last_seen_at ? ' · seen ' + device.last_seen_at + ' UTC' : '');
            copy.appendChild(detail);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-secondary';
            remove.textContent = 'Remove';
            remove.addEventListener('click', function () {
                remove.disabled = true;
                request('unregister_notification_device', 'POST', { device_id: Number(device.id) }).then(function (payload) {
                    var ownDeviceKey = device.channel === 'android' ? androidDeviceStorageKey : webDeviceStorageKey;
                    if (String(device.id) === storageGet(ownDeviceKey)) clearLocalRegistration(device.channel);
                    settings = payload;
                    renderDevices(payload.devices);
                    setStatus('The device was removed.');
                }).catch(function (error) {
                    remove.disabled = false;
                    setStatus(error.message, true);
                });
            });
            row.appendChild(copy);
            row.appendChild(remove);
            container.appendChild(row);
        });
    }

    function androidBridge() {
        var bridge = window.MiqAndroidNotifications;
        return bridge && (typeof bridge.requestPermission === 'function' || typeof bridge.postMessage === 'function') ? bridge : null;
    }

    function callAndroidBridge(action) {
        var bridge = androidBridge();
        if (!bridge) throw new Error('The Android notification bridge is unavailable.');
        if (typeof bridge[action] === 'function') bridge[action]();
        else bridge.postMessage(JSON.stringify({ action: action }));
    }

    function contextSnapshot(category, payload) {
        var bridge = androidBridge();
        var channel = bridge ? 'android' : 'web';
        var config = payload && payload.web ? payload.web : getConfig();
        var categoryKey = contextStorageKey('category', category, channel);
        var globalKey = contextStorageKey('global', '', channel);
        var categoryRecord = contextRecord(categoryKey);
        var globalRecord = contextRecord(globalKey);
        var browserSupported = typeof Notification !== 'undefined' && typeof navigator !== 'undefined' && 'serviceWorker' in navigator;
        var browserBlocked = !bridge && browserSupported && Notification.permission === 'denied';
        return {
            loggedIn: !!state.loggedIn,
            channel: channel,
            supported: !!bridge || browserSupported,
            configured: bridge ? config.deliveryEnabled === true : config.enabled === true,
            channelEnabled: bridge
                ? optInBelongsToCurrentUser('android')
                : browserSupported && Notification.permission === 'granted' && optInBelongsToCurrentUser('web'),
            preferenceEnabled: !!(payload && payload.preferences && payload.preferences[category]),
            categoryCooldown: contextCooldownActive(categoryRecord),
            globalCooldown: contextCooldownActive(globalRecord),
            blocked: bridge
                ? !!categoryRecord.denied || !!globalRecord.denied
                : browserBlocked
        };
    }

    function saveContextPreference(category) {
        var values = {};
        values[category] = true;
        return request('save_notification_settings', 'POST', { preferences: values }).then(function (payload) {
            applySettings(payload);
            return payload;
        });
    }

    function enableContextNotifications(category, snapshot) {
        var enableChannel = snapshot.channelEnabled
            ? Promise.resolve(settings)
            : (snapshot.channel === 'android' ? enableAndroid() : enableBrowser());
        return enableChannel.then(function () {
            if (settings && settings.preferences && settings.preferences[category]) return settings;
            return saveContextPreference(category);
        });
    }

    function offerContext(category, source) {
        category = normalizedContextCategory(category);
        if (!category) return Promise.resolve(false);
        return startup().catch(function () { return null; }).then(function () {
            if (!state.loggedIn) return null;
            return loadNotificationSettings(false);
        }).then(function (payload) {
            if (!payload) return false;
            var snapshot = contextSnapshot(category, payload);
            var plan = contextOfferPolicy(category, snapshot);
            if (!plan) return false;
            return showContextInvite(category, String(source || ''), plan, snapshot);
        }).catch(function () { return false; });
    }

    function bindContextOffers() {
        if (contextOffersBound || typeof window.addEventListener !== 'function') return;
        contextOffersBound = true;
        window.addEventListener('miq:notification-context', function (event) {
            var detail = event && event.detail ? event.detail : {};
            offerContext(detail.category, detail.source);
        });
    }

    function applySettings(payload) {
        settings = payload || {};
        var preferences = settings.preferences || {};
        preferenceInputs().forEach(function (input) {
            input.checked = !!preferences[input.getAttribute('data-miq-notification-preference')];
        });
        renderDevices(settings.devices || []);
        var config = settings.web || getConfig();
        var bridge = androidBridge();
        var deliveryEnabled = config.deliveryEnabled !== false;
        var enable = settingsPanel && settingsPanel.querySelector('[data-miq-notification-enable]');
        var disable = settingsPanel && settingsPanel.querySelector('[data-miq-notification-disable]');
        if (enable) {
            enable.textContent = bridge ? 'Enable app notifications' : 'Enable browser notifications';
            enable.disabled = bridge ? !deliveryEnabled : !config.enabled;
            enable.title = bridge
                ? (deliveryEnabled ? '' : 'Push delivery is not configured on this deployment.')
                : (config.enabled ? '' : 'Browser push is not configured on this deployment.');
        }
        if (disable) disable.textContent = bridge ? 'Disable app notifications' : 'Disable browser notifications';
        if (bridge) {
            if (!deliveryEnabled) setStatus('Push delivery is not configured yet. In-app notifications and the account badge remain available.');
            else setStatus(optInBelongsToCurrentUser('android') ? 'Android app notifications are enabled.' : 'App notifications are off until you choose Enable app notifications.');
            return;
        }
        var permission = typeof Notification !== 'undefined' ? Notification.permission : 'unsupported';
        if (permission === 'denied') setStatus('Browser permission is blocked. Allow notifications for this site in browser settings, then try again.', true);
        else if (!config.enabled) setStatus('Browser push is not configured yet. In-app notifications and the account badge remain available.');
        else if (permission === 'granted' && optInBelongsToCurrentUser('web')) setStatus('Browser notifications are enabled for this browser.');
        else setStatus('Push is off until you choose Enable browser notifications.');
    }

    function loadNotificationSettings(force) {
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required to manage notifications.'));
        if (!force && settings) return Promise.resolve(settings);
        if (notificationSettingsLoadPromise) return notificationSettingsLoadPromise;
        notificationSettingsLoadPromise = request('get_notification_settings', 'GET').then(function (payload) {
            notificationSettingsLoadPromise = null;
            applySettings(payload);
            return payload;
        }).catch(function (error) {
            notificationSettingsLoadPromise = null;
            throw error;
        });
        return notificationSettingsLoadPromise;
    }

    function refresh() {
        if (!state.loggedIn || !settingsPanel) return Promise.resolve(null);
        setStatus('Loading notification settings...');
        return loadNotificationSettings(true).catch(function (error) {
            setStatus(error.message, true);
            throw error;
        });
    }

    function loadScript(url) {
        return new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[data-miq-firebase-script="' + url + '"]');
            if (existing) {
                if (window.firebase) { resolve(); return; }
                existing.addEventListener('load', resolve, { once: true });
                existing.addEventListener('error', reject, { once: true });
                return;
            }
            var script = document.createElement('script');
            script.src = url;
            script.async = true;
            script.setAttribute('data-miq-firebase-script', url);
            script.onload = resolve;
            script.onerror = function () { reject(new Error('The browser notification library could not be loaded.')); };
            document.head.appendChild(script);
        });
    }

    function loadFirebase() {
        if (firebaseLoadPromise) return firebaseLoadPromise;
        var config = getConfig();
        var version = encodeURIComponent(config.sdkVersion || '11.10.0');
        firebaseLoadPromise = loadScript('https://www.gstatic.com/firebasejs/' + version + '/firebase-app-compat.js').then(function () {
            return loadScript('https://www.gstatic.com/firebasejs/' + version + '/firebase-messaging-compat.js');
        }).then(function () {
            if (!window.firebase) throw new Error('Firebase is unavailable in this browser.');
            if (!window.firebase.apps || !window.firebase.apps.length) window.firebase.initializeApp(config.firebase);
            messaging = window.firebase.messaging();
            return messaging;
        }).catch(function (error) {
            firebaseLoadPromise = null;
            throw error;
        });
        return firebaseLoadPromise;
    }

    function serviceWorkerUrl(config) {
        var configured = config.serviceWorkerUrl || '/service-worker.js';
        try {
            var parsed = new URL(configured, window.location.href);
            if (parsed.origin !== window.location.origin) return new URL('/service-worker.js', window.location.origin).toString();
            return parsed.toString();
        } catch (error) {
            return new URL('/service-worker.js', window.location.origin).toString();
        }
    }

    function safeClientUrl(value) {
        var fallback = (state.workspaceUrl || '/workspace') + '?tab=notifications';
        try {
            var parsed = new URL(value || fallback, window.location.href);
            return parsed.origin === window.location.origin && !parsed.username && !parsed.password ? parsed.toString() : fallback;
        } catch (error) { return fallback; }
    }

    function bindForegroundMessages() {
        if (foregroundBound || !messaging || typeof messaging.onMessage !== 'function') return;
        foregroundBound = true;
        messaging.onMessage(function (payload) {
            applyResponseCounts(payload || {});
            var notification = payload && payload.notification ? payload.notification : {};
            if (typeof Notification !== 'undefined' && Notification.permission === 'granted' && notification.title) {
                var foreground = new Notification(notification.title, {
                    body: notification.body || '',
                    icon: notification.icon || ((state.assetBaseUrl || '/assets') + '/img/360Logo_192.png'),
                    tag: notification.tag || 'miq-foreground-notification'
                });
                foreground.onclick = function () {
                    window.location.href = safeClientUrl(payload.data && (payload.data.link_url || payload.data.url));
                };
            }
        });
    }

    function bindServiceWorkerMessages() {
        if (typeof navigator === 'undefined' || !navigator.serviceWorker || typeof navigator.serviceWorker.addEventListener !== 'function') return;
        navigator.serviceWorker.addEventListener('message', function (event) {
            var payload = event.data || {};
            if (payload.type === 'miq-notification') applyResponseCounts(payload.data || payload);
        });
    }

    function syncBrowserRegistration(explicitOptIn) {
        var config = getConfig();
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required to enable notifications.'));
        if (!config.enabled) return Promise.reject(new Error('Browser push is not configured on this deployment.'));
        if (typeof Notification === 'undefined' || typeof navigator === 'undefined' || !('serviceWorker' in navigator)) {
            return Promise.reject(new Error('This browser does not support push notifications.'));
        }
        if (Notification.permission !== 'granted') return Promise.reject(new Error('Browser notification permission is not granted.'));
        var currentInstallation = installationId('web');
        return navigator.serviceWorker.register(serviceWorkerUrl(config)).then(function (registration) {
            return loadFirebase().then(function (loadedMessaging) {
                messaging = loadedMessaging;
                bindForegroundMessages();
                return messaging.getToken({ vapidKey: config.vapidKey, serviceWorkerRegistration: registration });
            });
        }).then(function (token) {
            if (!token) throw new Error('The browser did not return a push token.');
            return request('register_notification_device', 'POST', {
                channel: 'web',
                token: token,
                installation_id: currentInstallation,
                label: window.location.hostname + ' browser',
                app_version: navigator.userAgent.slice(0, 40)
            }).then(function (payload) {
                rememberDevice('web', payload);
                storageSet(tokenStorageKey, token);
                if (explicitOptIn || optInBelongsToCurrentUser('web')) markOptedIn('web');
                applySettings(payload);
                return payload;
            });
        });
    }

    function enableBrowser() {
        var config = getConfig();
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required to enable notifications.'));
        if (!config.enabled) return Promise.reject(new Error('Browser push is not configured on this deployment.'));
        if (typeof Notification === 'undefined' || typeof navigator === 'undefined' || !('serviceWorker' in navigator)) {
            return Promise.reject(new Error('This browser does not support push notifications.'));
        }
        if (Notification.permission === 'denied') return Promise.reject(new Error('Browser permission is blocked. Allow notifications for this site in browser settings first.'));
        setStatus('Waiting for browser permission...');
        return Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') throw new Error('Browser notifications remain off until you allow them.');
            setStatus('Registering this browser...');
            markOptedIn('web');
            return syncBrowserRegistration(true);
        }).then(function (payload) {
            setStatus('Browser notifications are enabled.');
            return payload;
        }).catch(function (error) {
            if (Notification.permission !== 'granted') {
                storageRemove(webOptInStorageKey);
                storageRemove(webOptInUserStorageKey);
            }
            setStatus(error.message, true);
            throw error;
        });
    }

    function unregisterInstallation(channel, id, token) {
        if (!state.loggedIn || !id) return Promise.resolve(null);
        return request('unregister_notification_device', 'POST', {
            channel: channel,
            installation_id: id,
            token: String(token || '')
        });
    }

    function disableBrowser() {
        var currentInstallation = installationId('web');
        return unregisterInstallation('web', currentInstallation, storageGet(tokenStorageKey)).then(function (payload) {
            return clearLocalRegistration('web').then(function () { return payload; });
        }).then(function (payload) {
            if (payload) applySettings(payload);
            setStatus('Browser notifications are disabled.');
            return payload;
        }).catch(function (error) {
            setStatus(error.message, true);
            throw error;
        });
    }

    function reconcileBrowserRegistration() {
        var optedIn = optInBelongsToCurrentUser('web');
        if (!optedIn && (storageGet(webOptInStorageKey) === '1' || storageGet(tokenStorageKey))) {
            var staleInstallation = installationId('web');
            var staleToken = storageGet(tokenStorageKey);
            var retireStale = state.loggedIn
                ? unregisterInstallation('web', staleInstallation, staleToken).catch(function () {})
                : Promise.resolve(null);
            return retireStale.then(function () { return clearLocalRegistration('web'); }).then(function () { return null; });
        }
        if (!optedIn || !state.loggedIn || androidBridge()) return Promise.resolve(null);
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') {
            return unregisterInstallation('web', installationId('web'), storageGet(tokenStorageKey)).catch(function () {}).then(function () {
                return clearLocalRegistration('web').then(function () { return null; });
            });
        }
        return syncBrowserRegistration(false).catch(function (error) {
            setStatus('Browser notification refresh failed: ' + error.message, true);
            return null;
        });
    }

    function registerAndroidToken(token, metadata) {
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required to register an Android device.'));
        metadata = metadata || {};
        var currentInstallation = installationId('android', metadata.installation_id);
        return request('register_notification_device', 'POST', {
            channel: 'android',
            token: String(token || ''),
            installation_id: currentInstallation,
            label: metadata.label || 'Android app',
            app_version: metadata.app_version || ''
        }).then(function (payload) {
            rememberDevice('android', payload);
            markOptedIn('android');
            applySettings(payload);
            return payload;
        });
    }

    function androidPermissionResult(granted, token, metadata) {
        if (typeof metadata === 'string') {
            try { metadata = JSON.parse(metadata); } catch (error) { metadata = {}; }
        }
        var completion = androidPermissionRequest;
        androidPermissionRequest = null;
        if (!granted) {
            var failureMessage = metadata && metadata.error
                ? String(metadata.error).slice(0, 160)
                : 'Android notification permission was not granted.';
            var cleanup = unregisterInstallation('android', installationId('android', metadata && metadata.installation_id))
                .catch(function () {})
                .then(function () { return clearLocalRegistration('android'); });
            if (completion) completion.reject(new Error(failureMessage));
            return cleanup;
        }
        if (!completion && !optInBelongsToCurrentUser('android')) {
            unregisterInstallation('android', installationId('android', metadata && metadata.installation_id)).catch(function () {}).then(function () {
                clearLocalRegistration('android');
            });
            return;
        }
        registerAndroidToken(token, metadata || {}).then(function (payload) {
            setStatus('Android app notifications are enabled.');
            if (completion) completion.resolve(payload);
        }).catch(function (error) {
            setStatus(error.message, true);
            if (completion) completion.reject(error);
        });
    }

    function enableAndroid() {
        var bridge = androidBridge();
        if (!bridge) return Promise.reject(new Error('The Android notification bridge is unavailable.'));
        if (!state.loggedIn) return Promise.reject(new Error('Sign in is required to enable notifications.'));
        if (getConfig().deliveryEnabled === false) return Promise.reject(new Error('Push delivery is not configured on this deployment.'));
        if (androidPermissionRequest) return androidPermissionRequest.promise;
        var resolveRequest;
        var rejectRequest;
        var promise = new Promise(function (resolve, reject) { resolveRequest = resolve; rejectRequest = reject; });
        androidPermissionRequest = { promise: promise, resolve: resolveRequest, reject: rejectRequest };
        markOptedIn('android');
        try { callAndroidBridge('requestPermission'); } catch (error) {
            androidPermissionRequest = null;
            storageRemove(androidOptInStorageKey);
            storageRemove(androidOptInUserStorageKey);
            rejectRequest(error);
        }
        window.setTimeout(function () {
            if (!androidPermissionRequest || androidPermissionRequest.promise !== promise) return;
            androidPermissionRequest = null;
            storageRemove(androidOptInStorageKey);
            storageRemove(androidOptInUserStorageKey);
            rejectRequest(new Error('The Android notification request timed out.'));
        }, 45000);
        return promise;
    }

    function disableAndroid() {
        var currentInstallation = installationId('android');
        return unregisterInstallation('android', currentInstallation).then(function (payload) {
            // The server binding is already disabled; native cleanup is
            // best-effort and cannot turn a successful opt-out into an error.
            clearLocalRegistration('android');
            if (payload) applySettings(payload);
            setStatus('Android app notifications are disabled.');
            return payload;
        });
    }

    function reconcileAndroidRegistration() {
        var bridge = androidBridge();
        if (!bridge || !state.loggedIn) return Promise.resolve(null);
        if (!optInBelongsToCurrentUser('android')) {
            if (storageGet(androidOptInStorageKey) === '1') {
                return unregisterInstallation('android', installationId('android')).catch(function () {}).then(function () {
                    return clearLocalRegistration('android');
                });
            }
            return Promise.resolve(null);
        }
        try {
            callAndroidBridge('syncToken');
        } catch (error) {
            setStatus('Android notification refresh failed.', true);
        }
        return Promise.resolve(null);
    }

    function bindLogoutCleanup() {
        if (typeof document === 'undefined' || typeof document.addEventListener !== 'function') return;
        document.addEventListener('click', function (event) {
            var target = event.target;
            var link = target && typeof target.closest === 'function' ? target.closest('a[href]') : null;
            if (!link || link.href.indexOf('account_logout') === -1) return;
            var registrations = [];
            if (optInBelongsToCurrentUser('web')) {
                registrations.push(unregisterInstallation('web', installationId('web'), storageGet(tokenStorageKey)).catch(function () {}));
            }
            if (optInBelongsToCurrentUser('android')) {
                registrations.push(unregisterInstallation('android', installationId('android')).catch(function () {}));
            }
            if (!registrations.length) return;
            event.preventDefault();
            var destination = link.href;
            link.setAttribute('aria-busy', 'true');
            var timeout = new Promise(function (resolve) { window.setTimeout(resolve, 1200); });
            Promise.race([Promise.all(registrations), timeout]).then(function () {
                storageRemove(webOptInStorageKey);
                storageRemove(webOptInUserStorageKey);
                storageRemove(webDeviceStorageKey);
                storageRemove(tokenStorageKey);
                storageRemove(androidOptInStorageKey);
                storageRemove(androidOptInUserStorageKey);
                storageRemove(androidDeviceStorageKey);
                window.location.href = destination;
            });
        });
    }

    function bindPanel() {
        settingsPanel = document.querySelector('[data-miq-notification-settings]');
        if (!settingsPanel || settingsPanel.getAttribute('data-miq-notifications-bound') === 'true') return;
        settingsPanel.setAttribute('data-miq-notifications-bound', 'true');
        preferenceInputs().forEach(function (input) {
            input.addEventListener('change', function () {
                var key = input.getAttribute('data-miq-notification-preference');
                input.disabled = true;
                var values = {};
                values[key] = input.checked;
                request('save_notification_settings', 'POST', { preferences: values }).then(function (payload) {
                    applySettings(payload);
                    setStatus('Notification preferences saved.');
                }).catch(function (error) {
                    input.checked = !input.checked;
                    setStatus(error.message, true);
                }).then(function () { input.disabled = false; });
            });
        });
        var enable = settingsPanel.querySelector('[data-miq-notification-enable]');
        if (enable) enable.addEventListener('click', function () {
            enable.disabled = true;
            var action = androidBridge() ? enableAndroid() : enableBrowser();
            action.catch(function (error) { setStatus(error.message, true); }).then(function () { enable.disabled = false; });
        });
        var disable = settingsPanel.querySelector('[data-miq-notification-disable]');
        if (disable) disable.addEventListener('click', function () {
            disable.disabled = true;
            var action = androidBridge() ? disableAndroid() : disableBrowser();
            action.catch(function (error) { setStatus(error.message, true); }).then(function () { disable.disabled = false; });
        });
        if (state.loggedIn) refresh().catch(function () {});
        else setStatus('Sign in to manage notification delivery settings.', true);
    }

    function startup() {
        if (startupPromise) return startupPromise;
        ensureContextStyles();
        bindContextOffers();
        bindServiceWorkerMessages();
        bindLogoutCleanup();
        startupPromise = bootstrapAccountState().then(function () {
            bindPanel();
            if (!state.loggedIn) return null;
            return androidBridge() ? reconcileAndroidRegistration() : reconcileBrowserRegistration();
        });
        return startupPromise;
    }

    var notificationApi = {
        refresh: refresh,
        enableBrowser: enableBrowser,
        disableBrowser: disableBrowser,
        enableAndroid: enableAndroid,
        disableAndroid: disableAndroid,
        registerAndroidToken: registerAndroidToken,
        androidPermissionResult: androidPermissionResult,
        updateUnread: updateUnread,
        offerContext: offerContext,
        startup: startup
    };

    if (window.__MIQ_NOTIFICATION_TEST__) {
        notificationApi._test = {
            contextOfferPolicy: contextOfferPolicy,
            contextCopy: contextCopy,
            contextCooldownActive: contextCooldownActive
        };
    }
    window.MIQNotifications = notificationApi;

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', startup);
    else startup();
}());
