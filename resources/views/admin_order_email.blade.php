<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .email-container {
            width: 100%;
            max-width: 700px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        h2 {
            color: #859061;
            font-size: 24px;
            margin-bottom: 5px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: -12px;
        }

        h4 {
            color: #333;
            font-size: 18px;
            margin-top: 20px;
            border-bottom: 2px solid #859061;
            padding-bottom: 5px;
        }

        .order-details {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e0e0e0;
        }

        .order-details th,
        .order-details td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-details th {
            background-color: #859061;
            color: white;
            text-transform: uppercase;
        }

        .order-details td {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #888;
            font-size: 14px;
        }

        .highlight {
            color: #859061;
            font-weight: bold;
        }

        .cta-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background-color: #859061;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
        }

        .cta-button:hover {
            background-color: #45a049;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-top: -25px;
        }

        .col-6 {
            width: 48%;
        }

        .col-6 p {
            margin: 5px 0;
        }

        .total-row th,
        .total-row td {
            font-weight: bold;
            color: #333;
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h2>Order Confirmation</h2>
        <p>Hello Admin,</p>
        <p>You have received a new order. Please find the details below:</p>

        <h4>User Details</h4>
        <p><span class="highlight">Name:</span> {{ $nameee }}</p>
        <p><span class="highlight">Email:</span> {{ $emailll }}</p>
        <p><span class="highlight">Mobile:</span> {{ $phoneee }}</p>

        <h4>Address Details</h4>
        <div class="row">
            <div class="col-6">
                <h4>User Address</h4>
                <p><span class="highlight">Name:</span> {{ $address->name ?? 'N/A' }}</p>
                <p><span class="highlight">Mobile:</span> {{ $address->mobile ?? 'N/A' }}</p>
                <p><span class="highlight">House Id:</span> {{ $address->house_id ?? 'N/A' }}</p>
                <p><span class="highlight">Street:</span> {{ $address->street ?? 'N/A' }}</p>
                <p><span class="highlight">Landmark:</span> {{ $address->landmark ?? 'N/A' }}</p>
                <p><span class="highlight">State:</span> {{ $address->state ?? 'N/A' }}</p>
                <p><span class="highlight">City:</span> {{ $address->city ?? 'N/A' }}</p>
                <p><span class="highlight">PIN/ZIP Code:</span> {{ $address->code ?? 'N/A' }}</p>
                <p><span class="highlight">Address Type:</span> {{ $address->address_type ?? 'N/A' }}</p>
            </div>

            <div class="col-6">
                <h4>Shipping Address</h4>
                <p><span class="highlight">Name:</span> {{ $shippingAddress->name ?? 'N/A' }}</p>
                <p><span class="highlight">Mobile:</span> {{ $shippingAddress->mobile ?? 'N/A' }}</p>
                <p><span class="highlight">Street:</span> {{ $shippingAddress->street ?? 'N/A' }}</p>
                <p><span class="highlight">Landmark:</span> {{ $shippingAddress->landmark ?? 'N/A' }}</p>
                <p><span class="highlight">State:</span> {{ $shippingAddress->state ?? 'N/A' }}</p>
                <p><span class="highlight">City:</span> {{ $shippingAddress->city ?? 'N/A' }}</p>
                <p><span class="highlight">PIN/ZIP Code:</span> {{ $shippingAddress->code ?? 'N/A' }}</p>
                <p><span class="highlight">Address Type:</span> {{ $shippingAddress->address_type ?? 'N/A' }}</p>
            </div>
        </div>

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

        @php
            $productTotal = 0;
            foreach ($orderItems as $item) {
                $productTotal += $item->sale_price * $item->quantity;
            }
        @endphp   
        <!-- Adding product total, shipping, and final total -->
        <table class="order-details" style="margin-top: 20px;">
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

        <a href="https://logfeller.cyberx-infosystem.us/login" class="cta-button" target="_blank" style="color: #ffffff">View Order Details</a>

        <p class="footer">
            Thank you for using {{ env('APP_NAME') }}.
        </p>
    </div>
</body>

</html>
