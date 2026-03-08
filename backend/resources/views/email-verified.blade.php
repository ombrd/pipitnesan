<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f8fafc; color: #0f172a; text-align: center; padding: 50px 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        h1 { color: #9333ea; font-size: 24px; margin-bottom: 16px; }
        p { font-size: 16px; color: #475569; margin-bottom: 24px; }
        .btn { display: inline-block; background: #9333ea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pipitnesan Gym</h1>
        <p>{{ $message ?? 'Your email has been successfully verified! You can now use the Pipitnesan App.' }}</p>
        <a href="/" class="btn">Return to App</a>
    </div>
</body>
</html>
