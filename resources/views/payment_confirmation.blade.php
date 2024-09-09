<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .email-body {
            padding: 20px;
            line-height: 1.6;
        }
        .email-footer {
            margin-top: 20px;
            text-align: center;
            color: #888;
        }
        .summary-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .summary-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Your Payment to {{ config('app.name') }}</h1>
        </div>
        <div class="email-body">
            <p>Dear {{ $name }},</p>

            <p>Thank you for using {{ config('app.name') }}. You have successfully made a payment of £{{ $amount }}.</p>

            <h3>PAYMENT SUMMARY</h3>
            <table class="summary-table">
                <tr>
                    <th>Payment Method</th>
                    <td>Card Payment</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>£{{ $amount }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>£{{ $amount }}</td>
                </tr>
            </table>

            <p>For order inquiries, please contact us at <a href="mailto:logfeller@gmail.com">logfeller@gmail.com</a> or call +44 7989 906 043.</p>
        </div>
        <div class="email-footer">
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
