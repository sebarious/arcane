// Minimal service worker for the kiosk PWA — its only job is satisfying
// Chrome's installability requirement (a registered SW with a fetch handler)
// so the tablet can "Add to Home Screen" and launch full-screen. Deliberately
// does no caching: search, basket, and payment all need a live connection
// anyway, so there's no useful offline mode to build here.
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', () => {
  // No-op — every request goes to the network as normal.
});
