<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Our Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            margin-top: 0;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            font-size: 14px;
            padding: 20px;
            color: #888;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}!</h1>
        </div>
        <div class="content">
            <h2>Hello 👋,</h2>
            <p>Thanks for joining our newsletter! We're excited to have you with us.</p>
            <p>Here's what you can expect in your inbox:</p>
            <ul>
                <li>🔔 Latest updates & news</li>
                <li>💡 Helpful tips and resources</li>
                <li>🎁 Exclusive deals and offers</li>
            </ul>
            <p>Stay tuned for some awesome content, straight to your inbox.</p>

            <a href="{{ config('app.url') }}" target="_blank" class="button">Visit Our Website</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
