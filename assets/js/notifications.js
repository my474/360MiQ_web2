(function () {
    'use strict';

    var state = window.__MIQ_ACCOUNT__ || {};
    var settingsPanel = null;
    var settings = null;
    var firebaseLoadPromise = null;
    var messaging = null;
    var foregroundBound = false;
    var tokenStorageKey = 'miq-notification-web-token';

    function getConfig() {
        return state.notificationConfig || {};
    }

    function displayCount(count) {
        count = Math.max(0, parseInt(count, 10) || 0);
        return count > 99 ? '99+' : String(count);
    }

    function updateUnread(count) {
        count = Math.max(0, parseInt(count, 10) || 0);
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

    function applyResponseCounts(payload) {
        if (!payload || typeof payload !== 'object') {
            return;
        }
        if (typeof payload.unread !== 'undefined') {
            updateUnread(payload.unread);
        } else if (typeof payload.unread_count !== 'undefined') {
            updateUnread(payload.unread_count);
        } else if (typeof payload.notifications_unread !== 'undefined') {
            updateUnread(payload.notifications_unread);
        } else if (payload.data && typeof payload.data.unread_count !== 'undefined') {
            updateUnread(payload.data.unread_count);
        } else if (payload.workspace && payload.workspace.counts) {
            updateUnread(payload.workspace.counts.notifications_unread);
        }
        if (payload.csrf_token) {
            state.csrfToken = payload.csrf_token;
        }
    }

    function request(action, method, payload) {
        method = method || 'GET';
        var url = state.apiUrl || '/account_api.php';
        var options = {
            method: method,
            credentials: 'same-origin',
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
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    data = {};
                }
                applyResponseCounts(data);
                if (!response.ok) {
                    throw new Error(data.error || 'The notification request failed.');
                }
                return data;
            });
        });
    }

    function setStatus(message, isError) {
        if (!settingsPanel) {
            return;
        }
        var status = settingsPanel.querySelector('[data-miq-notification-status]');
        if (status) {
            status.textContent = message || '';
            status.classList.toggle('text-danger', !!isError);
        }
    }

    function preferenceInputs() {
        return Array.prototype.slice.call(settingsPanel ? settingsPanel.querySelectorAll('[data-miq-notification-preference]') : []);
    }

    function renderDevices(devices) {
        if (!settingsPanel) {
            return;
        }
        var container = settingsPanel.querySelector('[data-miq-notification-devices]');
        if (!container) {
            return;
        }
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
                request('unregister_notification_device', 'POST', { device_id: Number(device.id) })
                    .then(function (payload) {
                        settings = payload;
                        renderDevices(payload.devices);
                        setStatus('The device was removed.');
                    })
                    .catch(function (error) {
                        remove.disabled = false;
                        setStatus(error.message, true);
                    });
            });
            row.appendChild(copy);
            row.appendChild(remove);
            container.appendChild(row);
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
        var enable = settingsPanel && settingsPanel.querySelector('[data-miq-notification-enable]');
        if (enable) {
            enable.disabled = !config.enabled;
            enable.title = config.enabled ? '' : 'Browser push is not configured on this deployment.';
        }
        var permission = typeof Notification !== 'undefined' ? Notification.permission : 'unsupported';
        if (permission === 'denied') {
            setStatus('Browser permission is blocked. Allow notifications for this site in browser settings, then try again.', true);
        } else if (!config.enabled) {
            setStatus('Browser push is not configured yet. In-app notifications and the account badge remain available.');
        } else if (permission === 'granted') {
            setStatus('Browser push is available for this account.');
        } else {
            setStatus('Push is off until you choose Enable browser notifications.');
        }
    }

    function refresh() {
        if (!state.loggedIn || !settingsPanel) {
            return Promise.resolve(null);
        }
        setStatus('Loading notification settings…');
        return request('get_notification_settings', 'GET').then(function (payload) {
            applySettings(payload);
            return payload;
        }).catch(function (error) {
            setStatus(error.message, true);
            throw error;
        });
    }

    function loadScript(url) {
        return new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[data-miq-firebase-script="' + url + '"]');
            if (existing) {
                existing.addEventListener('load', resolve, { once: true });
                existing.addEventListener('error', reject, { once: true });
                if (window.firebase) {
                    resolve();
                }
                return;
            }
            var script = document.createElement('script');
            script.src = url;
            script.async = true;
            script.setAttribute('data-miq-firebase-script', url);
            script.onload = resolve;
            script.onerror = function () {
                reject(new Error('The browser notification library could not be loaded.'));
            };
            document.head.appendChild(script);
        });
    }

    function loadFirebase() {
        if (firebaseLoadPromise) {
            return firebaseLoadPromise;
        }
        var config = getConfig();
        var version = encodeURIComponent(config.sdkVersion || '11.10.0');
        firebaseLoadPromise = loadScript('https://www.gstatic.com/firebasejs/' + version + '/firebase-app-compat.js')
            .then(function () {
                return loadScript('https://www.gstatic.com/firebasejs/' + version + '/firebase-messaging-compat.js');
            })
            .then(function () {
                if (!window.firebase) {
                    throw new Error('Firebase is unavailable in this browser.');
                }
                if (!window.firebase.apps || !window.firebase.apps.length) {
                    window.firebase.initializeApp(config.firebase);
                }
                messaging = window.firebase.messaging();
                return messaging;
            });
        return firebaseLoadPromise;
    }

    function serviceWorkerUrl(config) {
        var configured = config.serviceWorkerUrl || '/service-worker.js';
        try {
            var parsed = new URL(configured, window.location.href);
            if (parsed.origin !== window.location.origin) {
                return new URL('service-worker.js', document.baseURI || window.location.href).toString();
            }
            return parsed.toString();
        } catch (error) {
            return new URL('service-worker.js', document.baseURI || window.location.href).toString();
        }
    }

    function bindForegroundMessages() {
        if (foregroundBound || !messaging || typeof messaging.onMessage !== 'function') {
            return;
        }
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
                    window.location.href = (payload.data && (payload.data.link_url || payload.data.url)) || ((state.workspaceUrl || '/workspace') + '?tab=notifications');
                };
            }
        });
    }

    function bindServiceWorkerMessages() {
        if (typeof navigator === 'undefined' || !navigator.serviceWorker || typeof navigator.serviceWorker.addEventListener !== 'function') {
            return;
        }
        navigator.serviceWorker.addEventListener('message', function (event) {
            var payload = event.data || {};
            if (payload.type === 'miq-notification') {
                applyResponseCounts(payload.data || payload);
            }
        });
    }

    function enableBrowser() {
        var config = getConfig();
        if (!state.loggedIn) {
            return Promise.reject(new Error('Sign in is required to enable notifications.'));
        }
        if (!config.enabled) {
            return Promise.reject(new Error('Browser push is not configured on this deployment.'));
        }
        if (typeof Notification === 'undefined' || !('serviceWorker' in navigator)) {
            return Promise.reject(new Error('This browser does not support push notifications.'));
        }
        if (Notification.permission === 'denied') {
            return Promise.reject(new Error('Browser permission is blocked. Allow notifications for this site in browser settings first.'));
        }
        setStatus('Waiting for browser permission…');
        return Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') {
                throw new Error('Browser notifications remain off until you allow them.');
            }
            setStatus('Registering this browser…');
            return navigator.serviceWorker.register(serviceWorkerUrl(config));
        }).then(function (registration) {
            return loadFirebase().then(function (loadedMessaging) {
                messaging = loadedMessaging;
                bindForegroundMessages();
                return messaging.getToken({
                    vapidKey: config.vapidKey,
                    serviceWorkerRegistration: registration
                });
            });
        }).then(function (token) {
            if (!token) {
                throw new Error('The browser did not return a push token.');
            }
            return request('register_notification_device', 'POST', {
                channel: 'web',
                token: token,
                label: window.location.hostname + ' browser',
                app_version: navigator.userAgent.slice(0, 40)
            }).then(function (payload) {
                try {
                    localStorage.setItem(tokenStorageKey, token);
                } catch (error) {}
                applySettings(payload);
                setStatus('Browser notifications are enabled.');
                return payload;
            });
        }).catch(function (error) {
            setStatus(error.message, true);
            throw error;
        });
    }

    function getStoredToken() {
        try {
            return localStorage.getItem(tokenStorageKey) || '';
        } catch (error) {
            return '';
        }
    }

    function clearStoredToken() {
        try {
            localStorage.removeItem(tokenStorageKey);
        } catch (error) {}
    }

    function disableBrowser() {
        var token = getStoredToken();
        var removal = token
            ? request('unregister_notification_device', 'POST', { channel: 'web', token: token })
            : refresh().then(function (payload) {
                var browser = (payload && payload.devices || []).filter(function (device) {
                    return device.channel === 'web';
                });
                return Promise.all(browser.map(function (device) {
                    return request('unregister_notification_device', 'POST', { device_id: Number(device.id) });
                })).then(function () { return refresh(); });
            });
        return removal.then(function (payload) {
            clearStoredToken();
            if (messaging && typeof messaging.deleteToken === 'function') {
                return messaging.deleteToken().catch(function () {}).then(function () { return payload; });
            }
            return payload;
        }).then(function (payload) {
            applySettings(payload);
            setStatus('Browser notifications are disabled.');
            return payload;
        }).catch(function (error) {
            setStatus(error.message, true);
            throw error;
        });
    }

    function registerAndroidToken(token, metadata) {
        if (!state.loggedIn) {
            return Promise.reject(new Error('Sign in is required to register an Android device.'));
        }
        metadata = metadata || {};
        return request('register_notification_device', 'POST', {
            channel: 'android',
            token: token,
            label: metadata.label || 'Android app',
            app_version: metadata.app_version || ''
        });
    }

    function bindLogoutCleanup() {
        if (typeof document === 'undefined' || typeof document.addEventListener !== 'function') {
            return;
        }
        document.addEventListener('click', function (event) {
            var target = event.target;
            var link = target && typeof target.closest === 'function' ? target.closest('a[href]') : null;
            if (!link || link.href.indexOf('account_logout') === -1 || !getStoredToken()) {
                return;
            }
            event.preventDefault();
            var destination = link.href;
            link.setAttribute('aria-busy', 'true');
            var cleanup = request('unregister_notification_device', 'POST', {
                channel: 'web',
                token: getStoredToken()
            }).catch(function () {});
            var timeout = new Promise(function (resolve) {
                window.setTimeout(resolve, 1200);
            });
            Promise.race([cleanup, timeout]).then(function () {
                clearStoredToken();
                window.location.href = destination;
            });
        });
    }

    function bindPanel() {
        settingsPanel = document.querySelector('[data-miq-notification-settings]');
        if (!settingsPanel || settingsPanel.getAttribute('data-miq-notifications-bound') === 'true') {
            return;
        }
        settingsPanel.setAttribute('data-miq-notifications-bound', 'true');
        preferenceInputs().forEach(function (input) {
            input.addEventListener('change', function () {
                var key = input.getAttribute('data-miq-notification-preference');
                input.disabled = true;
                request('save_notification_settings', 'POST', { preferences: (function () {
                    var values = {};
                    values[key] = input.checked;
                    return values;
                })() }).then(function (payload) {
                    applySettings(payload);
                    setStatus('Notification preferences saved.');
                }).catch(function (error) {
                    input.checked = !input.checked;
                    setStatus(error.message, true);
                }).then(function () {
                    input.disabled = false;
                });
            });
        });
        var enable = settingsPanel.querySelector('[data-miq-notification-enable]');
        if (enable) {
            enable.addEventListener('click', function () {
                enable.disabled = true;
                enableBrowser().catch(function () {}).then(function () {
                    enable.disabled = false;
                });
            });
        }
        var disable = settingsPanel.querySelector('[data-miq-notification-disable]');
        if (disable) {
            disable.addEventListener('click', function () {
                disable.disabled = true;
                disableBrowser().catch(function () {}).then(function () {
                    disable.disabled = false;
                });
            });
        }
        if (state.loggedIn) {
            refresh().catch(function () {});
        } else {
            setStatus('Sign in to manage notification delivery settings.', true);
        }
    }

    window.MIQNotifications = {
        refresh: refresh,
        enableBrowser: enableBrowser,
        disableBrowser: disableBrowser,
        registerAndroidToken: registerAndroidToken,
        updateUnread: updateUnread
    };

    bindServiceWorkerMessages();
    bindLogoutCleanup();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPanel);
    } else {
        bindPanel();
    }
}());
