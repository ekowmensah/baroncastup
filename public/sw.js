// SmartCast Service Worker
const CACHE_NAME = 'smartcast-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Files to cache for offline functionality
const STATIC_CACHE_URLS = [
  '/',
  '/about',
  '/events',
  '/verify-receipt',
  '/public/css/app.css',
  '/public/css/admin.css',
  '/public/js/app.js',
  '/public/assets/images/favicon.svg',
  '/public/assets/images/icon-192.png',
  '/public/assets/images/icon-512.png',
  // Bootstrap and FontAwesome from CDN
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event - cache static resources
self.addEventListener('install', event => {
  console.log('SmartCast SW: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('SmartCast SW: Caching static resources');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('SmartCast SW: Installation complete');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('SmartCast SW: Installation failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('SmartCast SW: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('SmartCast SW: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('SmartCast SW: Activation complete');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', event => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Handle navigation requests
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.open(CACHE_NAME)
            .then(cache => {
              return cache.match(OFFLINE_URL);
            });
        })
    );
    return;
  }

  // Handle other requests with cache-first strategy
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }

        return fetch(event.request)
          .then(response => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response for caching
            const responseToCache = response.clone();

            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });

            return response;
          });
      })
  );
});

// Background sync for offline voting
self.addEventListener('sync', event => {
  if (event.tag === 'background-vote-sync') {
    console.log('SmartCast SW: Background sync triggered');
    event.waitUntil(syncOfflineVotes());
  }
});

// Push notifications for voting updates
self.addEventListener('push', event => {
  console.log('SmartCast SW: Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'New voting update available',
    icon: '/public/assets/images/icon-192.png',
    badge: '/public/assets/images/favicon.svg',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'View Events',
        icon: '/public/assets/images/icon-192.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/public/assets/images/icon-192.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('SmartCast', options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  console.log('SmartCast SW: Notification clicked');
  
  event.notification.close();

  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/events')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Sync offline votes when connection is restored
async function syncOfflineVotes() {
  try {
    const cache = await caches.open('offline-votes');
    const requests = await cache.keys();
    
    for (const request of requests) {
      try {
        const response = await fetch(request);
        if (response.ok) {
          await cache.delete(request);
          console.log('SmartCast SW: Offline vote synced successfully');
        }
      } catch (error) {
        console.error('SmartCast SW: Failed to sync offline vote', error);
      }
    }
  } catch (error) {
    console.error('SmartCast SW: Background sync failed', error);
  }
}

// Handle message from main thread
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
