<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Lottery Request</title>
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
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            width: 40%;
        }
        .value {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 14px;
            text-align: right;
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
                    <h1>New Request</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Admin Notification</h2>
                    <p>A new lottery request has been submitted. Please review the details below in the admin dashboard.</p>
                    
                    <div class="details-box">
                        <table class="details-table">
                            <tr>
                                <td class="label">Customer Name</td>
                                <td class="value">{{ $lotteryRequest->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Email Address</td>
                                <td class="value">{{ $lotteryRequest->email }}</td>
                            </tr>
                            <tr>
                                <td class="label">Phone Number</td>
                                <td class="value">{{ $lotteryRequest->country_code }} {{ $lotteryRequest->phone }}</td>
                            </tr>
                            <tr>
                                <td class="label">Lottery Type</td>
                                <td class="value">{{ $lotteryRequest->lottery_type }}</td>
                            </tr>
                            <tr>
                                <td class="label">Numbers</td>
                                <td class="value">{{ is_array($lotteryRequest->lottery_numbers) ? implode(', ', $lotteryRequest->lottery_numbers) : $lotteryRequest->lottery_numbers }}</td>
                            </tr>
                            <tr>
                                <td class="label">Amount Paid</td>
                                <td class="value">{{ $lotteryRequest->currency }}{{ number_format($lotteryRequest->amount, 2) }}</td>
                            </tr>
                            @if($lotteryRequest->notes)
                            <tr>
                                <td class="label">Notes</td>
                                <td class="value">{{ $lotteryRequest->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <p>Log in to the dashboard to process this request.</p>
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
