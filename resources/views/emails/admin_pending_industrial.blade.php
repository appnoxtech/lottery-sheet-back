<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Received</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #fcfcfc;
            padding-bottom: 40px;
            padding-top: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            font-family: sans-serif;
            color: #1a1a1a;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e2c275;
            box-shadow: 0 20px 50px rgba(197, 160, 89, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%);
            padding: 50px 40px;
            text-align: center;
            border-bottom: 1px solid #f4e4bc;
        }
        .header h1 {
            color: #c5a059;
            margin: 0;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .content {
            padding: 40px;
            background-color: #ffffff;
        }
        .content h2 {
            margin-top: 0;
            font-size: 22px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 16px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.7;
            color: #4a4a4a;
            margin-bottom: 24px;
        }
        .footer {
            padding: 30px 40px;
            text-align: center;
            background-color: #ffffff;
            border-top: 1px solid #f4e4bc;
        }
        .footer p {
            font-size: 13px;
            color: #c5a059;
            margin: 0;
            font-weight: 600;
        }
        .gold-line {
            height: 4px;
            background: linear-gradient(90deg, #c5a059, #e2c275, #c5a059);
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr><td class="gold-line"></td></tr>
            <tr>
                <td class="header">
                    <h1>Registration Received</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello, {{ $user->name }}!</h2>
                    <p>Thank you for registering as an administrator. Your application has been received and is currently being reviewed by our team.</p>
                    <p>You will receive a follow-up email once your account has been approved and activated. This usually takes less than 24 hours.</p>
                    
                    <p style="margin-top: 40px; font-weight: 800; color: #c5a059; font-size: 18px;">
                        Best regards,<br>
                        The Team
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
