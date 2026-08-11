'use strict';

var assert = require('assert');
var fs = require('fs');
var path = require('path');
var vm = require('vm');

var root = path.resolve(__dirname, '..');
var notificationsPath = path.join(root, 'assets', 'js', 'notifications.js');
var notificationsSource = fs.readFileSync(notificationsPath, 'utf8');
assert(notificationsSource.indexOf('30 * 24 * 60 * 60 * 1000') !== -1, 'context offers should use a 30-day cooldown');
assert(notificationsSource.indexOf("primary.addEventListener('click'") < notificationsSource.indexOf('Notification.requestPermission()'), 'browser permission should remain behind an explicit prompt click');
var sandbox = {
    window: {
        __MIQ_ACCOUNT__: { loggedIn: true, userId: 42 },
        __MIQ_NOTIFICATION_TEST__: true,
        addEventListener: function () {},
        location: { origin: 'https://360miq.com', href: 'https://360miq.com/workspace' },
        setTimeout: setTimeout
    },
    document: {
        readyState: 'loading',
        addEventListener: function () {}
    },
    navigator: {},
    Notification: { permission: 'default' },
    URL: URL,
    Promise: Promise,
    setTimeout: setTimeout
};

vm.runInNewContext(notificationsSource, sandbox, { filename: notificationsPath });

var hooks = sandbox.window.MIQNotifications && sandbox.window.MIQNotifications._test;
assert(hooks, 'notification policy test hooks should be exposed only in test mode');

function snapshot(overrides) {
    return Object.assign({
        loggedIn: true,
        supported: true,
        configured: true,
        channelEnabled: false,
        preferenceEnabled: false,
        categoryCooldown: false,
        globalCooldown: false,
        blocked: false
    }, overrides || {});
}

assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot()), 'channel');
assert.strictEqual(hooks.contextOfferPolicy('community_replies', snapshot({ channelEnabled: true })), 'preference');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ blocked: true })), 'blocked');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ channelEnabled: true, preferenceEnabled: true })), '');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ categoryCooldown: true })), '');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ globalCooldown: true })), '');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ channelEnabled: true, globalCooldown: true })), 'preference');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ loggedIn: false })), '');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ supported: false })), '');
assert.strictEqual(hooks.contextOfferPolicy('price_alerts', snapshot({ configured: false })), '');
assert.strictEqual(hooks.contextOfferPolicy('unknown', snapshot()), '');

assert.strictEqual(hooks.contextCopy('price_alerts', 'price_alert', 'channel', 'web').title, 'Get this price alert on this device');
assert(hooks.contextCopy('community_replies', 'idea', 'channel', 'web').body.indexOf('your idea') !== -1);
assert(hooks.contextCopy('community_replies', 'reply', 'channel', 'web').body.indexOf('discussion continues') !== -1);
assert(hooks.contextCopy('community_replies', 'bookmark', 'channel', 'web').body.indexOf('bookmarked idea') !== -1);
assert.strictEqual(hooks.contextCopy('price_alerts', 'price_alert', 'blocked', 'android').title, 'App notifications are blocked');
assert.strictEqual(hooks.contextCooldownActive({ nextAt: 200 }, 100), true);
assert.strictEqual(hooks.contextCooldownActive({ nextAt: 100 }, 100), false);

var css = fs.readFileSync(path.join(root, 'assets', 'css', 'notification-invite.css'), 'utf8');
assert(css.indexOf('.miq-notification-invite {') !== -1, 'light-mode base styles should exist');
assert(css.indexOf('html[data-theme="dark"] .miq-notification-invite') !== -1, 'explicit dark-mode styles should exist');
assert(css.indexOf('.bottom-nav ~ .miq-notification-invite') !== -1, 'mobile prompt should clear the main-site bottom navigation');
assert(css.indexOf('@media (prefers-reduced-motion: reduce)') !== -1, 'reduced-motion behavior should exist');

var metaSource = fs.readFileSync(path.join(root, 'meta.php'), 'utf8');
var themeSource = fs.readFileSync(path.join(root, 'assets', 'js', 'theme.js'), 'utf8');
assert(metaSource.indexOf("document.documentElement.setAttribute('data-theme',dark?'dark':'light')") !== -1, 'initial light and dark themes should be applied before styles load');
assert(themeSource.indexOf("htmlEl.setAttribute('data-theme', theme)") !== -1, 'live light-dark-light toggles should re-evaluate prompt CSS without a reload');

var workspaceSource = fs.readFileSync(path.join(root, 'assets', 'js', 'workspace.js'), 'utf8');
var communitySource = fs.readFileSync(path.join(root, 'assets', 'js', 'community.js'), 'utf8');
assert(workspaceSource.indexOf("offerNotificationContext('price_alerts', 'price_alert')") !== -1);
assert(communitySource.indexOf("offerNotificationContext('idea')") !== -1);
assert(communitySource.indexOf("offerNotificationContext('reply')") !== -1);
assert(communitySource.indexOf("if (bookmarked) offerNotificationContext('bookmark')") !== -1);

var headerSource = fs.readFileSync(path.join(root, 'header.php'), 'utf8');
var blogFooterSource = fs.readFileSync(path.join(root, 'blog', 'wp-content', 'themes', 'startup-blog', 'footer.php'), 'utf8');
assert(headerSource.indexOf('notifications.js?v=20260812.2') !== -1, 'main-site header should load the updated shared notification runtime');
assert(blogFooterSource.indexOf('/assets/js/notifications.js?v=20260812.2') !== -1, 'WordPress should load the same updated notification runtime');
assert(blogFooterSource.indexOf("'assetBaseUrl' => $miq_blog_main_site_url . '/assets'") !== -1, 'WordPress should resolve shared invite styles from the main site');

console.log('notification-context.test.js passed');
