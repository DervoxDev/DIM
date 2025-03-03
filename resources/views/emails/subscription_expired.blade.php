<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #5865F2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #4752C4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .notice {
            background-color: #fff8f8;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subscription Expired</h1>
        
        <div class="notice">
            <p>Dear {{ $teamName }},</p>
            <p>Your subscription has expired on {{ $expirationDate }}.</p>
            <p>To continue using our services, please renew your subscription immediately.</p>
        </div>

        <center>
            <a href="https://dim.dervox.com/dashboard" class="button">
                Renew Now
            </a>
        </center>

        <p style="margin-top: 30px; font-size: 14px; color: #666;">
            If you have any questions, please don't hesitate to contact our support team.
        </p>

        <p style="font-size: 12px; color: #999; margin-top: 40px;">
            This is an automated message, please do not reply to this email.
        </p>
    </div>
</body>
</html>
