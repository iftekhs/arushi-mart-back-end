<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; margin-bottom: 20px; }
        .order-details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Order Received</h2>
        </div>

        <p>A new order has been placed on Arushi Mart.</p>

        <div class="order-details">
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Customer:</strong> {{ $order->user->name }} ({{ $order->user->email }})</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i A') }}</p>
            <p><strong>Total Amount:</strong> {{ number_format($order->total_amount, 2) }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Qty</th>
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
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Please check the admin panel for more details.</p>
    </div>
</body>
</html>
