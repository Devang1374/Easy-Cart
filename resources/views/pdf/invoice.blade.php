<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 40px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: top;
        }

        .store-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
        }

        .invoice-title {
            font-size: 30px;
            font-weight: bold;
        }

        .divider {
            border-top: 2px solid #2563eb;
            margin: 25px 0;
        }

        .info-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #2563eb;
        }

        .items-table {
            margin-top: 25px;
        }

        .items-table th {
            background: #2563eb;
            color: #fff;
            text-align: left;
            padding: 10px;
            font-size: 13px;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 320px;
            margin-left: auto;
            margin-top: 25px;
        }

        .summary td {
            border: none;
            padding: 8px 0;
        }

        .grand-total td {
            border-top: 2px solid #2563eb;
            font-size: 18px;
            font-weight: bold;
            padding-top: 12px;
        }

        .badge-paid {
            color: #16a34a;
            font-weight: bold;
        }

        .badge-pending {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #777;
            font-size: 12px;
        }

        .thanks {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
    </style>

</head>

<body>

    {{-- Header --}}
    <table class="header-table">

        <tr>

            <td width="60%">

                <div class="store-name">
                    {{ config('store.name') }}
                </div>

                <p>{{ config('store.address') }}</p>

                <p>{{ config('store.email') }}</p>

                <p>{{ config('store.phone') }}</p>

            </td>

            <td width="40%" align="right">

                <div class="invoice-title">
                    TAX INVOICE
                </div>

                <br>

                <strong>Invoice #</strong><br>

                INV-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}

                <br><br>

                <strong>Date</strong><br>

                {{ $order->created_at->format('d M Y') }}

            </td>

        </tr>

    </table>

    <div class="divider"></div>

    {{-- Billing --}}
    <table class="info-table">

        <tr>

            <td width="55%">

                <div class="section-title">
                    BILL TO
                </div>

                <strong>
                    {{ $order->first_name }} {{ $order->last_name }}
                </strong>

                <br>

                {{ $order->email }}

                <br>

                {{ $order->phone }}

                <br><br>

                {{ $order->address }}

                <br>

                {{ $order->city }}, {{ $order->state }}

                <br>

                {{ $order->pincode }}

            </td>

            <td width="45%" align="right">

                <div class="section-title">
                    ORDER DETAILS
                </div>

                <strong>Order #</strong>

                <br>

                {{ $order->order_number }}

                <br><br>

                <strong>Payment</strong>

                <br>

                @if($order->pyment === 'PAID')

                    <span class="badge-paid">
                        PAID
                    </span>

                @else

                    <span class="badge-pending">
                        PENDING
                    </span>

                @endif

                <br><br>

                <strong>Status</strong>

                <br>

                {{ ucfirst($order->status) }}

            </td>

        </tr>

    </table>

    {{-- Products --}}
    <table class="items-table">

        <thead>

            <tr>

                <th>Product</th>

                <th width="70">Qty</th>

                <th width="120">Price</th>

                <th width="120">Total</th>

            </tr>

        </thead>

        <tbody>

            @foreach($order->items as $item)

                <tr>

                    <td>

                        {{ $item->product->name }}

                    </td>

                    <td>

                        {{ $item->quantity }}

                    </td>

                    <td>

                        ₹{{ number_format($item->price,2) }}

                    </td>

                    <td>

                        ₹{{ number_format($item->price * $item->quantity,2) }}

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    {{-- Summary --}}
    <table class="summary">

        <tr>

            <td>Subtotal</td>

            <td class="text-right">

                ₹{{ number_format($order->total_amount,2) }}

            </td>

        </tr>

        <tr>

            <td>Shipping</td>

            <td class="text-right">

                FREE

            </td>

        </tr>

        <tr class="grand-total">

            <td>Grand Total</td>

            <td class="text-right">

                ₹{{ number_format($order->total_amount,2) }}

            </td>

        </tr>

    </table>

    {{-- Footer --}}
    <div class="footer">

        <div class="thanks">

            Thank you for shopping with {{ config('store.name') }}!

        </div>

        <p>

            If you have any questions, contact us at
            {{ config('store.email') }}

        </p>

        <p>
            This is a computer-generated invoice and does not require a signature.
        </p>

    </div>

</body>

</html>