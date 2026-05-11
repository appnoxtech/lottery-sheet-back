<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lottery Request Processed</title>
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
        .details-box {
            background-color: #fdfbf7;
            border: 1px solid #f4e4bc;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f4e4bc;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .label {
            font-weight: 700;
            color: #8e6e37;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            width: 40%;
        }
        .value {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 15px;
            text-align: right;
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
                    <h1>Request Processed</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello, {{ $lotteryRequest->name }}!</h2>
                    <p>We are pleased to inform you that your lottery request has been successfully processed and accepted. Our team has reviewed your details and everything is in order.</p>
                    
                    <div class="details-box">
                        <table class="details-table">
                            <tr>
                                <td class="label">Lottery Type</td>
                                <td class="value">{{ $lotteryRequest->lottery_type }}</td>
                            </tr>
                            <tr>
                                <td class="label">Selected Lotteries</td>
                                <td class="value">{{ is_array($lotteryRequest->lottery_selections) ? implode(', ', $lotteryRequest->lottery_selections) : $lotteryRequest->lottery_selections }}</td>
                            </tr>
                            <tr>
                                <td class="label">Number Types</td>
                                <td class="value">{{ is_array($lotteryRequest->number_types) ? implode(', ', $lotteryRequest->number_types) : $lotteryRequest->number_types }}</td>
                            </tr>
                            <tr>
                                <td class="label">Your Numbers</td>
                                <td class="value">{{ is_array($lotteryRequest->lottery_numbers) ? implode(', ', $lotteryRequest->lottery_numbers) : $lotteryRequest->lottery_numbers }}</td>
                            </tr>
                        </table>
                    </div>

                    <p><strong>Payment Instructions:</strong> You will receive your ticket details and a Paysigur payment link via WhatsApp shortly. Please complete the payment to finalize your lottery entry.</p>

                    <p>Thank you for choosing us. We wish you the best of luck with your numbers!</p>
                    
                    <p style="margin-top: 40px; font-weight: 800; color: #c5a059; font-size: 18px;">
                        Thanks
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
