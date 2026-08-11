'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const schema = read('account/schema.sql');
const migration = read('account/migrations/20260811_add_notifications.sql');
const hardeningMigration = read('account/migrations/20260812_harden_notification_delivery.sql');
const config = read('account/config.php');
const db = read('account/db.php');
const bootstrap = read('account/bootstrap.php');
const auth = read('account/auth.php');
const lifecycle = read('account/lifecycle.php');
const cleanup = read('account/cleanup_rate_limits.php');
const notifications = read('account/notifications.php');
const productivity = read('account/productivity.php');
const worker = read('account/process_notification_queue.php');
const api = read('account_api.php');
const settings = read('account_settings.php');
const meta = read('meta.php');
const header = read('header.php');
const accountStyles = read('assets/css/account.css');
const client = read('assets/js/notifications.js');
const serviceWorker = read('assets/js/pwabuilder-sw.js');
const rootWorker = read('service-worker.js');
const chatSync = read('assets/js/chatbox-sync.js');
const blogPlugin = read('blog/wp-content/mu-plugins/360miq-theme-sync.php');
const blogScript = read('blog/wp-content/mu-plugins/360miq-theme-sync.js');
const blogStyles = read('blog/wp-content/mu-plugins/360miq-theme-sync.css');
const blogFooter = read('blog/wp-content/themes/startup-blog/footer.php');
const androidManifest = read('android/notification-integration/src/main/AndroidManifest.xml');
const androidBuild = read('android/notification-integration/build.gradle.kts');
const androidCoordinator = read('android/notification-integration/src/main/java/com/miq360/notifications/MiqNotificationCoordinator.kt');
const androidService = read('android/notification-integration/src/main/java/com/miq360/notifications/MiqFirebaseMessagingService.kt');
const androidIntent = read('android/notification-integration/src/main/java/com/miq360/notifications/MiqNotificationIntentHandler.kt');
const androidIcon = read('android/notification-integration/src/main/res/drawable/ic_stat_miq_notification.xml');

for (const table of [
    'miq_notification_preferences',
    'miq_notification_devices',
    'miq_notification_deliveries'
]) {
    assert.match(schema, new RegExp('CREATE TABLE IF NOT EXISTS ' + table));
    assert.match(migration, new RegExp('CREATE TABLE IF NOT EXISTS ' + table));
}

// Immutable installation/session ownership and a durable leased queue.
assert.match(schema, /installation_hash CHAR\(64\)/);
assert.match(schema, /session_hash CHAR\(64\)/);
assert.match(schema, /session_version INT UNSIGNED/);
assert.match(schema, /status ENUM\('pending', 'processing', 'retry', 'sent', 'failed', 'skipped'\)/);
assert.match(schema, /next_attempt_at DATETIME/);
assert.match(schema, /lease_token CHAR\(64\)/);
assert.match(schema, /requeue_requested TINYINT\(1\) NOT NULL DEFAULT 0/);
assert.match(schema, /KEY ix_miq_notification_delivery_status \(status, next_attempt_at, lease_expires_at, id\)/);
assert.match(hardeningMigration, /information_schema\.COLUMNS/);
assert.match(hardeningMigration, /DELETE delivery[\s\S]*notification\.id IS NULL/);
assert.match(hardeningMigration, /MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL/);
assert.match(hardeningMigration, /ADD COLUMN `requeue_requested`/);
assert.match(hardeningMigration, /ADD KEY ix_miq_notification_delivery_status \(status, next_attempt_at, lease_expires_at, id\)/);

assert.match(config, /FCM_PROJECT_ID/);
assert.match(config, /FCM_PRIVATE_KEY/);
assert.match(config, /FCM_WEB_VAPID_KEY/);
assert.match(config, /FCM_WORKER_BATCH_SIZE/);
assert.match(config, /FCM_MAX_DEVICES_PER_USER/);
assert.match(config, /MIQ_RATE_NOTIFICATION_DEVICE_LIMIT/);
assert.match(config, /FCM_DELIVERY_MAX_ATTEMPTS/);
assert.match(config, /FCM_RETRY_BASE_SECONDS/);
assert.match(config, /FCM_DELIVERY_LEASE_SECONDS/);
assert.match(db, /notification_preferences/);
assert.match(db, /notification_devices/);
assert.match(db, /notification_deliveries/);
assert.match(bootstrap, /require_once __DIR__ \. '\/notifications\.php'/);

assert.match(notifications, /miq_account_notification_clean_installation_id/);
assert.match(notifications, /miq_account_notification_delivery_preferences/);
assert.match(notifications, /miq_account_release_notification_device_binding/);
assert.match(notifications, /WHERE id = \? AND token_hash = \?/);
assert.match(notifications, /active push-device limit was reached/);
assert.match(notifications, /FOR UPDATE/);
assert.match(notifications, /session_hash = device\.session_hash/);
assert.match(notifications, /account_session\.expires_at > UTC_TIMESTAMP\(\)/);
assert.match(notifications, /could not be bound to an active session/);
assert.match(notifications, /notification\.read_at/);
assert.match(notifications, /NOTIFICATION_READ/);
assert.match(notifications, /status = 'processing'/);
assert.match(notifications, /lease_expires_at/);
assert.match(notifications, /status = IF\(requeue_requested = 1, 'pending', 'retry'\)/);
assert.match(notifications, /requeue_requested = IF\(status = 'processing'/);
assert.match(notifications, /status = IF\(requeue_requested = 1, 'pending', 'sent'\)/);
assert.match(notifications, /miq_account_notification_retry_delay/);
assert.match(notifications, /retry-after/);
assert.match(notifications, /type\.googleapis\.com\/google\.firebase\.fcm\.v1\.FcmError/);
assert.match(notifications, /'permanent' => \$fcm_code === 'UNREGISTERED'/);
assert.doesNotMatch(notifications, /'permanent'\s*=>[^\n]*404/);
assert.match(notifications, /if \(\(int\) \$response\['status'\] === 401\)[\s\S]*\$send_request\(true\)/);
assert.match(notifications, /https:\/\/fcm\.googleapis\.com\/v1\/projects/);
assert.match(notifications, /oauth2\.googleapis\.com\/token/);
assert.match(notifications, /miq_account_notification_web_config/);
assert.match(notifications, /\$service_account\['project_id'\]/);
assert.match(notifications, /channel.*web.*android/s);
assert.match(notifications, /function miq_account_enqueue_notification/);
assert.match(notifications, /function miq_account_process_notification_queue/);

assert.match(productivity, /miq_account_enqueue_notification/);
assert.doesNotMatch(productivity, /miq_account_dispatch_notification/);
assert.doesNotMatch(productivity, /miq_account_reset_notification_deliveries/);
assert.doesNotMatch(productivity, /miq_account_fcm_send/);
assert.match(worker, /PHP_SAPI !== 'cli'/);
assert.match(worker, /miq_account_process_notification_queue/);
assert.match(auth, /miq_account_unregister_notification_session[\s\S]*miq_account_remove_current_session/);
assert.match(auth, /function miq_account_login_user[\s\S]*miq_account_retire_current_notification_session\(\)/);
assert.match(lifecycle, /notification_deliveries[\s\S]*WHERE user_id = \?/);
assert.match(cleanup, /DELETE delivery FROM \{\$deliveries\}/);

// The blog page is cache-safe: no PHP-side reverse login or private identity HTML.
assert.match(api, /Cache-Control: private, no-store, max-age=0/);
assert.match(api, /Vary: Cookie/);
assert.match(api, /if \(\$action === 'account_bootstrap'\)[\s\S]*miq_api_json\(\$payload\)/);
assert(api.indexOf("if ($action === 'account_bootstrap')") < api.indexOf('$user = miq_api_user();'));
assert.match(api, /register_notification_device[\s\S]*installation_id/);
assert.match(api, /notification_device_user/);
assert.match(api, /unregister_notification_device[\s\S]*installation_id/);
assert.match(settings, /data-miq-notification-settings/);
assert.match(settings, /data-miq-notification-enable/);
assert.match(meta, /notificationConfig.*miq_account_notification_web_config/s);
assert.match(header, /assets\/js\/notifications\.js/);
assert.match(client, /replace\(\/\[\^A-Za-z0-9\._:-\]\/g, ''\)\.slice\(0, 128\)/);
assert.match(client, /miq-notification-web-user-id/);
assert.match(client, /optInBelongsToCurrentUser/);
assert.match(client, /if \(!granted\)[\s\S]*clearLocalRegistration\('android'\)/);
assert.match(blogPlugin, /data-miq-blog-account-shell/);
assert.match(blogPlugin, /data-miq-account-unread-badge/);
assert.doesNotMatch(blogPlugin, /miq_account_login_user/);
assert.doesNotMatch(blogPlugin, /miq_account_current_user/);
assert.doesNotMatch(blogPlugin, /miq_main_user_id/);
assert.doesNotMatch(blogPlugin, /csrf/i);
assert.match(blogFooter, /bootstrapRequired/);
assert.match(blogFooter, /action=account_bootstrap/);
assert.match(blogFooter, /credentials:'same-origin'/);
assert.match(blogFooter, /cache:'no-store'/);
assert.doesNotMatch(blogFooter, /miq_account_current_user/);
assert.doesNotMatch(blogFooter, /miq_account_find_user_by_id/);
assert.match(blogFooter, /assets\/js\/notifications\.js/);
assert.match(chatSync, /__MIQ_ACCOUNT_BOOTSTRAP_PROMISE__/);
assert.match(blogScript, /miq:account-state/);
assert.match(blogScript, /count > 99 \? '99\+' : String\(count\)/);
assert.match(blogStyles, /\.miq360-account-item\.is-authenticated\.is-open > \.miq360-account-menu/);
assert.match(accountStyles, /\.miq-notification-device/);
assert.match(accountStyles, /html\[data-theme="dark"\].*\.miq-notification-device/s);

// The worker has no install-time CDN/offline-placeholder dependency.
assert.match(rootWorker, /importScripts\('assets\/js\/pwabuilder-sw\.js'\)/);
assert.doesNotMatch(serviceWorker, /workbox|storage\.googleapis\.com|ToDo-replace-this-name/);
assert.match(serviceWorker, /addEventListener\('install'/);
assert.match(serviceWorker, /skipWaiting/);
assert.match(serviceWorker, /addEventListener\('push'/);
assert.match(serviceWorker, /addEventListener\('notificationclick'/);
assert.match(serviceWorker, /parsed\.origin === self\.location\.origin/);

// The checked-in Android module owns permission, token rotation, deep links, and icons.
assert.match(androidBuild, /id\("org\.jetbrains\.kotlin\.android"\)/);
assert.match(androidManifest, /android\.permission\.POST_NOTIFICATIONS/);
assert.match(androidManifest, /default_notification_icon/);
assert.match(androidManifest, /default_notification_channel_id/);
assert.match(androidCoordinator, /WebViewCompat\.addWebMessageListener/);
assert.match(androidCoordinator, /WebViewCompat\.removeWebMessageListener/);
assert.match(androidCoordinator, /setOf\(trustedOrigin\)/);
assert.match(androidCoordinator, /ActivityResultContracts\.RequestPermission/);
assert.match(androidCoordinator, /deleteToken\(\)/);
assert.match(androidCoordinator, /if \(!MiqNotificationContract\.optedIn\(activity\)\)[\s\S]*deleteProviderToken\(activity\)/);
assert.match(androidCoordinator, /if \(token\.isBlank\(\)\)[\s\S]*MiqNotificationContract\.setOptedIn\(activity, false\)/);
assert.match(androidCoordinator, /MIQNotifications\.updateUnread/);
assert.match(androidService, /override fun onNewToken/);
assert.match(androidService, /if \(MiqNotificationContract\.optedIn\(this\)\)[\s\S]*MiqNotificationContract\.saveToken/);
assert.match(androidService, /unread_count/);
assert.match(androidService, /R\.drawable\.ic_stat_miq_notification/);
assert.match(androidIntent, /uri\.scheme\.equals\("https", true\)/);
assert.match(androidIntent, /uri\.host\.equals\("360miq\.com", true\)/);
assert.match(androidIntent, /uri\.port != -1 && uri\.port != 443/);
assert.match(androidCoordinator, /isTrustedPage\(webView\.url\)/);
assert.match(androidIcon, /<vector/);

function response(payload, status = 200) {
    return {
        ok: status >= 200 && status < 300,
        status,
        json() { return Promise.resolve(payload); },
        text() { return Promise.resolve(JSON.stringify(payload)); }
    };
}

function createClientRuntime(options = {}) {
    const values = Object.assign({}, options.storage || {});
    const requests = [];
    const events = [];
    const badges = [{ textContent: '', hidden: true }, { textContent: '', hidden: true }];
    const trigger = {
        attributes: { 'data-account-aria-base': 'Account menu' },
        getAttribute(name) { return this.attributes[name] || null; },
        setAttribute(name, value) { this.attributes[name] = value; }
    };
    const fakeScript = { addEventListener() {} };
    const documentListeners = {};
    const documentObject = {
        readyState: 'complete',
        head: { appendChild() {} },
        querySelector(selector) {
            if (selector.indexOf('script[data-miq-firebase-script=') === 0 && options.firebase !== false) return fakeScript;
            return null;
        },
        querySelectorAll(selector) {
            if (selector === '[data-miq-account-unread-badge]') return badges;
            if (selector === '[data-miq-account-trigger]') return [trigger];
            return [];
        },
        createElement() {
            return { setAttribute() {}, addEventListener() {} };
        },
        addEventListener(type, listener) {
            if (!documentListeners[type]) documentListeners[type] = [];
            documentListeners[type].push(listener);
        }
    };
    let permissionCalls = 0;
    let registerCalls = 0;
    let getTokenCalls = 0;
    let deleteTokenCalls = 0;
    const nativeActions = [];
    const messaging = {
        getToken() { getTokenCalls += 1; return Promise.resolve(options.token || 'rotated-fcm-token'); },
        deleteToken() { deleteTokenCalls += 1; return Promise.resolve(true); },
        onMessage(handler) { this.messageHandler = handler; }
    };
    const firebase = {
        apps: [{}],
        initializeApp() {},
        messaging() { return messaging; }
    };
    function NotificationMock() {}
    NotificationMock.permission = options.permission || 'default';
    NotificationMock.requestPermission = function() {
        permissionCalls += 1;
        NotificationMock.permission = options.permissionResult || 'granted';
        return Promise.resolve(NotificationMock.permission);
    };
    class AccountEvent {
        constructor(type, init) { this.type = type; this.detail = init.detail; }
    }
    const state = Object.assign({
        loggedIn: true,
        userId: 7,
        csrfToken: 'csrf-1',
        apiUrl: 'https://360miq.com/account_api.php',
        workspaceUrl: 'https://360miq.com/workspace',
        assetBaseUrl: 'https://360miq.com/assets',
        unreadNotifications: 0,
        notificationConfig: {
            enabled: true,
            sdkVersion: '11.10.0',
            firebase: { apiKey: 'public', projectId: 'project' },
            vapidKey: 'public-vapid',
            serviceWorkerUrl: 'https://360miq.com/service-worker.js'
        }
    }, options.state || {});
    const storage = {
        getItem(key) { return Object.prototype.hasOwnProperty.call(values, key) ? values[key] : null; },
        setItem(key, value) { values[key] = String(value); },
        removeItem(key) { delete values[key]; }
    };
    const serviceWorkerApi = {
        register(url) { registerCalls += 1; this.lastUrl = url; return Promise.resolve({ scope: url }); },
        addEventListener() {}
    };
    const navigatorObject = { serviceWorker: serviceWorkerApi, userAgent: 'Regression Browser/1.0' };
    const windowObject = {
        __MIQ_ACCOUNT__: state,
        localStorage: storage,
        firebase,
        navigator: navigatorObject,
        Notification: NotificationMock,
        CustomEvent: AccountEvent,
        crypto: { randomUUID() { return '12345678-1234-4234-9234-123456789abc'; } },
        location: {
            href: 'https://360miq.com/account_settings',
            origin: 'https://360miq.com',
            hostname: '360miq.com'
        },
        dispatchEvent(event) { events.push(event); },
        setTimeout() {}
    };
    if (options.androidBridge) {
        windowObject.MiqAndroidNotifications = {
            postMessage(payload) {
                nativeActions.push(JSON.parse(payload).action);
            }
        };
    }
    const fetchMock = (url, fetchOptions = {}) => {
        requests.push({ url: String(url), options: fetchOptions });
        if (String(url).indexOf('action=account_bootstrap') !== -1) {
            return Promise.resolve(response(options.bootstrapPayload || state));
        }
        return Promise.resolve(response({
            saved: true,
            removed: true,
            device: { id: 17 },
            preferences: { price_alerts: true, community_replies: false, moderation: true },
            devices: [],
            web: state.notificationConfig,
            unread: 7,
            csrf_token: 'csrf-2'
        }));
    };
    windowObject.fetch = fetchMock;

    vm.runInNewContext(client, {
        window: windowObject,
        document: documentObject,
        navigator: navigatorObject,
        Notification: NotificationMock,
        fetch: fetchMock,
        console,
        URL,
        Array,
        Object,
        Promise,
        Math,
        Number,
        String,
        Date,
        encodeURIComponent
    });

    return {
        window: windowObject,
        state,
        storage: values,
        requests,
        events,
        badges,
        trigger,
        messaging,
        serviceWorkerApi,
        permissionCalls: () => permissionCalls,
        registerCalls: () => registerCalls,
        getTokenCalls: () => getTokenCalls,
        deleteTokenCalls: () => deleteTokenCalls,
        nativeActions
    };
}

function dispatchWorker(handlers, type, values = {}) {
    let pending = Promise.resolve();
    handlers[type](Object.assign({}, values, {
        waitUntil(promise) { pending = Promise.resolve(promise); }
    }));
    return pending;
}

async function testServiceWorkerBehavior() {
    const handlers = {};
    const shown = [];
    const posted = [];
    const opened = [];
    const deletedCaches = [];
    let skipped = 0;
    let claimed = 0;
    let clients = [{
        url: 'https://360miq.com/market',
        postMessage(message) { posted.push(message); },
        navigate(url) { this.url = url; return Promise.resolve(this); },
        focus() { this.focused = true; return Promise.resolve(this); }
    }];
    const selfObject = {
        location: { origin: 'https://360miq.com' },
        registration: {
            scope: 'https://360miq.com/',
            showNotification(title, options) { shown.push({ title, options }); return Promise.resolve(); }
        },
        clients: {
            claim() { claimed += 1; return Promise.resolve(); },
            matchAll() { return Promise.resolve(clients); },
            openWindow(url) { opened.push(url); return Promise.resolve({ url }); }
        },
        skipWaiting() { skipped += 1; return Promise.resolve(); },
        addEventListener(type, handler) { handlers[type] = handler; }
    };
    vm.runInNewContext(serviceWorker, {
        self: selfObject,
        caches: { delete(name) { deletedCaches.push(name); return Promise.resolve(true); } },
        URL,
        Promise,
        Date,
        Math,
        parseInt
    });

    await dispatchWorker(handlers, 'install');
    await dispatchWorker(handlers, 'activate');
    assert.strictEqual(skipped, 1);
    assert.strictEqual(claimed, 1);
    assert.deepStrictEqual(deletedCaches, ['pwabuilder-page']);

    await dispatchWorker(handlers, 'push', {
        data: {
            json() {
                return {
                    notification: { title: 'Price alert', body: 'ABC reached 10.00' },
                    data: { notification_id: '42', unread_count: '123', link_url: 'https://360miq.com/workspace?tab=notifications' }
                };
            }
        }
    });
    assert.strictEqual(shown.length, 1);
    assert.strictEqual(shown[0].title, 'Price alert');
    assert.strictEqual(shown[0].options.data.notification_id, '42');
    assert.strictEqual(shown[0].options.data.unread_count, 123);
    assert.strictEqual(posted.length, 1);

    let closed = 0;
    clients = [];
    await dispatchWorker(handlers, 'notificationclick', {
        notification: {
            data: { url: 'https://attacker.example/private' },
            close() { closed += 1; }
        }
    });
    assert.strictEqual(closed, 1);
    assert.deepStrictEqual(opened, ['https://360miq.com/workspace?tab=notifications']);

    handlers.push({ data: null, waitUntil() { throw new Error('An empty push must not create work.'); } });
    assert.strictEqual(shown.length, 1);
}

async function testClientBehavior() {
    const quiet = createClientRuntime({ permission: 'default' });
    await quiet.window.MIQNotifications.startup();
    assert.strictEqual(quiet.permissionCalls(), 0, 'Page load must never request notification permission.');
    quiet.window.MIQNotifications.updateUnread(123);
    assert.strictEqual(quiet.badges[0].textContent, '99+');
    assert.strictEqual(quiet.badges[0].hidden, false);
    assert.strictEqual(quiet.trigger.attributes['aria-label'], 'Account menu, 123 unread notifications');

    const signedOut = createClientRuntime({ state: { loggedIn: false, userId: null } });
    await signedOut.window.MIQNotifications.startup();
    signedOut.window.MIQNotifications.updateUnread(123);
    assert.strictEqual(signedOut.badges[0].hidden, true, 'Late pushes must not expose a badge after logout.');
    assert.strictEqual(signedOut.badges[0].textContent, '0');
    assert.strictEqual(signedOut.trigger.attributes['aria-label'], 'Account menu');

    const optedIn = createClientRuntime({
        permission: 'granted',
        token: 'rotated-token',
        storage: {
            'miq-notification-web-enabled': '1',
            'miq-notification-web-user-id': '7',
            'miq-notification-web-token': 'previous-token',
            'miq-notification-web-installation-v1': 'web-stable-installation-12345'
        }
    });
    await optedIn.window.MIQNotifications.startup();
    assert.strictEqual(optedIn.permissionCalls(), 0, 'An opted-in startup refresh must not reopen the permission prompt.');
    assert.strictEqual(optedIn.registerCalls(), 1);
    assert.strictEqual(optedIn.getTokenCalls(), 1);
    const registerRequest = optedIn.requests.find((request) => request.options.method === 'POST');
    const registerBody = JSON.parse(registerRequest.options.body);
    assert.strictEqual(registerBody.action, 'register_notification_device');
    assert.strictEqual(registerBody.channel, 'web');
    assert.strictEqual(registerBody.installation_id, 'web-stable-installation-12345');
    assert.strictEqual(registerBody.token, 'rotated-token');
    assert.strictEqual(optedIn.storage['miq-notification-web-device-id'], '17');

    await optedIn.window.MIQNotifications.disableBrowser();
    const unregisterBody = JSON.parse(optedIn.requests[optedIn.requests.length - 1].options.body);
    assert.strictEqual(unregisterBody.action, 'unregister_notification_device');
    assert.strictEqual(unregisterBody.channel, 'web');
    assert.strictEqual(unregisterBody.installation_id, 'web-stable-installation-12345');
    assert.strictEqual(unregisterBody.token, 'rotated-token');
    assert.strictEqual(Object.prototype.hasOwnProperty.call(unregisterBody, 'device_id'), false);
    assert.strictEqual(optedIn.deleteTokenCalls(), 1);
    assert.strictEqual(optedIn.storage['miq-notification-web-enabled'], undefined);
    assert.strictEqual(optedIn.storage['miq-notification-web-user-id'], undefined);
    assert.strictEqual(optedIn.storage['miq-notification-web-device-id'], undefined);

    const otherAccount = createClientRuntime({
        permission: 'granted',
        storage: {
            'miq-notification-web-enabled': '1',
            'miq-notification-web-user-id': '8',
            'miq-notification-web-token': 'another-account-token',
            'miq-notification-web-installation-v1': 'web-shared-installation-12345'
        }
    });
    await otherAccount.window.MIQNotifications.startup();
    assert.strictEqual(otherAccount.permissionCalls(), 0);
    assert.strictEqual(otherAccount.registerCalls(), 0, 'Push consent must not transfer between signed-in accounts.');
    const retirement = JSON.parse(otherAccount.requests[0].options.body);
    assert.strictEqual(retirement.action, 'unregister_notification_device');
    assert.strictEqual(otherAccount.storage['miq-notification-web-enabled'], undefined);

    const explicit = createClientRuntime({ permission: 'default', permissionResult: 'granted' });
    await explicit.window.MIQNotifications.startup();
    await explicit.window.MIQNotifications.enableBrowser();
    assert.strictEqual(explicit.permissionCalls(), 1, 'Only the explicit enable action may request browser permission.');
    assert.strictEqual(explicit.registerCalls(), 1);
    assert.strictEqual(explicit.storage['miq-notification-web-user-id'], '7');

    const androidFailure = createClientRuntime({ androidBridge: true });
    await androidFailure.window.MIQNotifications.startup();
    const androidEnable = androidFailure.window.MIQNotifications.enableAndroid().then(
        function () { return null; },
        function (error) { return error; }
    );
    assert.deepStrictEqual(androidFailure.nativeActions, ['requestPermission']);
    const androidCleanup = androidFailure.window.MIQNotifications.androidPermissionResult(false, '', {
        installation_id: 'android-stable-installation-12345',
        error: 'FCM token unavailable'
    });
    const androidError = await androidEnable;
    await androidCleanup;
    assert.strictEqual(androidError.message, 'FCM token unavailable');
    assert.deepStrictEqual(androidFailure.nativeActions, ['requestPermission', 'deleteToken']);
    const androidUnregister = JSON.parse(androidFailure.requests.find(function (entry) {
        return entry.options.body && entry.options.body.indexOf('unregister_notification_device') !== -1;
    }).options.body);
    assert.strictEqual(androidUnregister.installation_id, 'android-stable-installation-12345');
    assert.strictEqual(androidFailure.storage['miq-notification-android-enabled'], undefined);
    assert.strictEqual(androidFailure.storage['miq-notification-android-user-id'], undefined);

    const blogBootstrap = createClientRuntime({
        permission: 'default',
        state: {
            loggedIn: false,
            bootstrapRequired: true,
            bootstrapUrl: 'https://360miq.com/account_api.php?action=account_bootstrap',
            unreadNotifications: 0
        },
        bootstrapPayload: {
            loggedIn: true,
            displayName: 'Blog User',
            csrfToken: 'blog-csrf',
            unreadNotifications: 4,
            notificationConfig: { enabled: false }
        }
    });
    await blogBootstrap.window.MIQNotifications.startup();
    assert.strictEqual(blogBootstrap.requests[0].options.cache, 'no-store');
    assert.strictEqual(blogBootstrap.state.loggedIn, true);
    assert.strictEqual(blogBootstrap.badges[0].textContent, '4');
    assert(blogBootstrap.events.some((event) => event.type === 'miq:account-state'));
    assert.strictEqual(blogBootstrap.permissionCalls(), 0);
}

(async function run() {
    await testServiceWorkerBehavior();
    await testClientBehavior();
    console.log('Notification system regression checks passed.');
}()).catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
