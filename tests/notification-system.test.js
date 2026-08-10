'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const schema = read('account/schema.sql');
const migration = read('account/migrations/20260811_add_notifications.sql');
const config = read('account/config.php');
const db = read('account/db.php');
const bootstrap = read('account/bootstrap.php');
const notifications = read('account/notifications.php');
const productivity = read('account/productivity.php');
const api = read('account_api.php');
const settings = read('account_settings.php');
const meta = read('meta.php');
const header = read('header.php');
const accountStyles = read('assets/css/account.css');
const client = read('assets/js/notifications.js');
const serviceWorker = read('assets/js/pwabuilder-sw.js');
const blogPlugin = read('blog/wp-content/mu-plugins/360miq-theme-sync.php');
const blogFooter = read('blog/wp-content/themes/startup-blog/footer.php');

for (const table of [
    'miq_notification_preferences',
    'miq_notification_devices',
    'miq_notification_deliveries'
]) {
    assert.match(schema, new RegExp('CREATE TABLE IF NOT EXISTS ' + table));
    assert.match(migration, new RegExp('CREATE TABLE IF NOT EXISTS ' + table));
}

assert.match(config, /FCM_PROJECT_ID/);
assert.match(config, /FCM_PRIVATE_KEY/);
assert.match(config, /FCM_WEB_VAPID_KEY/);
assert.match(db, /notification_preferences/);
assert.match(db, /notification_devices/);
assert.match(db, /notification_deliveries/);
assert.match(bootstrap, /require_once __DIR__ \. '\/notifications\.php'/);
assert.match(notifications, /https:\/\/fcm\.googleapis\.com\/v1\/projects/);
assert.match(notifications, /oauth2\.googleapis\.com\/token/);
assert.match(notifications, /miq_account_notification_web_config/);
assert.match(notifications, /channel.*web.*android/s);
assert.match(productivity, /miq_account_dispatch_notification/);
assert.match(productivity, /miq_account_reset_notification_deliveries/);
assert.match(api, /get_notification_settings/);
assert.match(api, /save_notification_settings/);
assert.match(api, /register_notification_device/);
assert.match(api, /unregister_notification_device/);
assert.match(settings, /data-miq-notification-settings/);
assert.match(settings, /data-miq-notification-enable/);
assert.match(meta, /notificationConfig.*miq_account_notification_web_config/s);
assert.match(header, /assets\/js\/notifications\.js/);
assert.match(client, /Notification\.requestPermission\(\)/);
assert.match(client, /navigator\.serviceWorker\.register/);
assert.match(client, /registerAndroidToken/);
assert.match(client, /data-miq-account-unread-badge/);
assert.match(serviceWorker, /addEventListener\('push'/);
assert.match(serviceWorker, /addEventListener\('notificationclick'/);
assert.match(blogPlugin, /miq_account_find_user_by_id/);
assert.match(blogPlugin, /data-miq-account-unread-badge/);
assert.match(blogFooter, /window\.__MIQ_ACCOUNT__/);
assert.match(blogFooter, /assets\/js\/notifications\.js/);
assert.match(blogFooter, /miq_blog_main_site_url/);
assert.match(blogFooter, /workspaceUrl/);
assert.match(blogFooter, /assetBaseUrl/);
assert.match(accountStyles, /\.miq-notification-device/);
assert.match(accountStyles, /html\[data-theme="dark"\].*\.miq-notification-device/s);

let permissionCalls = 0;
const badges = [{ textContent: '', hidden: true }, { textContent: '', hidden: true }];
const triggers = [{
    attributes: { 'data-account-aria-base': 'Account menu' },
    getAttribute(name) { return this.attributes[name] || null; },
    setAttribute(name, value) { this.attributes[name] = value; }
}];
const notificationDocument = {
    readyState: 'complete',
    querySelector() {
        return null;
    },
    querySelectorAll(selector) {
        if (selector === '[data-miq-account-unread-badge]') return badges;
        if (selector === '[data-miq-account-trigger]') return triggers;
        return [];
    }
};
const notificationWindow = {
    __MIQ_ACCOUNT__: { loggedIn: true, notificationConfig: { enabled: true } },
    location: { href: 'https://360miq.com/account_settings' },
    document: notificationDocument
};
vm.runInNewContext(client, {
    window: notificationWindow,
    document: notificationDocument,
    Notification: { requestPermission() { permissionCalls += 1; } },
    console,
    Array,
    Object,
    Promise,
    Math,
    Number,
    String,
    encodeURIComponent
});
assert.strictEqual(permissionCalls, 0, 'Notification permission must not be requested on page load.');
notificationWindow.MIQNotifications.updateUnread(123);
assert.strictEqual(badges[0].textContent, '99+');
assert.strictEqual(badges[0].hidden, false);
assert.strictEqual(triggers[0].attributes['aria-label'], 'Account menu, 123 unread notifications');

console.log('Notification system regression checks passed.');
