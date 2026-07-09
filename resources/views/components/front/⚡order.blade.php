<?php

use Livewire\Component;

use App\Models\orderTable;

new class extends Component {
    public $orders;
    public function mount()
    {
        $this->orders = orderTable::where('user_id', auth()->id())
            ->latest()
            ->get();
    }
};
?>

<div class="mx-auto max-w-7xl px-6 py-10">

    <h1 class="text-3xl font-bold">
        My Orders
    </h1>

    <div class="mt-8 space-y-4">

        @forelse($orders as $order)
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="grid gap-4 md:grid-cols-[2fr_1fr_1fr_1fr_auto] md:items-center">

                    {{-- Order Info --}}
                    <div>
                        <h3 class="font-semibold">
                            {{ $order->order_number }}
                        </h3>

                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $order->created_at->format('d M Y') }}
                        </p>
                    </div>

                    {{-- Total --}}
                    <div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            Total
                        </div>

                        <div class="font-semibold">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </div>
                    </div>

                    {{-- Order Status --}}
                    <div>

                        @if ($order->status === 'pending')
                            <span
                                class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                Pending
                            </span>
                        @elseif($order->status === 'processing')
                            <span
                                class="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                Processing
                            </span>
                        @elseif($order->status === 'shipped')
                            <span
                                class="rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                Shipped
                            </span>
                        @elseif($order->status === 'delivered')
                            <span
                                class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Delivered
                            </span>
                        @elseif($order->status === 'Returned')
                            <span
                                class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Returned
                            </span>
                        @elseif($order->status === 'Received')
                            <span
                                class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Return is Under Processing
                            </span>
                        @else
                            <span
                                class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                Cancelled
                            </span>
                        @endif

                    </div>

                    {{-- Payment Status --}}
                    <div>

                        @if ($order->pyment === 'PAID')
                            <span
                                class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Paid
                            </span>
                        @elseif ($order->pyment === 'Refunded')
                            <span
                                class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Refunded
                            </span>
                        @else
                            <span
                                class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                Payment Pending
                            </span>
                        @endif

                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap justify-end gap-2">

                        <a href="{{ route('user/order-success', $order->order_number) }}">
                            <flux:button variant="ghost">
                                View Order
                            </flux:button>
                        </a>

                        @if ($order->pyment !== 'Refunded' && $order->status != 'Returned')
                            @if ($order->pyment !== 'PAID' && $order->status != 'cancelled')
                                <a href="{{ route('user/checkout', $order->id) }}">
                                    <flux:button variant="primary">
                                        Pay Now
                                    </flux:button>
                                </a>
                            @endif
                        @endif

                    </div>

                </div>

            </div>
        @empty

            <div class="py-20 text-center">

                <h2 class="text-xl font-semibold">
                    No Orders Found
                </h2>

            </div>
        @endforelse

    </div>

</div>
