<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            background-color: #c5a059;
            color: #ffffff !important;
            padding: 18px 36px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.2);
        }
        .footer {
            padding: 30px 40px;
            text-align: center;
            background-color: #ffffff;
            border-top: 1px solid #f4e4bc;
        }
        .footer p {
            font-size: 12px;
            color: #c5a059;
            margin: 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .gold-line {
            height: 5px;
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
                    <h1>Reset Password</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Password Reset Request</h2>
                    <p>You are receiving this email because we received a password reset request for your account. If you did not make this request, no further action is required.</p>
                    
                    <div class="button-container">
                        <a href="{{ url(config('app.url') . '/admin/reset-password?token=' . $token) }}" class="button">Reset Password</a>
                    </div>

                    <p style="margin-top: 40px; font-size: 14px; color: #94a3b8; font-style: italic;">This password reset link will expire in 60 minutes.</p>
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
