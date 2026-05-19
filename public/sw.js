const CACHE = 'delicon-v1';

self.addEventListener('install', e => {
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('push', e => {
    const d = e.data?.json() ?? {};
    e.waitUntil(
        self.registration.showNotification(d.title || 'デリヘルリスト', {
            body:  d.body  || '',
            icon:  '/pwa-icon-192.png',
            badge: '/favicon-32x32.png',
            data:  { url: d.url || '/' },
        })
    );
});

self.addEventListener('notificationclick', e => {
    e.notification.close();
    e.waitUntil(clients.openWindow(e.notification.data?.url || '/'));
});
