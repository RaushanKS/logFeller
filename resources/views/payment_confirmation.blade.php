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
            border-bottom: 2px solid #859061;
        }

        .email-body {
            padding: 20px;
            line-height: 1.6;
        }

        .email-footer {
            margin-top: 20px;
            text-align: center;
            color: #888;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .summary-table,
        .order-details,
        .invoice-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }

        .summary-table th,
        .summary-table td,
        .order-details th,
        .order-details td,
        .invoice-table th,
        .invoice-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .summary-table th,
        .order-details th,
        .invoice-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .order-details th {
            background-color: #859061;
            color: white;
            text-transform: uppercase;
        }

        .order-details td {
            background-color: #f9f9f9;
        }

        .highlight {
            color: #859061;
            font-weight: bold;
        }

        .invoice-table td:last-child {
            text-align: right;
        }

        .total-row th,
        .total-row td {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #859061;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <div class="email-header">
            <h1>Payment Confirmation</h1>
            <h2>Your Payment to {{ config('app.name') }}</h2>
        </div>
        <div class="email-body">
            <p>Dear {{ $name }},</p>

            <p>Thank you for using {{ config('app.name') }}. You have successfully made a payment of
                £{{ $amount }}.</p>

            <h3>Payment Summary</h3>
            <table class="summary-table">
                <tr>
                    <th>Payment Method</th>
                    <td>Card Payment</td>
                </tr>
                <tr>
                    <th>Amount Paid</th>
                    <td>£{{ $amount }}</td>
                </tr>
            </table>

            <h3>Order Details</h3>
            <table class="order-details">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Variation</th>
                        <th>Quantity</th>
                        <th>Sale Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->variation->name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>£{{ $item->sale_price }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Invoice section with totals -->
            @php
                $productTotal = 0;
                foreach ($orderItems as $item) {
                    $productTotal += $item->sale_price * $item->quantity;
                }
            @endphp
            <h3>Invoice Summary</h3>
            <table class="invoice-table">
                <tr>
                    <th>Product Total</th>
                    <td>£{{ $productTotal }}</td>
                </tr>
                <tr>
                    <th>Shipping Amount</th>
                    <td>£{{ $shippingAmount ?? 'N/A' }}</td>
                </tr>
                <tr class="total-row">
                    <th>Total Amount Paid</th>
                    <td>£{{ $amount }}</td>
                </tr>
            </table>

            <p>If you have any inquiries about your order, please contact us at <a
                    href="mailto:logfeller@gmail.com">logfeller@gmail.com</a> or call <a href="tel:+44 7989 906 043">+44
                    7989 906 043</a>.</p>
        </div>
        <div class="email-footer">
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
    </div>

</body>

</html>
