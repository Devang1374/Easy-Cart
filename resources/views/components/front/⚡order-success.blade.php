<?php

use Livewire\Component;
use App\Models\orderTable;

use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component
{
    public $order_id;
    public $data;
    public $order;
    public $items;
  
    public function mount(){
        $order = orderTable::with("items.product")->where('order_number', $this->order_id)->first();
        $this->order = $order;

        if($order['pyment'] === 'pending'){

            $curl = curl_init();

            curl_setopt_array($curl, [
              CURLOPT_URL => "https://sandbox.cashfree.com/pg/orders/{$this->order_id}",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => [
                "x-api-version: 2025-01-01",
                "x-client-id: ".env('CASHFREE_APP_ID'),
                "x-client-secret: ".env('CASHFREE_SECRET_KEY')
              ],
            ]);

            $response = curl_exec($curl);
            $this->data = json_decode($response, true);

            if ($this->data['order_status'] === "PAID") {

                if ($order && $order->pyment !== 'PAID') {

                    $order->update([
                        'pyment' => 'PAID'
                    ]);

                    foreach ($order->items as $item) {

                        $item->product->decrement(
                            'stock',
                            $item->quantity
                        );

                    }

                }
            }

            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
              echo "cURL Error #:" . $err;
            }
        }

        session()->forget('cart');
    }

    public function payNow(){
        return redirect()->route('user/checkout',['order_id' => $this->order['id']]);
    }

    public function cancel(){
        orderTable::where('id', $this->order['id'])->update([
            'status' => "cancelled"
        ]);
        return redirect()->route('user/product');
    }

    public function downloadInvoice()
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $this->order,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Invoice-' . $this->order->order_number . '.pdf'
        );
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10">

    {{-- Success Header --}}
    <div class="rounded-3xl border border-zinc-200 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

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
                    @if($order->pyment === 'PAID')
                        <span class="text-green-600 dark:text-green-400">
                            Paid
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
                    @if($order->status === "delivered")
                        <span class="text-green-600 dark:text-green-400">
                            Delivered
                        </span>
                    @else
                        <span class="text-yellow-600 dark:text-yellow-400">
                            {{$order->status}}
                        </span>
                    @endif
                </div>
            </div>

        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">

            @if($order->pyment !== 'PAID' && $order->status != "cancelled")
                <flux:button wire:click="payNow">
                    Pay Now
                </flux:button>

                <flux:button variant="danger" wire:click="cancel">
                    Cancel
                </flux:button>
            @endif

            <a href="{{ route('user/product') }}">
                <flux:button variant="primary">
                    Continue Shopping
                </flux:button>
            </a>

            <flux:button
                variant="outline"
                wire:click="downloadInvoice"
            >
                Download Invoice
            </flux:button>

        </div>

    </div>

    {{-- Order Items --}}
    <div class="mt-8 rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-6 dark:border-zinc-800">
            <h2 class="text-xl font-bold">
                Order Items
            </h2>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">

            @foreach($order->items as $item)

                <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center">

                    {{-- Product Image --}}
                    <div class="h-24 w-24 overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800">

                        @if(isset($item->product->images[0]))
                            <img
                                src="{{ asset('storage/'.$item->product->images[0]->image) }}"
                                alt="{{ $item->product_name }}"
                                class="h-full w-full object-cover"
                            >
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

        <div class="mt-4 flex items-center justify-between">
            <span>Total Amount</span>

            <span class="text-2xl font-black">
                ₹{{ number_format($order->total_amount, 2) }}
            </span>
        </div>

    </div>

</div>