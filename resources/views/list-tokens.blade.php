<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Tokens List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4>User FCM Tokens (Debug Page)</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This page shows all users and their stored FCM tokens. If a user has no tokens, it means they haven't opened the notification receiver page yet.</p>
                        
                        @if($users->isEmpty())
                            <div class="alert alert-info">
                                No users found in the database.
                            </div>
                        @else
                            @foreach($users as $user)
                                <div class="mb-4 p-3 border rounded">
                                    <h5>User ID: <strong>{{ $user->id }}</strong> - {{ $user->name }} ({{ $user->email }})</h5>
                                    
                                    @if($user->fcmTokens->isEmpty())
                                        <div class="alert alert-warning mb-0">
                                            ⚠️ No FCM tokens for this user yet.
                                            <br><small>Open the <a href="{{ route('notification') }}">notification receiver page</a> with this user ID to generate tokens.</small>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            ✓ {{ $user->fcmTokens->count() }} token(s) found
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Token</th>
                                                        <th>Created</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($user->fcmTokens as $token)
                                                        <tr>
                                                            <td>
                                                                <code class="text-truncate" style="max-width: 400px;">{{ substr($token->token, 0, 50) }}...</code>
                                                            </td>
                                                            <td>{{ $token->created_at->format('Y-m-d H:i:s') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <hr>
                        <h5>How to Fix Missing Tokens:</h5>
                        <ol>
                            <li>Open <a href="{{ route('notification') }}">notification receiver page</a></li>
                            <li>Check which User ID is showing (it should match the browser you're testing with)</li>
                            <li>If you want to test User ID 2:
                                <ul>
                                    <li>Edit <code>app/Http/Controllers/FirebaseController.php</code></li>
                                    <li>Change <code>$userId = 1;</code> to <code>$userId = 2;</code> in the <code>saveToken()</code> method</li>
                                    <li>Reload this page to confirm the token was saved</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="mt-4">
                            <a href="{{ route('notification.form') }}" class="btn btn-primary">Go to Send Notification</a>
                            <a href="{{ route('notification') }}" class="btn btn-secondary">Go to Notification Receiver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
