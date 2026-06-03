const CACHE_NAME = 'monkouadjo-v1';

// Stratégie : cache-first pour les assets Vite (hachés, immuables)
//             network-first pour les pages HTML (Inertia shell)
const ASSET_PATTERN = /\/build\/assets\//;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) =>
            cache.addAll(['/dashboard'])
        ).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorer les requêtes non-GET et les API Inertia (X-Inertia header)
    if (request.method !== 'GET') return;
    if (request.headers.get('X-Inertia')) return;

    // Assets Vite hachés → cache first
    if (ASSET_PATTERN.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(c => c.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Requêtes de navigation HTML → network first, cache shell en fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(c => c.put(request, clone));
                    }
                    return response;
                })
                .catch(() => caches.match(request).then(c => c || caches.match('/dashboard')))
        );
    }
});
