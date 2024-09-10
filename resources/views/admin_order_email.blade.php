<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4CAF50;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .order-details {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }
        .order-details th, .order-details td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .order-details th {
            background-color: #f4f4f4;
        }
        .order-details td {
            background-color: #fafafa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>Order Confirmation</h2>
        <p>Hello Admin,</p>
        <p>You have received a new order from <strong>{{ $name }}</strong> ({{ $email }}).</p>

        <h3>Order Details:</h3>
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
                        <td>${{ $item->sale_price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footer">
            Thank you for using {{ env('APP_NAME') }}.
        </p>
    </div>
</body>
</html>
