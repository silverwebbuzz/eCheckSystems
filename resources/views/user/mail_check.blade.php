<!DOCTYPE html>
<html>
<head>
    <title>SMTP Test Email</title>
</head>
<body>
    <h2>Send SMTP Test Email via PHPMailer</h2>

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('smtp_checker') }}">
        @csrf
        <label for="email">Recipient Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Test Email</button>
    </form>
</body>
</html>
