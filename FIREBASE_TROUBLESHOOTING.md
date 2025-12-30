# Firebase Notification Troubleshooting Guide

## Error: "Auth error from APNS or Web Push Service"

This error occurs when Firebase Cloud Messaging cannot authenticate with the push notification service. Here are the solutions:

### 1. **Verify Firebase Credentials** ✓
- Ensure the service account JSON file exists at:
  ```
  storage/app/firebase/fir-training-e42c0-firebase-adminsdk-fbsvc-ed772f4540.json
  ```
- The file should contain valid JSON with these fields:
  - `type`: "service_account"
  - `project_id`: "fir-training-e42c0"
  - `private_key`: Valid RSA private key
  - `client_email`: Valid service account email

### 2. **Enable Web Push Service in Firebase Console**
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (fir-training-e42c0)
3. Go to **Cloud Messaging** (formerly Firebase Cloud Messaging)
4. Ensure the service is enabled

### 3. **Get Valid FCM Token**
⚠️ **Important**: A manually typed token won't work! You must generate it from the browser:

1. Visit: `http://yourapp.test/notification`
2. Wait for browser notification permission popup
3. Click **Allow**
4. Copy the generated FCM Token (it will appear in the "Your FCM Token" box)
5. Use this token in the send notification form at `/send-notification`

**Token Format**: Valid FCM tokens are typically 150+ characters long:
```
e_6jEr...8KsyFw1z_something_like_this_but_much_longer
```

### 4. **Common Causes & Solutions**

| Error | Cause | Solution |
|-------|-------|----------|
| "Auth error from APNS or Web Push" | Invalid/expired credentials | Re-download service account JSON from Firebase Console |
| "Registration token is invalid" | Malformed or short token | Generate new token using notification-demo page |
| "Topic name is invalid" | Token format issue | Ensure full token is copied (not truncated) |
| "Mismatched token and credential" | Token from different project | Regenerate token from correct browser session |

### 5. **Step-by-Step Testing**

#### Step 1: Test Firebase Connection
```
http://yourapp.test/firebase-test
```
Should return: "Firebase Connected and Test Data Added!"

#### Step 2: Get FCM Token
1. Visit `http://yourapp.test/notification`
2. Grant notification permission when prompted
3. Copy the generated token

#### Step 3: Send Test Notification
1. Visit `http://yourapp.test/send-notification`
2. Fill in:
   - **Title**: "Test"
   - **Body**: "This is a test"
   - **FCM Token**: Paste the token from Step 2
3. Click "Send Notification"

### 6. **Check Logs**

If notification fails, check Laravel logs:
```
storage/logs/laravel.log
```

Look for entries like:
```
[FCM Send Error] message: "..." code: 401 file: "..."
```

### 7. **Firebase Console Checks**

In Firebase Console:
1. **Cloud Messaging** → Verify service is active
2. **Service Accounts** → Verify service account exists and has "Editor" role
3. **Project Settings** → Verify Web API Key is set

### 8. **VAPID Key Configuration**

The VAPID key in `notification-demo.blade.php` is already configured:
```javascript
vapidKey: 'BO4NV022Em7pDrb_2Gd5vkenK0JRNzdJ7IMTKxy78Tso8AQ0oG-MjW4WPKwmdq0uNNbFJ56_AVbCuIwJ9zMnQts'
```

To get your own VAPID key from Firebase Console:
1. Go to **Project Settings** → **Cloud Messaging**
2. Under "Web Push certificates", click "Generate Key Pair"
3. Copy the Public Key and update both:
   - `resources/views/notification-demo.blade.php`
   - Client-side JavaScript (vapidKey)
   - Laravel doesn't need it server-side, but the credentials JSON must be valid

### 9. **Network & Security**

- Ensure service worker file exists at `/public/firebase-messaging-sw.js`
- Service worker should be accessible without authentication
- Check browser console (F12) for any JavaScript errors
- Verify HTTPS is not required locally (HTTP should work in development)

### 10. **If All Else Fails**

1. **Clear service worker cache**:
   - In browser: DevTools → Application → Service Workers → Unregister
   - Clear Site Data

2. **Regenerate credentials**:
   - Firebase Console → Project Settings → Service Accounts
   - Delete old key
   - Generate new service account key
   - Replace JSON file

3. **Re-initialize Firebase**:
   - Delete `storage/app/firebase/*`
   - Download new credentials from Firebase Console
   - Restart Laravel

---

**Quick Reference URLs**:
- Notification Receiver: `http://yourapp.test/notification`
- Send Notifications: `http://yourapp.test/send-notification`
- Test Connection: `http://yourapp.test/firebase-test`
