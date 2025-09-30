<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $mailData->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #4f46e5;
        }
        .body {
            margin: 20px 0;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: #4f46e5;
            color: #fff !important;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ $mailData->title }}</h2>
    </div>

    <div class="body">
        <p>{{ $mailData->body }}</p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="{{ $mailData->data['tokenUrl'] }}" class="button">
                Verify Account
            </a>
        </p>
    </div>

    <div class="footer">
        <p>If you did not create an account, please ignore this email.</p>
    </div>
</div>
</body>
</html>
