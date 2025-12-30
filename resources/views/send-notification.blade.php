<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Send Push Notification</div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('send.notification') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="body" class="form-label">Message</label>
                                <textarea class="form-control" id="body" name="body" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="token" class="form-label">FCM Token</label>
                                <input type="text" class="form-control" id="token" name="token" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
