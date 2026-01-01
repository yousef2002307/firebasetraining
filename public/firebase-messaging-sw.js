// Import Firebase scripts in Service Worker
importScripts('https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.0/firebase-messaging-compat.js');

// Initialize Firebase in Service Worker
const firebaseConfig = {
    apiKey: "AIzaSyCgOAjsZZe2ykfrvFAoB3VOd8T4NyYwSYo",
    authDomain: "fir-training-e42c0.firebaseapp.com",
    databaseURL: "https://fir-training-e42c0-default-rtdb.firebaseio.com",
    projectId: "fir-training-e42c0",
    storageBucket: "fir-training-e42c0.firebasestorage.app",
    messagingSenderId: "983440483938",
    appId: "1:983440483938:web:2b05f5642f10392d67933b",
    measurementId: "G-LKPGMGBCMQ"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[Service Worker] Received background message ', payload);
    const notificationTitle = payload.notification?.title || 'New Notification';
    const notificationOptions = {
        body: payload.notification?.body || '',
        icon: '/icon.png',
        badge: '/icon.png',
        data: payload.data || {}
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');
    
    try {
        if (event.data) {
            const data = event.data.json();
            console.log(`[Service Worker] Push data:`, data);

            const title = data.notification?.title || 'New Notification';
            const options = {
                body: data.notification?.body || '',
                icon: '/icon.png',
                badge: '/icon.png',
                data: data.data || {}
            };

            event.waitUntil(
                self.registration.showNotification(title, options)
            );
        }
    } catch (error) {
        console.error('[Service Worker] Error handling push:', error);
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');
    event.notification.close();
    
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
