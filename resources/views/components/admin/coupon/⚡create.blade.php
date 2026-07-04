<?php

use Livewire\Component;

use App\Models\Coupon;
use Illuminate\Support\Carbon;

new class extends Component
{
    public $edit_id;

    public $code;
    public $type;	
    public $value;	
    public $minimum_amount;	
    public $maximum_discount;
    public $usage_limit;		
    public $start_at;	
    public $expires_at;	
    public $is_active;

    public ?string $startDate = null;
    public ?string $startTime = null;

    public ?string $expiresDate = null;
    public ?string $expiresTime = null;

    public function mount(){
        $this->type = "";
        if(!empty($this->edit_id)){
            $selectedCoupon = Coupon::where('id',$this->edit_id)->first();
                $this->code  = $selectedCoupon->code;
                $this->type = $selectedCoupon->type;	
                $this->value = $selectedCoupon->value;	
                $this->minimum_amount = $selectedCoupon->minimum_amount;	
                $this->maximum_discount = $selectedCoupon->maximum_discount;
                $this->usage_limit = $selectedCoupon->usage_limit;		
                $this->expires_at = $selectedCoupon->expire_at;	
                $this->is_active = $selectedCoupon->is_active;

                $dt = Carbon::parse($selectedCoupon->starts_at);
            
                $this->startDate = $dt->format('Y-m-d'); 
                $this->startTime = $dt->format('H:i');
                $this->start_at = Carbon::parse("{$this->startDate} {$this->startTime}")->toDateTimeString();
                
                $dt = Carbon::parse($selectedCoupon->expires_at);

                $this->expiresDate = $dt->format('Y-m-d'); 
                $this->expiresTime = $dt->format('H:i');
                $this->expires_at = Carbon::parse("{$this->expiresDate} {$this->expiresTime}")->toDateTimeString();
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'startTime']) && $this->startDate) {
            $time = $this->startTime ?: '00:00';
            $this->start_at = Carbon::parse("{$this->startDate} {$time}")->toDateTimeString();
        }

        if (in_array($property, ['expiresDate', 'expiresTime']) && $this->expiresDate) {
            $time = $this->expiresTime ?: '00:00';
            $this->expires_at = Carbon::parse("{$this->expiresDate} {$time}")->toDateTimeString();
        }
    }

    public function save(){
        if(empty($this->edit_id)){
            Coupon::create([
                'code' =>  $this->code,
                'type' => $this->type,
                'value' => $this->value,
                'minimum_amount' => $this->minimum_amount,
                'maximum_discount' => $this->maximum_discount,
                'usage_limit' => $this->usage_limit,
                'starts_at' => $this->start_at,
                'expires_at' => $this->expires_at,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('send-Message', "Coupon Added successfully.");
            $this->dispatch('coupon-updated');
        }else{
            if($this->usage_limit == 0){
                $this->usage_limit = null;
            }
            Coupon::where('id', $this->edit_id)->update([
                'code' =>  $this->code,
                'type' => $this->type,
                'value' => $this->value,
                'minimum_amount' => $this->minimum_amount,
                'maximum_discount' => $this->maximum_discount,
                'usage_limit' => $this->usage_limit,
                'starts_at' => $this->start_at,
                'expires_at' => $this->expires_at,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('send-Message', "Coupon Updated successfully.");
            $this->dispatch('coupon-updated');
        }
    }

    public function cancel(){
        $this->dispatch("coupon-updated");
        $this->reset();
    }
};
?>

<div>
    <form wire:submit.prevent="save" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                wire:model="code"
                name="code"
                :label="__('Coupon Code')"
                :value="old('code')"
                type="text"
                required
                autofocus
                autocomplete="code"
                :placeholder="__('Category code...')"
            />
            
            <flux:select required name="type" wire:model.live="type" placeholder="Choose Coupon Type">
                    <flux:select.option value="fixed">fixed</flux:select.option>
                    <flux:select.option value="percentage">percentage</flux:select.option>
            </flux:select>

            <flux:input
                wire:model="value"
                name="value"
                :label="__('Coupon Value')"
                type="number"
                required
                autocomplete="value"
                :placeholder="__('Coupon Value')"
                step="any"
            />

            <flux:input
                wire:model="minimum_amount"
                name="minimum_amount"
                :label="__('Coupon Minimum Amount')"
                type="number"
                required
                autocomplete="minimum_amount"
                :placeholder="__('Coupon Minimum Amount')"
                step="any"
            />

            <flux:input
                wire:model="maximum_discount"
                name="maximum_discount"
                :label="__('Coupon Maximum Discount')"
                type="number"
                autocomplete="maximum_discount"
                :placeholder="__('Coupon Maximum Discount')"
                step="any"
            />

            <flux:input
                wire:model="usage_limit"
                name="usage_limit"
                :label="__('Coupon Usage Limit')"
                type="number"
                autocomplete="Coupon Usage Limit"
                :placeholder="__('Coupon Usage Limit')"
            />

            <flux:field>
                <flux:label>Starts At</flux:label>
                
                <flux:input.group>
                    <!-- Binds to the temporary Date variable -->
                    <flux:input type="date" wire:model.live="startDate" />
            
                    <!-- Binds to the temporary Time variable -->
                    <flux:input type="time" wire:model.live="startTime" />
                </flux:input.group>
            </flux:field>

            <flux:field>
                <flux:label>Expires At</flux:label>

                <flux:input.group>
                    <!-- Binds to the temporary Date variable -->
                            <flux:input type="date" wire:model.live="expiresDate" />

                    <!-- Binds to the temporary Time variable -->
                    <flux:input type="time" wire:model.live="expiresTime" />
                </flux:input.group>
            </flux:field>


            <flux:checkbox wire:model="is_active" name="is_active" :label="__('Activate')" :checked="old('is_active')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="create-button">
                    @if(empty($edit_id))
                        {{ __('Add') }}
                    @else
                        {{__('Update')}}
                    @endif
                </flux:button>

            </div>
            <flux:button wire:click="cancel" variant="danger" type="button" class="w-full">
                {{ __('Cancel') }}
            </flux:button>
        </form>
</div>