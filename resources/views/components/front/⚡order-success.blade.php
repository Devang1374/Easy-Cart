<?php

use Livewire\Component;
use App\Models\orderTable;

use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component {
    public $order_id;
    public $data;
    public $order;
    public $items;

    public function mount()
    {
        $order = orderTable::with('items.product')->where('order_number', $this->order_id)->first();
        $this->order = $order;

        if ($order['pyment'] === 'pending') {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://sandbox.cashfree.com/pg/orders/{$this->order_id}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => ['x-api-version: 2025-01-01', 'x-client-id: ' . env('CASHFREE_APP_ID'), 'x-client-secret: ' . env('CASHFREE_SECRET_KEY')],
            ]);

            $response = curl_exec($curl);
            $this->data = json_decode($response, true);

            if ($this->data['order_status'] === 'PAID') {
                if ($order && $order->pyment !== 'PAID') {
                    $order->update([
                        'pyment' => 'PAID',
                    ]);

                    foreach ($order->items as $item) {
                        $item->product->decrement('stock', $item->quantity);
                    }

                    if ($order->coupon_id) {
                        $order->coupon()->increment('used_count');
                    }
                }
            }

            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo 'cURL Error #:' . $err;
            }
        }

        session()->forget('cart');
    }

    public function payNow()
    {
        return redirect()->route('user/checkout', ['order_id' => $this->order['id']]);
    }

    public function cancel()
    {
        orderTable::where('id', $this->order['id'])->update([
            'status' => 'cancelled',
        ]);
        return redirect()->route('user/product');
    }

    public function downloadInvoice()
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $this->order,
        ]);

        return response()->streamDownload(fn() => print $pdf->output(), 'Invoice-' . $this->order->order_number . '.pdf');
    }

    public function isStepCompleted($step)
    {
        $status = strtolower($this->order->status);

        $steps = ['pending', 'processing', 'shipped', 'delivered'];

        if ($status === 'cancelled') {
            return false;
        }

        return array_search($step, $steps) <= array_search($status, $steps);
    }

    public function isCurrentStep($step)
    {
        return strtolower($this->order->status) === strtolower($step);
    }

    public function isFutureStep($step)
    {
        $steps = ['pending', 'processing', 'shipped', 'delivered'];

        return array_search($step, $steps) > array_search(strtolower($this->order->status), $steps);
    }

    public function isLineCompleted($step)
    {
        $steps = ['pending', 'processing', 'shipped', 'delivered'];

        $current = array_search(strtolower($this->order->status), $steps);
        $line = array_search(strtolower($step), $steps);

        return $line < $current;
    }

    // order return request
    public bool $showReturnForm = false;
    public string $returnReason = '';
    public function toggleReturnForm()
    {
        $this->showReturnForm = !$this->showReturnForm;
    }

    //submit order return request
    public function submitReturn()
    {
        $this->validate([
            'returnReason' => 'required|min:20|max:1000',
        ]);

        $this->order->update([
            'return_requested' => true,
            'return_status' => 'requested',
            'return_reason' => $this->returnReason,
            'return_requested_at' => now(),
        ]);

        $this->order->refresh();

        $this->showReturnForm = false;

        Flux::toast(heading: 'Return Request Submitted', text: 'Our team will review your request shortly.');
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10">

    {{-- Success Header --}}
    <div
        class="rounded-3xl border border-zinc-200 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="text-6xl">
            🎉
        </div>

        <h1 class="mt-4 text-3xl font-black">
            Order Placed Successfully
        </h1>

        <p class="mt-2 text-zinc-500 dark:text-zinc-400">
            Thank you for your purchase.
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">

            <div class="rounded-xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                <span class="text-sm text-zinc-500">Order Number</span>
                <div class="font-semibold">
                    {{ $order->order_number }}
                </div>
            </div>

            <div class="rounded-xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                <span class="text-sm text-zinc-500">Payment</span>

                <div class="font-semibold">
                    @if ($order->pyment === 'PAID')
                        <span class="text-green-600 dark:text-green-400">
                            Paid
                        </span>
                    @elseif ($order->pyment === 'Refunded')
                        <span class="text-green-600 dark:text-green-400">
                            Refunded
                        </span>
                    @else
                        <span class="text-yellow-600 dark:text-yellow-400">
                            Pending
                        </span>
                    @endif
                </div>
            </div>

            <div class="rounded-xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                <span class="text-sm text-zinc-500">Status</span>

                <div class="font-semibold capitalize">
                    @if ($order->status === 'delivered')
                        <span class="text-green-600 dark:text-green-400">
                            Delivered
                        </span>
                    @elseif ($order->status === 'Returned')
                        <span class="text-green-600 dark:text-green-400">
                            Returned
                        </span>
                    @else
                        <span class="text-yellow-600 dark:text-yellow-400">
                            {{ $order->status }}
                        </span>
                    @endif
                </div>
            </div>

        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">

            @if ($order->pyment !== 'Refunded' || $order->status != 'Returned')
                @if ($order->pyment !== 'PAID' && $order->status != 'cancelled')
                    <flux:button wire:click="payNow">
                        Pay Now
                    </flux:button>

                    <flux:button variant="danger" wire:click="cancel">
                        Cancel
                    </flux:button>
                @endif
            @endif

            <a href="{{ route('user/product') }}">
                <flux:button variant="primary">
                    Continue Shopping
                </flux:button>
            </a>

            <flux:button variant="outline" wire:click="downloadInvoice">
                Download Invoice
            </flux:button>

            @if (strtolower($order->status) === 'delivered' && !$order->return_requested)
                <flux:button variant="danger" wire:click="toggleReturnForm">
                    Request Return
                </flux:button>
            @endif

            <flux:modal wire:model="showReturnForm" class="w-full max-w-4xl">

                <h3 class="text-xl font-bold">
                    Return Request
                </h3>

                <p class="mt-2 text-zinc-500">
                    Please tell us why you want to return this order.
                </p>

                <div class="mt-6">

                    <flux:textarea wire:model="returnReason" label="Reason" rows="5"
                        placeholder="Example: Received damaged product..." />

                </div>

                <div class="mt-6 flex gap-3">

                    <flux:button variant="danger" wire:click="submitReturn">
                        Submit Request
                    </flux:button>

                    <flux:button variant="ghost" wire:click="toggleReturnForm">
                        Cancel
                    </flux:button>

                </div>

            </flux:modal>

        </div>

    </div>

    {{-- returnt request tracking --}}
    @if ($order->return_requested)

        @if ($order->return_status !== 'none')

            <section
                class="mt-8 rounded-3xl border border-orange-200 bg-orange-50 p-6 shadow-sm dark:border-orange-800 dark:bg-orange-100/20">

                {{-- Header --}}
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-2xl dark:bg-orange-900/40">
                            📦
                        </div>

                        <div>

                            <h2 class="text-2xl font-bold">
                                Return Request
                            </h2>

                            <p class="mt-2 text-zinc-600 dark:text-zinc-300">

                                @switch($order->return_status)
                                    @case('requested')
                                        Your return request has been received and is currently under review.
                                    @break

                                    @case('approved')
                                        Great news! Your return request has been approved. Please send the product back
                                        following the provided instructions.
                                    @break

                                    @case('received')
                                        We've received your returned product. Your refund is now being processed.
                                    @break

                                    @case('refunded')
                                        Your refund has been successfully completed. Thank you for shopping with us.
                                    @break

                                    @case('rejected')
                                        Unfortunately, your return request could not be approved. Please check the support
                                        message below for more details.
                                    @break
                                @endswitch

                            </p>

                        </div>

                    </div>

                    {{-- Status --}}
                    <div>

                        @switch($order->return_status)
                            @case('requested')
                                <flux:badge color="yellow" size="lg">
                                    Request Pending
                                </flux:badge>
                            @break

                            @case('approved')
                                <flux:badge color="blue" size="lg">
                                    Approved
                                </flux:badge>
                            @break

                            @case('received')
                                <flux:badge color="purple" size="lg">
                                    Product Received
                                </flux:badge>
                            @break

                            @case('refunded')
                                <flux:badge color="green" size="lg">
                                    Refunded
                                </flux:badge>
                            @break

                            @case('rejected')
                                <flux:badge color="red" size="lg">
                                    Rejected
                                </flux:badge>
                            @break
                        @endswitch

                    </div>

                </div>

                {{-- Details --}}
                <div class="mt-8 grid gap-6 lg:grid-cols-2">

                    {{-- Return Reason --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">

                        <h3 class="mb-3 font-semibold">
                            Return Reason
                        </h3>

                        <p class="whitespace-pre-line text-zinc-600 dark:text-zinc-300">
                            {{ $order->return_reason }}
                        </p>

                    </div>

                    {{-- Request Information --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">

                        <h3 class="mb-3 font-semibold">
                            Request Information
                        </h3>

                        <div class="space-y-3">

                            <div class="flex justify-between">

                                <span class="text-zinc-500">
                                    Requested On
                                </span>

                                <span class="font-medium">
                                    {{ \Carbon\Carbon::parse($order->return_requested_at)->format('d M Y') }}
                                </span>

                            </div>

                            <div class="flex justify-between">

                                <span class="text-zinc-500">
                                    Time
                                </span>

                                <span class="font-medium">
                                    {{ \Carbon\Carbon::parse($order->return_requested_at)->format('h:i A') }}
                                </span>

                            </div>

                            @if ($order->return_completed_at)
                                <div class="flex justify-between">

                                    <span class="text-zinc-500">
                                        Completed On
                                    </span>

                                    <span class="font-medium text-green-600 dark:text-green-400">
                                        {{ \Carbon\Carbon::parse($order->return_completed_at)->format('d M Y') }}
                                    </span>

                                </div>
                            @endif

                        </div>

                    </div>

                </div>

                {{-- Support Message --}}
                @if ($order->return_admin_note)
                    <div
                        class="mt-8 rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-900/20">

                        <div class="flex items-center gap-2">

                            <span class="text-xl">
                                💬
                            </span>

                            <h3 class="font-semibold">
                                Message from Support
                            </h3>

                        </div>

                        <p class="mt-4 whitespace-pre-line text-zinc-700 dark:text-zinc-300">
                            {{ $order->return_admin_note }}
                        </p>

                    </div>
                @endif

            </section>

        @endif
        {{-- Order Tracking --}}
    @else
        <div
            class="mt-8 rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <h2 class="text-2xl font-bold">
                Order Tracking
            </h2>

            <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                Track the progress of your order.
            </p>

            @if ($order->status === 'cancelled')
                <div
                    class="mt-8 flex items-center gap-4 rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900 dark:bg-red-950/30">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-500 text-xl text-white">
                        ✕
                    </div>

                    <div>
                        <h3 class="font-semibold text-red-600 dark:text-red-400">
                            Order Cancelled
                        </h3>

                        <p class="text-sm text-zinc-500">
                            This order has been cancelled.
                        </p>
                    </div>

                </div>
            @else
                <div class="mt-4 rounded-2xl bg-blue-50 p-4 dark:bg-blue-900/20">

                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Estimated Delivery
                    </p>

                    <p class="mt-1 text-lg font-bold">
                        {{ $order->created_at->addDays(5)->format('d M Y') }}
                    </p>

                </div>

                <div class="mt-8 space-y-8">

                    {{-- Pending --}}
                    <div class="flex items-start gap-4" style="margin-bottom: 5px;">

                        <div class="flex flex-col items-center">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full @if ($this->isCurrentStep('pending')) bg-blue-600 ring-4 ring-blue-200 animate-pulse text-white @elseif($this->isStepCompleted('pending')) bg-green-500 text-white @else bg-zinc-200 dark:bg-zinc-700 @endif">
                                ✓
                            </div>

                            <div
                                class="mt-2 h-16 w-1 rounded-full
                            @if ($this->isLineCompleted('pending')) bg-green-500
                            @else
                                bg-zinc-200 dark:bg-zinc-700 @endif">
                            </div>

                        </div>

                        <div>

                            <h3 class="font-semibold">
                                Order Placed
                            </h3>

                            <p class="text-sm text-zinc-500">
                                We have received your order.
                            </p>

                        </div>

                    </div>

                    {{-- Processing --}}
                    <div class="flex items-start gap-4" style=" margin-bottom: 5px;">

                        <div class="flex flex-col items-center">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full @if ($this->isCurrentStep('processing')) bg-blue-600 ring-4 ring-blue-200 animate-pulse text-white @elseif($this->isStepCompleted('processing')) bg-green-500 text-white @else bg-zinc-200 dark:bg-zinc-700 @endif">
                                ⚙
                            </div>

                            <div
                                class="mt-2 h-16 w-1 rounded-full
                            @if ($this->isLineCompleted('processing')) bg-green-500
                            @else
                                bg-zinc-200 dark:bg-zinc-700 @endif">
                            </div>

                        </div>

                        <div>

                            <h3 class="font-semibold">
                                Processing
                            </h3>

                            <p class="text-sm text-zinc-500">
                                Your items are being prepared.
                            </p>

                        </div>

                    </div>

                    {{-- Shipped --}}
                    <div class="flex items-start gap-4" style="margin-bottom: 5px;">

                        <div class="flex flex-col items-center">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full @if ($this->isCurrentStep('shipped')) bg-blue-600 ring-4 ring-blue-200 animate-pulse text-white @elseif($this->isStepCompleted('shipped')) bg-green-500 text-white @else bg-zinc-200 dark:bg-zinc-700 @endif">
                                🚚
                            </div>

                            <div
                                class="mt-2 h-16 w-1 rounded-full
                            @if ($this->isLineCompleted('shipped')) bg-green-500
                            @else
                                bg-zinc-200 dark:bg-zinc-700 @endif">
                            </div>

                        </div>

                        <div>

                            <h3 class="font-semibold">
                                Shipped
                            </h3>

                            <p class="text-sm text-zinc-500">
                                Your order is on the way.
                            </p>

                        </div>

                    </div>

                    {{-- Delivered --}}
                    <div class="flex items-start gap-4" style="margin-bottom: 5px;">

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full @if ($this->isStepCompleted('delivered')) bg-green-500 text-white @else bg-zinc-200 dark:bg-zinc-700 @endif">
                            📦
                        </div>

                        <div>

                            <h3 class="font-semibold">
                                Delivered
                            </h3>

                            <p class="text-sm text-zinc-500">
                                Your order has been delivered.
                            </p>

                        </div>

                    </div>

                </div>
            @endif

        </div>
    @endif

    {{-- Order Items --}}
    <div class="mt-8 rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-6 dark:border-zinc-800">
            <h2 class="text-xl font-bold">
                Order Items
            </h2>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">

            @foreach ($order->items as $item)
                <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center">

                    {{-- Product Image --}}
                    <div class="h-24 w-24 overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800">

                        @if (isset($item->product->images[0]))
                            <img src="{{ $item->product->images[0]->image }}" alt="{{ $item->product_name }}"
                                class="h-full w-full object-cover">
                        @endif

                    </div>

                    {{-- Product Info --}}
                    <div class="flex-1">

                        <h3 class="font-semibold">
                            {{ $item->product_name }}
                        </h3>

                        <p class="mt-1 text-sm text-zinc-500">
                            Quantity: {{ $item->quantity }}
                        </p>

                    </div>

                    {{-- Price --}}
                    <div class="text-right">

                        <div class="font-semibold">
                            ₹{{ number_format($item->subtotal, 2) }}
                        </div>

                        <div class="text-sm text-zinc-500">
                            ₹{{ number_format($item->price, 2) }} each
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

    {{-- Order Summary --}}
    <div class="mt-8 rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <h2 class="text-xl font-bold">
            Order Summary
        </h2>

        <div class="mt-4 space-y-3">

            <div class="flex justify-between">
                <span>Subtotal</span>

                <span>
                    ₹{{ number_format($order->total_amount + $order->discount_amount, 2) }}
                </span>
            </div>

            @if ($order->discount_amount > 0)

                <div class="flex justify-between text-green-600">

                    <span>
                        Discount
                        @if ($order->coupon)
                            ({{ $order->coupon->code }})
                        @endif
                    </span>

                    <span>
                        -₹{{ number_format($order->discount_amount, 2) }}
                    </span>

                </div>

            @endif

            <div class="border-t pt-3 flex justify-between text-2xl font-black">

                <span>Total</span>

                <span>
                    ₹{{ number_format($order->total_amount, 2) }}
                </span>

            </div>

        </div>

    </div>

</div>
