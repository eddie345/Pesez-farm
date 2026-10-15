// ============================================================
// Pesez Farm Management System — Service Worker
// Cache-first strategy for UI shell assets (Tailwind CDN,
// Lucide icons, Chart.js, Google Fonts) plus full offline
// fallback for navigation requests.
// ============================================================

const CACHE_NAME    = 'pesez-farm-shell-v1';
const OFFLINE_PAGE  = '/hmm/offline.html'; // optional offline fallback page

// Assets to pre-cache on install
const PRECACHE_URLS = [
    // CDN assets actually used by the app (adjust paths to match your setup)
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap',
    // App shell pages (relative to the SW scope)
    '/hmm/dashboard.php',
    '/hmm/eggs.php',
    '/hmm/feed.php',
    '/hmm/mortality.php',
    '/hmm/health.php',
    '/hmm/reports.php',
    '/hmm/logs.php',
];

// ── Install: pre-cache shell assets ────────────────────────────────────────
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // addAll will fail silently for CDN URLs that may have CORS limits;
            // we use individual add() calls wrapped in try/catch for resilience.
            return Promise.allSettled(PRECACHE_URLS.map(url => cache.add(url).catch(() => {})));
        })
    );
});

// ── Activate: purge old caches ─────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// ── Fetch: cache-first for same-origin, network-first for navigation ────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin POST (form submissions must reach server)
    if (request.method !== 'GET') return;

    // Cache-first strategy
    event.respondWith(
        caches.match(request).then(cached => {
            if (cached) {
                // Serve from cache, then update in background (stale-while-revalidate)
                const networkFetch = fetch(request).then(response => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => {}); // Network failure — ignore, already served cached

                return cached;
            }

            // Not cached — try network
            return fetch(request).then(response => {
                if (!response || response.status !== 200 || response.type === 'opaque') {
                    return response;
                }
                // Cache fresh responses
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                return response;
            }).catch(() => {
                // Full offline fallback for navigation requests
                if (request.mode === 'navigate') {
                    return caches.match(OFFLINE_PAGE);
                }
            });
        })
    );
});
