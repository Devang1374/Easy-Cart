<?php

use Livewire\Component;

use Livewire\Attributes\On;  
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

use App\Models\orderTable;

new class extends Component
{
    use WithPagination;

    public $status = 'all';
    
    #[Computed]
    public function orders(){
        return orderTable::query()
                ->when($this->search, function ($query) {
                    $query->where('order_number', 'like', '%' . $this->search . '%')
                        ->orWhere('first_name', 'like', "%$this->search%")
                        ->orWhere('last_name', 'like', "%$this->search%");
                })

                ->when(
                    $this->status !== 'all',
                    fn ($query) => $query->where('status', $this->status)
                )
                ->latest()
                ->paginate(15);
    }

    public string $search = "";
    public function updatingSearch(){
        $this->resetPage();
    }

    public $message;
    #[on('send-message')]
    public function handleMessage($message){
        $this->message = $message;
    }

    public $selectedOrder = null;
    public $showOrderModal = false;
    public function viewOrder($id)
    {
        $this->selectedOrder = orderTable::with('items.product.images')
            ->findOrFail($id);

        $this->showOrderModal = true;
    }

    public function updateStatus($id, $status)
    {
        orderTable::where('id', $id)
            ->update([
                'status' => $status
            ]);

        $this->selectedOrder->status = $status;
    }
};
?>

<div class="relative w-full flex flex-col gap-5 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">

    @if($message)
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 5000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-5 right-5 z-50 max-w-sm"
        >
            <div class="flex flex-row items-center justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50 p-4 shadow-lg shadow-indigo-100/40 dark:border-indigo-950 dark:bg-indigo-950/50 dark:shadow-none">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse shrink-0"></span>
                    <p class="text-sm font-medium text-indigo-900 dark:text-indigo-200">
                        {{$message}}
                    </p>
                </div>
        
                <button 
                    @click="show = false" 
                    class="rounded-lg p-1 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-700 dark:text-indigo-300 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-200 transition-colors duration-200 focus:outline-none"
                    aria-label="Close notification"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
       <div class="w-full sm:w-72">
           <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Categories..."/>
       </div>
       
       <div class="flex flex-wrap gap-2 overflow-x-auto max-w-full pb-1 sm:pb-0">

            <flux:button
                wire:click="$set('status', 'all')"
                variant="{{ $status === 'all' ? 'primary' : 'ghost' }}"
            >
                All
            </flux:button>

            <flux:button
                wire:click="$set('status', 'pending')"
                variant="{{ $status === 'pending' ? 'primary' : 'ghost' }}"
            >
                Pending
            </flux:button>

            <flux:button
                wire:click="$set('status', 'processing')"
                variant="{{ $status === 'processing' ? 'primary' : 'ghost' }}"
            >
                Processing
            </flux:button>

            <flux:button
                wire:click="$set('status', 'shipped')"
                variant="{{ $status === 'shipped' ? 'primary' : 'ghost' }}"
            >
                Shipped
            </flux:button>

            <flux:button
                wire:click="$set('status', 'delivered')"
                variant="{{ $status === 'delivered' ? 'primary' : 'ghost' }}"
            >
                Delivered
            </flux:button>

            <flux:button
                wire:click="$set('status', 'cancelled')"
                variant="{{ $status === 'cancelled' ? 'primary' : 'ghost' }}"
            >
                Cancelled
            </flux:button>

        </div>
    </div>

    <div class="relative w-full overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-2">
        <flux:table scrollable container:class="w-full" :paginate="$this->orders">

            <flux:table.columns>
                <flux:table.column>Order</flux:table.column>
                <flux:table.column>Customer</flux:table.column>
                <flux:table.column>Total</flux:table.column>
                <flux:table.column>Payment</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @foreach($this->orders as $order)

                    <flux:table.row>

                        <flux:table.cell>
                            {{ $order->order_number }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $order->first_name }} {{ $order->last_name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            ₹{{ number_format($order->total_amount, 2) }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @if($order->pyment === 'PAID')

                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Paid
                                </span>

                            @else

                                <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Pending
                                </span>

                            @endif

                        </flux:table.cell>

                        <flux:table.cell>

                            @if($order->status === 'pending')

                                <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Pending
                                </span>

                            @elseif($order->status === 'processing')

                                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    Processing
                                </span>

                            @elseif($order->status === 'shipped')

                                <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    Shipped
                                </span>

                            @elseif($order->status === 'delivered')

                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Delivered
                                </span>

                            @else

                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    Cancelled
                                </span>

                            @endif

                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $order->created_at->format('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                wire:click="viewOrder({{ $order->id }})"
                            >
                                View
                            </flux:button>
                        </flux:table.cell>

                    </flux:table.row>

                @endforeach

            </flux:table.rows>

        </flux:table>
    </div>

    <flux:modal wire:model="showOrderModal" class="w-full max-w-4xl">

        @if($selectedOrder)

            <div class="space-y-6">

                <div>
                    <h2 class="text-2xl font-bold">
                        {{ $selectedOrder->order_number }}
                    </h2>

                    <p class="text-zinc-500">
                        {{ $selectedOrder->created_at->format('d M Y h:i A') }}
                    </p>
                </div>

                {{-- Customer --}}
                <div class="rounded-xl border p-4">

                    <h3 class="mb-3 font-semibold">
                        Customer Details
                    </h3>

                    <p>{{ $selectedOrder->first_name }} {{ $selectedOrder->last_name }}</p>
                    <p class="break-all">{{ $selectedOrder->email }}</p>
                    <p>{{ $selectedOrder->phone }}</p>

                </div>

                {{-- Address --}}
                <div class="rounded-xl border p-4">

                    <h3 class="mb-3 font-semibold">
                        Shipping Address
                    </h3>

                    <p>{{ $selectedOrder->address }}</p>
                    <p>{{ $selectedOrder->city }}</p>
                    <p>{{ $selectedOrder->state }}</p>
                    <p>{{ $selectedOrder->pincode }}</p>

                </div>

                {{-- Products --}}
                <div class="rounded-xl border p-4">

                    <h3 class="mb-4 font-semibold">
                        Order Items
                    </h3>

                    <div class="space-y-4">

                        @foreach($selectedOrder->items as $item)

                            <div class="flex items-center gap-4">

                                @if(isset($item->product->images[0]))

                                    <img
                                        src="{{ $item->product->images[0]->image }}"
                                        class="h-16 w-16 shrink-0 rounded-lg object-cover"
                                    >

                                @endif

                                <div class="flex-1 min-w-0">

                                    <div class="font-medium truncate">
                                        {{ $item->product_name }}
                                    </div>

                                    <div class="text-sm text-zinc-500">
                                        Qty: {{ $item->quantity }}
                                    </div>

                                </div>

                                <div class="shrink-0 font-medium">
                                    ₹{{ number_format($item->subtotal, 2) }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

                {{-- Status Management --}}
                <div class="grid gap-4 md:grid-cols-2">

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Payment Status
                        </label>

                        <div class="font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ $selectedOrder->pyment }}
                        </div>

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Order Status
                        </label>

                        <flux:select wire:change="updateStatus('{{ $selectedOrder->id }}', $event.target.value)" class="w-full rounded-lg border">
                            <option value="pending" @selected($selectedOrder->status === 'pending')>
                                Pending
                            </option>

                            <option value="processing" @selected($selectedOrder->status === 'processing')>
                                Processing
                            </option>

                            <option value="shipped" @selected($selectedOrder->status === 'shipped')>
                                Shipped
                            </option>

                            <option value="delivered" @selected($selectedOrder->status === 'delivered')>
                                Delivered
                            </option>

                            <option value="cancelled" @selected($selectedOrder->status === 'cancelled')>
                                Cancelled
                            </option>
                        </flux:select>

                    </div>

                </div>

                <div class="flex justify-between border-t pt-4">

                    <span class="font-semibold">
                        Total
                    </span>

                    <span class="text-xl font-bold">
                        ₹{{ number_format($selectedOrder->total_amount, 2) }}
                    </span>

                </div>

            </div>

        @endif

    </flux:modal>
</div>