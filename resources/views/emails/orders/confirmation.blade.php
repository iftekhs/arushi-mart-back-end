<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Order Confirmation</h2>
            <p>Thank you for your order!</p>
        </div>

        <p>Hi {{ $order->user->name }},</p>
        <p>We've received your order and it's currently being processed. Here are your order details:</p>

        <div class="order-details">
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
            <p><strong>Payment Method:</strong> {{ $order->payment_method->name }}</p>
            <p><strong>Shipping Status:</strong> {{ $order->shipping_status->name }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Color/Size</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_snapshot['title'] ?? 'Product' }}</td>
                    <td>
                        {{ $item->product_snapshot['color']['name'] ?? '' }}
                        @if(!empty($item->product_snapshot['size']))
                        / {{ $item->product_snapshot['size']['name'] }}
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total">Subtotal:</td>
                    <td>{{ number_format($order->total_amount - $order->shipping_cost, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="total">Shipping:</td>
                    <td>{{ number_format($order->shipping_cost, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="total">Total:</td>
                    <td>{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($order->shipping_address_snapshot)
        <div class="shipping-address">
            <h3>Shipping Address</h3>
            <p>
                {{ $order->shipping_address_snapshot['full_name'] }}<br>
                {{ $order->shipping_address_snapshot['address'] }}<br>
                @endif
                Phone: {{ $order->shipping_address_snapshot['phone'] }}
            </p>
        </div>
        @endif

        <div class="footer">
            <p>&copy; {{ date('Y') }} Arushi Mart. All rights reserved.</p>
        </div>
    </div>
</body>

</html>