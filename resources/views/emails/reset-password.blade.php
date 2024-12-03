<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #007bff;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>
        <p>Hello,</p>
        <p>You requested a password reset. Use the following code to reset your password:</p>
        <div class="code">
            {{ $code }}
        </div>
        <p>If you didn't request this, please ignore this email.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Your Application</p>
        </div>
    </div>
</body>
</html>
