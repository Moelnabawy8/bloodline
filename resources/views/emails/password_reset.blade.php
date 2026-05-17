<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:30px;">
    <div style="max-width:520px;margin:0 auto;background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <h2 style="color:#8B0000;">BloodLine — Password Reset</h2>
        <p>We received a request to reset your password. Click the button below to set a new one. This link expires in 1 hour.</p>
        <p style="text-align:center;margin:30px 0;">
            <a href="{{ $resetUrl }}" style="background:#8B0000;color:#fff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:bold;">Reset Password</a>
        </p>
        <p style="color:#777;font-size:13px;">If you didn't request this, you can safely ignore this email.</p>
    </div>
</body>
</html>
