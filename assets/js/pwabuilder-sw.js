// This is the "Offline page" service worker

importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js');

const CACHE = "pwabuilder-page";

// TODO: replace the following with the correct offline fallback page i.e.: const offlineFallbackPage = "offline.html";
const offlineFallbackPage = "ToDo-replace-this-name.html";

self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

self.addEventListener('install', async (event) => {
  event.waitUntil(
    caches.open(CACHE)
      .then((cache) => cache.add(offlineFallbackPage))
  );
});

if (workbox.navigationPreload.isSupported()) {
  workbox.navigationPreload.enable();
}

self.addEventListener('fetch', (event) => {
  if (event.request.mode === 'navigate') {
    event.respondWith((async () => {
      try {
        const preloadResp = await event.preloadResponse;

        if (preloadResp) {
          return preloadResp;
        }

        const networkResp = await fetch(event.request);
        return networkResp;
      } catch (error) {

        const cache = await caches.open(CACHE);
        const cachedResp = await cache.match(offlineFallbackPage);
        return cachedResp;
      }
    })());
  }
});

self.addEventListener('push', (event) => {
  if (!event.data) {
    return;
  }

  event.waitUntil((async () => {
    let payload = {};
    try {
      payload = event.data.json();
    } catch (error) {
      payload = { notification: { body: event.data.text() } };
    }

    const notification = payload.notification || {};
    const data = payload.data || {};
    const scope = self.registration.scope || self.location.origin + '/';
    let url = data.link_url || data.url || payload.fcm_options && payload.fcm_options.link || 'workspace?tab=notifications';
    try {
      url = new URL(url, scope).toString();
      if (new URL(url).origin !== self.location.origin) {
        url = new URL('workspace?tab=notifications', scope).toString();
      }
    } catch (error) {
      url = new URL('workspace?tab=notifications', scope).toString();
    }

    const icon = new URL('assets/img/360Logo_192.png', scope).toString();
    await self.registration.showNotification(
      notification.title || payload.title || '360MiQ notification',
      {
        body: notification.body || payload.body || 'You have a new notification.',
        icon: notification.icon || icon,
        badge: notification.badge || icon,
        tag: notification.tag || 'miq-notification-' + (data.notification_id || Date.now()),
        data: { url: url }
        }
    );
    const openClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });
    openClients.forEach((client) => {
      client.postMessage({ type: 'miq-notification', data: data });
    });
  })());
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const fallback = new URL('workspace?tab=notifications', self.registration.scope || self.location.origin + '/').toString();
  let url = event.notification.data && event.notification.data.url ? event.notification.data.url : fallback;
  try {
    const parsed = new URL(url, self.registration.scope || self.location.origin + '/');
    url = parsed.origin === self.location.origin ? parsed.toString() : fallback;
  } catch (error) {
    url = fallback;
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url === url || client.url.indexOf(url) === 0) {
          return client.focus();
        }
        if ('navigate' in client) {
          return client.navigate(url).then(() => client.focus());
        }
      }
      return clients.openWindow(url);
    })
  );
});
