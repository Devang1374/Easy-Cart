<?php

use Livewire\Component;

use App\Models\orderTable;
use Livewire\WithPagination;

use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    public $status = 'all';

    public function getReturnOrdersProperty()
    {
        return OrderTable::with(['user', 'items.product.images'])
            ->where('return_requested', true)
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('return_requested_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()]);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    // Search order table
                    $subQuery
                        ->where('order_number', 'like', '%' . $this->search . '%')
                        // Search related user table
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== 'all', fn($query) => $query->where('return_status', $this->status))
            ->latest('return_requested_at')
            ->paginate(10);
    }

    public $startDate;
    public $endDate;
    public function updatingStartDate()
    {
        $this->resetPage();
    }
    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function approve($id)
    {
        $order = orderTable::findOrFail($id);

        $order->update([
            'return_status' => 'approved',
            'return_admin_note' => $this->adminNote,
        ]);

        $this->selectedOrder = $order->fresh();

        Flux::toast(variant: 'success', heading: 'Success', text: 'Return request approved.');
    }

    public function reject($id)
    {
        $order = orderTable::findOrFail($id);

        $order->update([
            'return_status' => 'rejected',
            'return_admin_note' => $this->adminNote,
        ]);

        $this->selectedOrder = $order->fresh();

        Flux::toast(variant: 'warning', heading: 'Rejected', text: 'Return Request Rejected');
    }

    public function received($id)
    {
        $order = orderTable::findOrFail($id);

        $order->update([
            'status' => 'Received',
            'return_status' => 'received',
            'return_admin_note' => $this->adminNote,
        ]);

        $this->selectedOrder = $order->fresh();

        Flux::toast(variant: 'warning', heading: 'Received', text: 'Return Request Received');
    }

    public function refunded($id)
    {
        $order = orderTable::with('items.product')->findOrFail($id);

        // Prevent duplicate refund
        if ($order->return_status === 'refunded') {
            Flux::toast(variant: 'warning', heading: 'Already Refunded', text: 'This return has already been processed.');

            return;
        }

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        // Update order
        $order->update([
            'status' => 'Returned',
            'pyment' => 'Refunded',
            'return_status' => 'refunded',
            'return_admin_note' => $this->adminNote,
            'return_completed_at' => now(),
        ]);

        $this->selectedOrder = $order->fresh();

        Flux::toast(variant: 'success', heading: 'Refund Completed', text: 'Stock restored and return completed successfully.');
    }

    public string $adminNote = '';
    public $selectedOrder = null;
    public bool $showViewModal = false;
    public function showDetails($id)
    {
        $this->selectedOrder = orderTable::with(['user', 'items.product.images'])->findOrFail($id);
        $this->adminNote = $this->selectedOrder->return_admin_note ?? '';
        $this->showViewModal = true;
    }

    public string $search = '';
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public ?DateRange $dateRange = null;
    public function updatingDateRange()
    {
        $this->resetPage();
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Categories..." />
        </div>

        <div class="flex items-center gap-4">
            <!-- Start Date Input -->
            <flux:input type="date" wire:model.live="startDate" label="From Date" max="{{ now()->format('Y-m-d') }}" />

            <!-- Decorative Spacer/Arrow -->
            <span class="text-gray-400 mt-5">to</span>

            <!-- End Date Input -->
            <flux:input type="date" wire:model.live="endDate" label="To Date" max="{{ now()->format('Y-m-d') }}" />
        </div>

        <div class="flex flex-wrap gap-2 overflow-x-auto max-w-full pb-1 sm:pb-0">

            <flux:button wire:click="$set('status', 'all')" variant="{{ $status === 'all' ? 'primary' : 'ghost' }}">
                All
            </flux:button>

            <flux:button wire:click="$set('status', 'requested')"
                variant="{{ $status === 'requested' ? 'primary' : 'ghost' }}">
                Requested
            </flux:button>

            <flux:button wire:click="$set('status', 'rejected')"
                variant="{{ $status === 'rejected' ? 'primary' : 'ghost' }}">
                Rejected
            </flux:button>

            <flux:button wire:click="$set('status', 'received')"
                variant="{{ $status === 'received' ? 'primary' : 'ghost' }}">
                Received
            </flux:button>

            <flux:button wire:click="$set('status', 'refunded')"
                variant="{{ $status === 'refunded' ? 'primary' : 'ghost' }}">
                Refunded
            </flux:button>

        </div>
    </div>

    <flux:table scrollable container:class="w-full" :paginate="$this->returnOrders">

        <flux:table.columns>
            <flux:table.column>Order</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Requested</flux:table.column>
            <flux:table.column>Reason</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>

            @foreach ($this->returnOrders as $order)
                <flux:table.row>

                    {{-- Order --}}
                    <flux:table.cell variant="strong">
                        {{ $order->order_number }}

                        <div class="text-xs text-zinc-500">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </div>
                    </flux:table.cell>

                    {{-- Customer --}}
                    <flux:table.cell>

                        <div class="font-medium">
                            {{ $order->user->name }}
                        </div>

                        <div class="text-xs text-zinc-500">
                            {{ $order->user->email }}
                        </div>

                    </flux:table.cell>

                    {{-- Requested Date --}}
                    <flux:table.cell>

                        @if ($order->return_requested_at)
                            {{ $order->return_requested_at->format('d M Y') }}

                            <div class="text-xs text-zinc-500">
                                {{ $order->return_requested_at->format('h:i A') }}
                            </div>
                        @endif

                    </flux:table.cell>

                    {{-- Reason --}}
                    <flux:table.cell>

                        <div class="max-w-xs truncate">
                            {{ $order->return_reason }}
                        </div>

                    </flux:table.cell>

                    {{-- Status --}}
                    <flux:table.cell>

                        @switch($order->return_status)
                            @case('requested')
                                <flux:badge color="yellow">
                                    Requested
                                </flux:badge>
                            @break

                            @case('approved')
                                <flux:badge color="blue">
                                    Approved
                                </flux:badge>
                            @break

                            @case('received')
                                <flux:badge color="purple">
                                    Received
                                </flux:badge>
                            @break

                            @case('refunded')
                                <flux:badge color="green">
                                    Refunded
                                </flux:badge>
                            @break

                            @case('rejected')
                                <flux:badge color="red">
                                    Rejected
                                </flux:badge>
                            @break

                            @default
                                <flux:badge color="zinc">
                                    None
                                </flux:badge>
                        @endswitch

                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell>

                        <flux:button wire:click="showDetails({{ $order->id }})" size="sm" variant="ghost">
                            View
                        </flux:button>

                    </flux:table.cell>

                </flux:table.row>
            @endforeach

        </flux:table.rows>

    </flux:table>

    <flux:modal wire:model="showViewModal" class="max-w-5xl">

        @if ($selectedOrder)

            <div class="space-y-8">

                <div>
                    <h2 class="text-2xl font-bold">
                        Return Request
                    </h2>

                    <p class="text-zinc-500">
                        Order #{{ $selectedOrder->order_number }}
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- Customer --}}
                    <div class="rounded-2xl border p-5">

                        <h3 class="font-bold mb-4">
                            Customer Information
                        </h3>

                        <div class="space-y-2">

                            <div>
                                <span class="text-zinc-500">Name:</span>
                                <div>{{ $selectedOrder->user->name }}</div>
                            </div>

                            <div>
                                <span class="text-zinc-500">Email:</span>
                                <div>{{ $selectedOrder->user->email }}</div>
                            </div>

                        </div>

                    </div>

                    {{-- Return --}}
                    <div class="rounded-2xl border p-5">

                        <h3 class="font-bold mb-4">
                            Return Details
                        </h3>

                        <div class="space-y-3">

                            <div>
                                <span class="text-zinc-500">Status</span>

                                <div class="mt-1">

                                    @switch($selectedOrder->return_status)
                                        @case('requested')
                                            <flux:badge color="yellow">
                                                Requested
                                            </flux:badge>
                                        @break

                                        @case('approved')
                                            <flux:badge color="blue">
                                                Approved
                                            </flux:badge>
                                        @break

                                        @case('received')
                                            <flux:badge color="purple">
                                                Received
                                            </flux:badge>
                                        @break

                                        @case('refunded')
                                            <flux:badge color="green">
                                                Refunded
                                            </flux:badge>
                                        @break

                                        @case('rejected')
                                            <flux:badge color="red">
                                                Rejected
                                            </flux:badge>
                                        @break

                                        @default
                                            <flux:badge color="zinc">
                                                None
                                            </flux:badge>
                                    @endswitch

                                </div>
                            </div>

                            <div>

                                <span class="text-zinc-500">
                                    Requested At
                                </span>

                                <div>
                                    {{ $selectedOrder->return_requested_at }}
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Reason --}}
                <div class="rounded-2xl border p-5">

                    <h3 class="font-bold mb-3">
                        Return Reason
                    </h3>

                    <p class="text-zinc-600 dark:text-zinc-300 whitespace-pre-line">
                        {{ $selectedOrder->return_reason }}
                    </p>

                </div>

                {{-- admin repons --}}
                <div class="rounded-2xl border p-5">

                    <h3 class="mb-3 font-bold">
                        Admin Note
                    </h3>

                    <flux:textarea wire:model="adminNote" rows="5"
                        placeholder="Write internal notes about this return..." />

                </div>

                {{-- Products --}}
                <div>

                    <h3 class="mb-4 text-xl font-bold">
                        Products
                    </h3>

                    <div class="space-y-4">

                        @foreach ($selectedOrder->items as $item)
                            <div class="flex items-center gap-4 rounded-2xl border p-4">

                                @if (isset($item->product->images[0]))
                                    <img src="{{ $item->product->images[0]->image }}"
                                        class="h-20 w-20 rounded-xl object-cover">
                                @endif

                                <div class="flex-1">

                                    <div class="font-semibold">
                                        {{ $item->product_name }}
                                    </div>

                                    <div class="text-sm text-zinc-500">
                                        Qty: {{ $item->quantity }}
                                    </div>

                                </div>

                                <div class="font-bold">
                                    ₹{{ number_format($item->subtotal, 2) }}
                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>

            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t pt-6">

                @if ($selectedOrder->return_status === 'requested')
                    <flux:button wire:click="approve({{ $selectedOrder->id }})" variant="primary">
                        Approve Request
                    </flux:button>

                    <flux:button wire:click="reject({{ $selectedOrder->id }})" variant="danger">
                        Reject Request
                    </flux:button>
                @elseif($selectedOrder->return_status === 'approved')
                    <flux:button wire:click="received({{ $selectedOrder->id }})" variant="primary">
                        Mark as Received
                    </flux:button>
                @elseif($selectedOrder->return_status === 'received')
                    <flux:button wire:click="refunded({{ $selectedOrder->id }})" variant="primary">
                        Mark as Refunded
                    </flux:button>
                @endif

                <flux:button wire:click="$set('showViewModal', false)" variant="ghost">
                    Close
                </flux:button>

            </div>
        @endif
    </flux:modal>
</div>
