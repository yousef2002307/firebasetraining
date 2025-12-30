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
    // In a real app, you would associate this token with the authenticated user
    // For this example, we'll just store it in the session
    $request->session()->put('fcm_token', $request->token);
    return response()->json(['message' => 'Token saved successfully.']);
}


  public function showSendForm()
    {
        return view('send-notification');
    }

    public function sendNotification(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'token' => 'required|string'
    ]);

    try {
        // Validate token format (FCM tokens are typically 150+ characters)
        $token = trim($request->token);
        if (strlen($token) < 100) {
            return back()->with('error', 'Invalid FCM token format. Token should be longer. Make sure you copied the full token from the notification receiver page.');
        }

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

        return back()->with('success', 'Notification sent successfully!');
    } catch (\Exception $e) {
        \Log::error('FCM Send Error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'Auth error') !== false) {
            $errorMsg = 'Authentication error: Check that Firebase credentials are valid and Web Push service is enabled in Firebase Console.';
        }
        
        return back()->with('error', 'Failed to send notification: ' . $errorMsg);
    }
}

}

