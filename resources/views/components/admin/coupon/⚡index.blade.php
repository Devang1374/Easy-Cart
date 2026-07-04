<?php

use Livewire\Component;

use Livewire\Attributes\On;  
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

use App\Models\Coupon;

new class extends Component
{
    use WithPagination;

    public $create;
    #[on('coupon-updated')]
    public function mount(){
        $this->create = false;
        $this->edit_id = "";
    }

    #[Computed]
    public function Coupons(){
       return Coupon::query()
            ->latest()

            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })

            ->when($this->status, function ($query) {

                switch ($this->status) {

                    case 'active':
                        $query->where('is_active', true)
                            ->where(function ($q) {
                                $q->whereNull('starts_at')
                                  ->orWhere('starts_at', '<=', now());
                            })
                            ->where(function ($q) {
                                $q->whereNull('expires_at')
                                  ->orWhere('expires_at', '>=', now());
                            })
                            ->where(function ($q) {
                                $q->whereNull('usage_limit')
                                  ->orWhereColumn('used_count', '<', 'usage_limit');
                            });
                        break;
                    
                    case 'upcoming':
                        $query->whereNotNull('starts_at')
                            ->where('starts_at', '>', now());
                        break;
                    
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    
                    case 'expired':
                        $query->whereNotNull('expires_at')
                            ->where('expires_at', '<', now());
                        break;
                    
                    case 'limitReached':
                        $query->whereNotNull('usage_limit')
                            ->whereColumn('used_count', '>=', 'usage_limit');
                        break;
                }
    })

    ->when($this->search, function ($query) {
        $query->where(function ($subQuery) {
            $subQuery->where('code', 'like', "%{$this->search}%")
                ->orWhere('value', 'like', "%{$this->search}%");
        });
    })

    ->paginate(10);
    }

    public string $search = "";
    public string $type = "";
    public string $status = "";
    public function updatingSearch(){
        $this->resetPage();
    }

    public $message;
    #[on('send-message')]
    public function handleMessage($message){
        $this->message = $message;
    }

    public function delete($id){
        coupon::where('id', $id)->delete();
        $this->message = "coupon Deleted Successfully";
    }

    public $edit_id;
    public function showCreate($edit_id = ""){
        $this->edit_id = $edit_id;
        $this->create = true;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->type = '';
        $this->status = '';

        $this->resetPage();
    }
};
?>

<div class="relative h-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
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

    @if($create)
        <livewire:admin.coupon.create :edit_id="$edit_id" />
    @else

    <div class="flex flex-row justify-between rounded-xl border border-neutral-200 dark:border-neutral-700">
       <div class="grid grid-cols-4 gap-4">
           <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Coupons..."/>

            <flux:select required name="type" wire:model.live="type">
                <flux:select.option value="">All Types...</flux:select.option>
                <flux:select.option value="fixed">fixed</flux:select.option>
                <flux:select.option value="percentage">percentage</flux:select.option>
            </flux:select>

            <flux:select required name="status" wire:model.live="status">
                <flux:select.option value="">All Status...</flux:select.option>
                <flux:select.option value="active">Active</flux:select.option>
                <flux:select.option value="upcoming">Upcoming</flux:select.option>
                <flux:select.option value="inactive">Inactive</flux:select.option>
                <flux:select.option value="expired">Expired</flux:select.option>
                <flux:select.option value="limitReached">Limit Reached</flux:select.option>
            </flux:select>

            <flux:button
                wire:click="resetFilters"
                variant="ghost"
                icon="arrow-path"
            />
       </div>

        <flux:button wire:click="showCreate" variant="primary" type="button" class="" data-test="Add-button">
                {{ __('Add') }}
        </flux:button>
    </div>

    <div class="relative h-full flex flex-row justify-between overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-2">
        <flux:table scrollable container:class="w-full" :paginate="$this->coupons">
        
            <flux:table.columns>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Discount</flux:table.column>
                <flux:table.column>Min Order</flux:table.column>
                <flux:table.column>Usage</flux:table.column>
                <flux:table.column>Validity</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
        
            <flux:table.rows>
        
                @foreach($this->coupons as $coupon)
        
                    <flux:table.row>
        
                        {{-- Coupon Code --}}
                        <flux:table.cell variant="strong">
                            {{ $coupon->code }}
                        </flux:table.cell>
        
                        {{-- Type --}}
                        <flux:table.cell>
                            @if($coupon->type === 'percentage')
                                <flux:badge color="blue" size="sm">
                                    Percentage
                                </flux:badge>
                            @else
                                <flux:badge color="purple" size="sm">
                                    Fixed
                                </flux:badge>
                            @endif
                        </flux:table.cell>
        
                        {{-- Discount --}}
                        <flux:table.cell>
        
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                ₹{{ number_format($coupon->value,2) }}
                            @endif
        
                            @if($coupon->maximum_discount)
                                <div class="text-xs text-zinc-500">
                                    Max ₹{{ number_format($coupon->maximum_discount,2) }}
                                </div>
                            @endif
        
                        </flux:table.cell>
        
                        {{-- Minimum Order --}}
                        <flux:table.cell>
                            ₹{{ number_format($coupon->minimum_amount,2) }}
                        </flux:table.cell>
        
                        {{-- Usage --}}
                        <flux:table.cell>
        
                            {{ $coupon->used_count }}
        
                            @if($coupon->usage_limit)
        
                                / {{ $coupon->usage_limit }}
        
                            @else
        
                                / ∞
        
                            @endif
        
                        </flux:table.cell>
        
                        {{-- Validity --}}
                        <flux:table.cell>
        
                            <div class="text-sm">
        
                                @if($coupon->starts_at)
                                    {{ $coupon->starts_at}}
                                @else
                                    —
                                @endif
        
                            </div>
        
                            <div class="text-xs text-zinc-500">
        
                                @if($coupon->expires_at)
                                    Until {{ $coupon->expires_at }}
                                @else
                                    Never Expires
                                @endif
        
                            </div>
        
                        </flux:table.cell>
        
                        {{-- Status --}}
                        <flux:table.cell>
        
                            @if(!$coupon->is_active)
        
                                <flux:badge color="zinc" size="sm">
                                    Inactive
                                </flux:badge>
        
                            @elseif($coupon->expires_at && now()->gt($coupon->expires_at))
        
                                <flux:badge color="red" size="sm">
                                    Expired
                                </flux:badge>
        
                            @elseif($coupon->starts_at && now()->lt($coupon->starts_at))
        
                                <flux:badge color="yellow" size="sm">
                                    Upcoming
                                </flux:badge>
        
                            @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
        
                                <flux:badge color="orange" size="sm">
                                    Limit Reached
                                </flux:badge>
        
                            @else
        
                                <flux:badge color="green" size="sm">
                                    Active
                                </flux:badge>
        
                            @endif
        
                        </flux:table.cell>
        
                        {{-- Actions --}}
                        <flux:table.cell>
        
                            <div class="flex gap-2">
        
                                <flux:button
                                    wire:click="showCreate({{ $coupon->id }})"
                                    variant="primary"
                                    size="sm"
                                >
                                    Edit
                                </flux:button>
        
                                <flux:button
                                    wire:click="delete({{ $coupon->id }})"
                                    wire:confirm="Delete this coupon?"
                                    variant="danger"
                                    size="sm"
                                >
                                    Delete
                                </flux:button>
        
                            </div>
        
                        </flux:table.cell>
        
                    </flux:table.row>
        
                @endforeach
        
            </flux:table.rows>
        
        </flux:table>
    </div>
    @endif
</div>