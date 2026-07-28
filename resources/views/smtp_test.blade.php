<!DOCTYPE html>
<html>
<head>
    <title>SMTP Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>SMTP Test with PHPMailer</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('smtp.test.send') }}">
            @csrf

            <div class="mb-3">
                <label>SMTP Host</label>
                <input type="text" name="host" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>SMTP Port</label>
                <input type="number" name="port" class="form-control" value="587" required>
            </div>

            <div class="mb-3">
                <label>SMTP Username (Email)</label>
                <input type="email" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>SMTP Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Encryption</label>
                <select name="encryption" class="form-control">
                    <option value="">None</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Send To Email</label>
                <input type="email" name="to" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email Subject</label>
                <input type="text" name="subject" class="form-control" value="SMTP Test Successful" required>
            </div>

            <div class="mb-3">
                <label>Email Body</label>
                <textarea name="body" class="form-control" rows="5" required>This is a test email sent using PHPMailer in Laravel.</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Test Email</button>
        </form>
    </div>
</body>
</html>
