<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firebase Notification Receiver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Firebase Notifications</div>
                    <div class="card-body">
                        <div id="token-container" class="mb-4">
                            <!-- Token will be displayed here -->
                        </div>

                        <div class="alert alert-info mb-4">
                            <strong>User ID (Hardcoded):</strong> 1 
                            <br><small>Change the user ID in FirebaseController.php saveToken() method to test different users</small>
                        </div>
                            <h4>Received Notifications</h4>
                            <div id="notification-list" class="list-group">
                                <!-- Notifications will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Firebase SDKs -->

    <!-- Add Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-messaging-compat.js"></script>
    
    <script>
      // Your web app's Firebase configuration
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
// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
let messaging;

// Register service worker and initialize messaging
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then((registration) => {
            console.log('Service Worker registered with scope:', registration.scope);
            
            // Wait for service worker to be active and check controller
            const waitForActivation = setInterval(() => {
                if (navigator.serviceWorker.controller) {
                    clearInterval(waitForActivation);
                    console.log('Service Worker controller is active');
                    initializeMessaging();
                }
            }, 100);
            
            // Fallback timeout after 5 seconds
            setTimeout(() => {
                clearInterval(waitForActivation);
                if (!navigator.serviceWorker.controller) {
                    console.warn('Service Worker controller not ready, attempting anyway...');
                    initializeMessaging();
                }
            }, 5000);
            
            return navigator.serviceWorker.ready;
        })
        .catch((error) => {
            console.error('Service Worker registration failed:', error);
            const tokenContainer = document.getElementById('token-container');
            if (tokenContainer) {
                tokenContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Service Worker Error</strong>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        });
}

function initializeMessaging() {
    console.log('Initializing Firebase Messaging...');
    messaging = firebase.messaging();
    
    // Handle token refresh (check if function exists for browser compatibility)
    if (typeof messaging.onTokenRefresh === 'function') {
        messaging.onTokenRefresh(() => {
            messaging.getToken({vapidKey: 'BO4NV022Em7pDrb_2Gd5vkenK0JRNzdJ7IMTKxy78Tso8AQ0oG-MjW4WPKwmdq0uNNbFJ56_AVbCuIwJ9zMnQts'})
                .then((refreshedToken) => {
                    console.log('Token refreshed:', refreshedToken);
                    saveTokenToServer(refreshedToken);
                })
                .catch((err) => {
                    console.log('Unable to retrieve refreshed token', err);
                });
        });
    } else {
        console.log('onTokenRefresh not supported in this browser');
    }

    // Handle incoming messages
    messaging.onMessage((payload) => {
        console.log('✓ Message received in foreground:', payload);
        const notification = payload.notification;
        const notificationList = document.getElementById('notification-list');
        
        // Show browser notification popup
        if (Notification.permission === 'granted') {
            new Notification(notification.title || 'New Notification', {
                body: notification.body || '',
                icon: '/icon.png',
                badge: '/icon.png',
                tag: 'firebase-notification',
                requireInteraction: false
            });
        }
        
        // Also add to the list on page
        if (notificationList) {
            const notificationItem = document.createElement('div');
            notificationItem.className = 'list-group-item list-group-item-success';
            notificationItem.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${notification.title || 'No Title'}</h6>
                    <small class="text-success">✓ RECEIVED NOW</small>
                </div>
                <p class="mb-1">${notification.body || 'No content'}</p>
                <small>${new Date().toLocaleTimeString()}</small>
            `;
            notificationList.prepend(notificationItem);
        }
    });
    
    // Request notification permission
    requestPermission(messaging);
}

function requestPermission(messaging) {
    console.log('Requesting permission...');
    
    // Check if notifications are already denied
    if (Notification.permission === 'denied') {
        console.log('Notifications are blocked. Please enable in browser settings.');
        const tokenContainer = document.getElementById('token-container');
        if (tokenContainer) {
            tokenContainer.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Notifications are blocked!</strong>
                    <p>Please enable notifications in your browser settings and reload the page.</p>
                </div>
            `;
        }
        return;
    }
    
    // If already granted, get token directly
    if (Notification.permission === 'granted') {
        console.log('Notification permission already granted');
        getToken(messaging);
        return;
    }
    
    // Request permission
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log('Notification permission granted');
            getToken(messaging);
        } else {
            console.log('Unable to get permission to notify.');
            const tokenContainer = document.getElementById('token-container');
            if (tokenContainer) {
                tokenContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <strong>Permission Denied</strong>
                        <p>You rejected notification permission. Please reload and accept.</p>
                    </div>
                `;
            }
        }
    });
}

function getToken(messaging) {
    console.log('Attempting to get FCM token...');
    
    // Add a small delay to ensure service worker is ready (especially for Brave)
    const delay = navigator.brave ? 1000 : 100;
    
    setTimeout(() => {
        messaging.getToken({vapidKey: 'BO4NV022Em7pDrb_2Gd5vkenK0JRNzdJ7IMTKxy78Tso8AQ0oG-MjW4WPKwmdq0uNNbFJ56_AVbCuIwJ9zMnQts'})
        .then((currentToken) => {
            if (currentToken) {
                console.log('✓ FCM Token received:', currentToken);
                saveTokenToServer(currentToken);
                
                // Display token in UI
                const tokenContainer = document.getElementById('token-container');
                if (tokenContainer) {
                    tokenContainer.innerHTML = `
                        <div class="alert alert-success">
                            <h5>✓ Your FCM Token:</h5>
                            <div class="text-truncate">${currentToken}</div>
                            <button class="btn btn-sm btn-secondary mt-2" 
                                    onclick="navigator.clipboard.writeText('${currentToken}')">
                                Copy Token
                            </button>
                        </div>
                    `;
                }
            } else {
                console.log('No token available - retrying in 2 seconds...');
                // Retry after 2 seconds
                setTimeout(() => getToken(messaging), 2000);
            }
        }).catch((err) => {
            console.error('Error retrieving token:', err.code, err.message);
            const tokenContainer = document.getElementById('token-container');
            if (tokenContainer) {
                let errorMsg = err.message;
                let solution = 'Try refreshing the page or checking browser permissions.';
                
                if (err.code === 'messaging/failed-service-worker-registration') {
                    solution = 'Service Worker registration failed. Please reload the page.';
                } else if (err.message.includes('push service error')) {
                    solution = 'Push service error. Check Settings > Privacy > Site Settings > Notifications and make sure notifications are allowed.';
                } else if (err.code === 'messaging/permission-blocked') {
                    solution = 'Notifications are blocked. Please enable in browser settings.';
                }
                
                tokenContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${errorMsg}
                        <br><small>${solution}</small>
                        <br><button class="btn btn-sm btn-warning mt-2" onclick="location.reload()">Reload Page</button>
                    </div>
                `;
            }
            
            // Retry after 3 seconds
            console.log('Retrying token retrieval in 3 seconds...');
            setTimeout(() => getToken(messaging), 3000);
        });
    }, delay);
}

function saveTokenToServer(token) {
    fetch('/save-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({token: token})
    })
    .then(response => response.json())
    .then(data => console.log('Token saved:', data))
    .catch(error => console.error('Error saving token:', error));
}
    </script>
   
</body>
</html>

