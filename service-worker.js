// service-worker.js (na mesma pasta que Index.php)
const CACHE_NAME = 'kr-legends-cache-v1';
const STATIC_ASSETS = [
  './',             // raiz (Index)
  'Index.php',
  'manifest.json',
  // inclua os ícones que existirem; se não existir, será ignorado sem quebrar
  'Imagens/Logotipos/Logo_KR_Legends.png',
  'Imagens/Logotipos/Logo_KR_Legends.png'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    for (const url of STATIC_ASSETS) {
      try {
        // tentativa de cache; se 404/erro -> apenas logamos e continuamos
        const res = await fetch(url, {cache: 'no-store'});
        if (!res.ok) throw new Error('HTTP ' + res.status);
        await cache.put(url, res.clone());
        console.log('[SW] Cached', url);
      } catch (err) {
        console.warn('[SW] Could not cache', url, err);
      }
    }
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)));
    self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Apenas lidar com mesmo origin
  if (url.origin !== self.location.origin) return;

  // HTML (páginas) -> network-first (garante conteúdo atualizado)
  if (req.headers.get('accept') && req.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(req).then(res => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
        return res;
      }).catch(() => caches.match(req).then(r => r || caches.match('./')))
    );
    return;
  }

  // Assets (css, js, imagens) -> cache-first, fallback para rede
  event.respondWith(
    caches.match(req).then(cached => {
      if (cached) return cached;
      return fetch(req).then(res => {
        if (res && res.status === 200 && req.method === 'GET') {
          const copy = res.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
        }
        return res;
      }).catch(() => { /* se falhar, resolve com undefined */ })
    })
  );
});
