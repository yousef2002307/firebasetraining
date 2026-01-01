<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
class FirebaseController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase->getDatabase();
           $this->messaging = $firebase->getMessaging();
    }
    



    public function test()
    {
        $this->firebase->getReference("testing")
            ->set([
                'message' => 'Firebase Integration Successful!'
            ]);

        return 'Firebase Connected and Test Data Added!';
    }

    public function notificationPage()
    {
        return view('notification-demo');
    }

public function saveToken(Request $request)
{
    // Hardcoded user ID for now - change this manually as needed
    $userId = 2; // Change this to test different users (e.g., 1, 2, 3, etc.)
    
    $token = $request->token;
    
    // Check if this token already exists for this user
    $exists = \App\Models\FcmToken::where('user_id', $userId)
        ->where('token', $token)
        ->exists();
    
    if (!$exists) {
        // Store new token in database associated with user
        \App\Models\FcmToken::create([
            'user_id' => $userId,
            'token' => $token
        ]);
    }
    
    return response()->json(['message' => 'Token saved successfully.', 'user_id' => $userId]);
}


    public function showSendForm()
    {
        return view('send-notification');
    }

    public function listUserTokens()
    {
        $users = \App\Models\User::with('fcmTokens')->get();
        return view('list-tokens', ['users' => $users]);
    }

    public function sendNotification(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'user_id' => 'required|integer'
    ]);

    try {
        // Get user's FCM tokens
        $user = \App\Models\User::find($request->user_id);
        
        if (!$user) {
            return back()->with('error', 'User not found.');
        }
        
        $tokens = $user->fcmTokens()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return back()->with('error', 'No FCM tokens found for this user. Please open the notification receiver page first.');
        }

        \Log::info('Sending notification to user ' . $request->user_id, [
            'token_count' => count($tokens),
            'title' => $request->title,
            'body' => $request->body
        ]);

        $successCount = 0;
        $failureMessages = [];

        foreach ($tokens as $token) {
            try {
                \Log::info('Attempting to send to token: ' . substr($token, 0, 50) . '...');
                
                $message = CloudMessage::fromArray([
                    'token' => $token,
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->body
                    ],
                    'webpush' => [
                        'fcmOptions' => [
                            'link' => url('/')
                        ]
                    ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]
                ]);

                $this->messaging->send($message);
                $successCount++;
                \Log::info('Successfully sent notification');
            } catch (\Exception $e) {
                \Log::error('Failed to send to token', [
                    'token' => substr($token, 0, 50) . '...',
                    'error' => $e->getMessage()
                ]);
                $failureMessages[] = $e->getMessage();
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "Notification sent to $successCount device(s) for user ID {$request->user_id}!");
        } else {
            return back()->with('error', 'Failed to send notifications: ' . implode(' | ', $failureMessages));
        }
    } catch (\Exception $e) {
        \Log::error('FCM Send Error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
    }
}

}

