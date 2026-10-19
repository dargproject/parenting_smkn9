const CACHE_NAME = 'siknine-guru-shell-v1';
const SHELL_ASSETS = [
  '/assets/img/icons/pwa-guru-192.png',
  '/assets/img/icons/pwa-guru-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

// Hanya aset ikon statis yang di-cache. Semua request lain (halaman, API,
// form login) selalu ke jaringan supaya CSRF token & data sesi tidak pernah basi.
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  if (event.request.method !== 'GET' || !SHELL_ASSETS.includes(url.pathname)) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => cached || fetch(event.request))
  );
});
