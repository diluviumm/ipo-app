const C = 'ipo-v2';
const AWAL = ['/login', '/app.css', '/manifest.webmanifest', '/favicon.svg', '/icons/icon-192.png', '/icons/apple-touch-icon.png'];
const STATIS = ['/app.css', '/manifest.webmanifest', '/favicon.svg', '/icons/', '/vendor/chart.umd.js'];
function bolehCache(url) {
  if (url.origin !== location.origin) return false;
  const p = url.pathname;
  if (p === '/login') return true;
  return STATIS.some((a) => p === a || p.startsWith(a));
}
self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(C).then((c) => c.addAll(AWAL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((ks) => Promise.all(ks.filter((k) => k !== C).map((k) => caches.delete(k)))).then(() => self.clients.claim())
  );
});
self.addEventListener('fetch', (e) => {
  if (e.request.method !== 'GET') return;
  const url = new URL(e.request.url);
  if (!bolehCache(url)) return;
  e.respondWith(
    fetch(e.request).then((r) => {
      const k = r.clone();
      caches.open(C).then((c) => c.put(e.request, k));
      return r;
    }).catch(() => caches.match(e.request).then((m) => m || caches.match('/login')))
  );
});
