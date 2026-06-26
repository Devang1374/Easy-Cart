<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:20px;">

    <div style="max-width:600px;margin:auto;background:white;padding:30px;border-radius:12px;">

        <h1 style="margin-top:0;">
            🎉 Order Confirmed
        </h1>

        <p>
            Hi {{ $order->first_name }},
        </p>

        <p>
            Thank you for your order. We've received it successfully.
        </p>

        <hr>

        <p>
            <strong>Order Number:</strong>
            {{ $order->order_number }}
        </p>

        <p>
            <strong>Total Amount:</strong>
            ₹{{ number_format($order->total_amount, 2) }}
        </p>

        <p>
            <strong>Payment Status:</strong>
            {{ $order->pyment }}
        </p>

        <p>
            <strong>Order Status:</strong>
            {{ ucfirst($order->status) }}
        </p>

        <hr>

        <h3>Items Ordered</h3>

        @foreach($order->items as $item)

            <p>
                {{ $item->product_name }}
                × {{ $item->quantity }}
                — ₹{{ number_format($item->subtotal, 2) }}
            </p>

        @endforeach

        <hr>

        <p>
            Thank you for shopping with us.
        </p>

    </div>

</body>
</html>