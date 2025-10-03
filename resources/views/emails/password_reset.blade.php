<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $mailData->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #004aad;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
            font-size: 16px;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            margin: 20px 0;
            padding: 12px 20px;
            background: #004aad;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #888888;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        {{ $mailData->title }}
    </div>
    <div class="content">
        <p>{{ $mailData->body }}</p>

        <p style="text-align:center;">
            <a href="{{ $mailData->data['tokenURL'] ?? '#' }}" class="btn">
                Reset Password
            </a>
        </p>

        <p>If the button above doesn’t work, copy and paste this link into your browser:</p>
        <p>
            <a href="{{ $mailData->data['tokenURL'] ?? '#' }}">
                {{ $mailData->data['tokenURL'] ?? '' }}
            </a>
        </p>

        <p>If you did not request a password reset, you can safely ignore this email.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} YourAppName. All rights reserved.
    </div>
</div>
</body>
</html>
