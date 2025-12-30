self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');
    console.log(`[Service Worker] Push had this data:`, event.data.json());

    const notificationData = event.data.json();
    const title = notificationData.notification?.title || 'New Notification';
    const options = {
        body: notificationData.notification?.body || '',
        icon: '/icon.png',
        data: notificationData.data || {}
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Add this to handle notification clicks
self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');
    event.notification.close();
    
    // This looks to see if the current is already open and focuses if it is
    event.waitUntil(
        clients.matchAll({type: 'window'}).then(windowClients => {
            for (let client of windowClients) {
                if (client.url === '/' && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow('/');
            }
        })
    );
});
