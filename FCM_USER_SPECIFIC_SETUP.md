# User-Specific Firebase Notifications Setup

## What Changed

Your Firebase notification system now supports **user-specific notifications**. Each user has their own FCM tokens stored in the database, and you can send notifications to specific users.

## How It Works

### 1. **Database Storage**
- Created a new `fcm_tokens` table that stores FCM tokens associated with user IDs
- Each token is unique per user
- One user can have multiple tokens (different devices)

### 2. **Hardcoded User ID**
- The notification receiver page is hardcoded to save tokens for **User ID: 1**
- Change this manually in the code to test with different users

### 3. **How to Use**

#### **Step 1: Receive Notifications (Notification Page)**
```
URL: http://yourapp.test/notification
```
- Open this page in a browser
- The page will request notification permission
- Your FCM token will be automatically saved to **User ID: 1** in the database
- The token and user ID will be displayed on the page

#### **Step 2: Send Notifications (Send Page)**
```
URL: http://yourapp.test/send-notification
```
- Enter the **User ID** (e.g., 1, 2, 3, etc.)
- Enter the notification **Title** and **Message**
- Click "Send Notification"
- The notification will be sent to **all devices** of that user

## Testing with Multiple Users

### To test with different users:

1. **For User 1:**
   - No changes needed, use as-is
   - Open `/notification` page (tokens saved for user 1)
   - Send notifications to user 1 from `/send-notification`

2. **For User 2:**
   - Edit `app/Http/Controllers/FirebaseController.php`
   - In the `saveToken()` method, change:
     ```php
     $userId = 1;  // Change to: $userId = 2;
     ```
   - Open `/notification` page (tokens saved for user 2)
   - Send notifications to user 2 from `/send-notification`

3. **For User 3, 4, etc:**
   - Repeat the same process

## Files Modified

1. **Database Migration**: `database/migrations/2026_01_01_000003_create_fcm_tokens_table.php`
   - Creates the `fcm_tokens` table

2. **FirebaseController**: `app/Http/Controllers/FirebaseController.php`
   - `saveToken()`: Now stores token with user ID in database
   - `sendNotification()`: Now sends to user's stored tokens instead of raw token input

3. **User Model**: `app/Models/User.php`
   - Added `fcmTokens()` relationship

4. **FcmToken Model**: `app/Models/FcmToken.php`
   - New model for managing FCM tokens

5. **Views**:
   - `notification-demo.blade.php`: Shows hardcoded User ID
   - `send-notification.blade.php`: Changed from FCM token input to User ID input

## Important Notes

- ✅ **No authentication needed** - User IDs are hardcoded
- ✅ **Multiple devices**: One user can receive notifications on multiple devices
- ✅ **Easy to test**: Just change the user ID to test different users
- ✅ **Database stored**: Tokens persist across page reloads

## Next Steps (Optional)

When you're ready to implement real authentication, you can:
- Replace `$userId = 1;` with `$userId = auth()->id();`
- Add proper user authentication middleware
- The rest of the code will work as-is!

## Run Migration

Before testing, run the migration to create the table:
```bash
php artisan migrate
```
