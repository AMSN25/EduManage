const CACHE_NAME = 'edumanage-v1';
const STATIC_CACHE = 'edumanage-static-v1';

const PRECACHE_URLS = [
    '/',
    '/manifest.json',
    '/icons/icon-192x192.svg',
    '/icons/icon-512x512.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME && name !== STATIC_CACHE)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip Livewire, API, and auth routes from caching
    if (url.pathname.startsWith('/livewire') ||
        url.pathname.startsWith('/api') ||
        url.pathname === '/login' ||
        url.pathname === '/register' ||
        url.pathname === '/logout') {
        return;
    }

    // Network-first for navigation (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/')))
        );
        return;
    }

    // Cache-first for static assets (CSS, JS, fonts, images)
    if (url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.woff2') ||
        url.pathname.endsWith('.woff') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.ico')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    const clone = response.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // Network-first for everything else
    event.respondWith(
        fetch(request)
            .then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Handle offline sync messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SYNC_OFFLINE_QUEUE') {
        event.waitUntil(syncOfflineQueue());
    }
});

async function syncOfflineQueue() {
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
        client.postMessage({ type: 'TRIGGER_SYNC' });
    });
}
