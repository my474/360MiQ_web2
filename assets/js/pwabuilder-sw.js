/* 360MiQ root service worker: resilient FCM background delivery.
 *
 * There is intentionally no install-time network dependency. Optional offline
 * content must never prevent the notification worker from activating.
 */
(function () {
  'use strict';

  var LEGACY_CACHE = 'pwabuilder-page';

  function scopeUrl(path) {
    var scope = self.registration && self.registration.scope
      ? self.registration.scope
      : self.location.origin + '/';
    return new URL(path, scope);
  }

  function safeNotificationUrl(value) {
    var fallback = scopeUrl('workspace?tab=notifications');
    try {
      var parsed = new URL(value || fallback.toString(), scopeUrl('/').toString());
      return parsed.origin === self.location.origin && !parsed.username && !parsed.password
        ? parsed.toString()
        : fallback.toString();
    } catch (error) {
      return fallback.toString();
    }
  }

  function parsePushPayload(event) {
    if (!event.data) return {};
    try {
      return event.data.json() || {};
    } catch (error) {
      return { notification: { body: event.data.text() } };
    }
  }

  self.addEventListener('install', function (event) {
    event.waitUntil(Promise.resolve(self.skipWaiting()));
  });

  self.addEventListener('activate', function (event) {
    event.waitUntil(Promise.all([
      self.clients.claim(),
      typeof caches !== 'undefined' ? caches.delete(LEGACY_CACHE) : Promise.resolve(false)
    ]));
  });

  self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
  });

  self.addEventListener('push', function (event) {
    if (!event.data) return;
    var payload = parsePushPayload(event);
    var notification = payload.notification || {};
    var data = payload.data || {};
    var webpush = payload.webpush || {};
    var webNotification = webpush.notification || {};
    var options = payload.fcm_options || payload.fcmOptions || webpush.fcm_options || {};
    var url = safeNotificationUrl(
      data.link_url || data.url || webNotification.data && webNotification.data.url || options.link
    );
    var icon = scopeUrl('assets/img/360Logo_192.png').toString();
    var title = notification.title || webNotification.title || payload.title || '360MiQ notification';
    var unread = Math.max(0, parseInt(data.unread_count, 10) || 0);

    event.waitUntil(Promise.all([
      self.registration.showNotification(title, {
        body: notification.body || webNotification.body || payload.body || 'You have a new notification.',
        icon: notification.icon || webNotification.icon || icon,
        badge: notification.badge || webNotification.badge || icon,
        tag: notification.tag || webNotification.tag || 'miq-notification-' + (data.notification_id || Date.now()),
        data: {
          url: url,
          notification_id: String(data.notification_id || ''),
          unread_count: unread
        }
      }),
      self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (openClients) {
        openClients.forEach(function (client) {
          try { client.postMessage({ type: 'miq-notification', data: data }); } catch (error) {}
        });
      })
    ]));
  });

  self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = safeNotificationUrl(event.notification.data && event.notification.data.url);
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      var exact = clientList.find(function (client) { return client.url === url; });
      if (exact) {
        return Promise.resolve(exact.focus()).catch(function () { return self.clients.openWindow(url); });
      }
      var sameOrigin = clientList.find(function (client) {
        try { return new URL(client.url).origin === self.location.origin; } catch (error) { return false; }
      });
      if (sameOrigin && typeof sameOrigin.navigate === 'function') {
        return Promise.resolve(sameOrigin.navigate(url)).then(function (navigated) {
          return navigated && typeof navigated.focus === 'function'
            ? navigated.focus()
            : self.clients.openWindow(url);
        }).catch(function () { return self.clients.openWindow(url); });
      }
      return self.clients.openWindow(url);
    }));
  });
}());
