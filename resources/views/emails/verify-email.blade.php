<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;">
        <h2 style="color:#0057b8;">Verify Your Email</h2>
        <p style="color:#555;">Hi {{ $user->Full_Name }},</p>
        <p style="color:#555;">Thank you for registering on the Skill Matching System. Please verify your email address by clicking the button below:</p>
        <p style="margin:20px 0;">
            <a href="{{ url('/verify-email/' . $token . '/' . $user->User_ID) }}" style="display:inline-block;padding:12px 24px;background:#0057b8;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">Verify My Email</a>
        </p>
        <p style="color:#888;font-size:0.85rem;">If you did not create an account, you can ignore this email.</p>
        <p style="color:#888;font-size:0.85rem;">This link will expire when you verify your email or log in.</p>
    </div>
</body>
</html>
