<?php

use Livewire\Component;

use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\Banner;

use App\Services\CloudinaryService;

new class extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $subtitle = '';

    public string $button_text = '';
    public string $button_link = '';

    public string $secondary_button_text = '';
    public string $secondary_button_link = '';

    public $desktop_image;
    public $mobile_image;

    public $tampDesktop_image;
    public $tampMobile_image;

    public $background_image;
    public $tampBackground_image;

    public string $background_type = 'gradient';
    public string $background_color = '#2563eb';

    public string $position = 'hero';
    public int $sort_order = 1;

    public bool $is_active = true;

    public $starts_at;
    public $expires_at;

    public $startDate;
    public $startTime;

    public $expireDate;
    public $expireTime;

    public $edit_id;
    public function mount(){
        if(!empty($this->edit_id)){
            $banner = Banner::where('id', $this->edit_id)->first();
            
            $this->title = $banner->title;
            $this->subtitle = $banner->subtitle;
            $this->button_text = $banner->button_text;
            $this->button_link = $banner->button_link;
            $this->secondary_button_text = $banner->secondary_button_text;
            $this->secondary_button_link = $banner->secondary_button_link;
            $this->tampDesktop_image = $banner->desktop_image;
            $this->tampMobile_image = $banner->mobile_image;
            $this->background_type = $banner->background_type;
            $this->background_color = $banner->background_color;
            $this->position = $banner->position;
            $this->sort_order = $banner->sort_order;
            $this->is_active = $banner->is_active;
            $this->tampBackground_image = $banner->background_image;

            $dt = Carbon::parse($banner->starts_at);
            
            $this->startDate = $dt->format('Y-m-d'); 
            $this->startTime = $dt->format('H:i');
            $this->start_at = Carbon::parse("{$this->startDate} {$this->startTime}")->toDateTimeString();
            
            $dt = Carbon::parse($banner->expires_at);

            $this->expireDate = $dt->format('Y-m-d'); 
            $this->expireTime = $dt->format('H:i');
            $this->expires_at = Carbon::parse("{$this->expireDate} {$this->expireTime}")->toDateTimeString();
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate','startTime']) && $this->startDate) {

            $time = $this->startTime ?: '00:00';

            $this->starts_at = Carbon::parse(
                "{$this->startDate} {$time}"
            )->toDateTimeString();
        }

        if (in_array($property, ['expireDate','expireTime']) && $this->expireDate) {

            $time = $this->expireTime ?: '23:59';

            $this->expires_at = Carbon::parse(
                "{$this->expireDate} {$time}"
            )->toDateTimeString();
        }
    }

    protected function rules()
    {
        return [

            'title' => 'required|max:255',

            'subtitle' => 'nullable',

            'button_text' => 'nullable|max:100',
            'button_link' => 'nullable|max:255',

            'secondary_button_text' => 'nullable|max:100',
            'secondary_button_link' => 'nullable|max:255',

            'desktop_image' => 'nullable|image|max:2048',
            'mobile_image' => 'nullable|image|max:2048',

            'background_type' => 'required',

            'background_color' => 'nullable',

            'position' => 'required',

            'sort_order' => 'required|integer|min:1',

            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'background_image' => 'nullable|image',
        ];
    }

    public function save()
    {
        $this->validate();

        if(!empty($this->desktop_image)){
            $upload = app(CloudinaryService::class)
                ->upload($this->desktop_image, 'easycart/banners');

            $desktop_image_path = $upload['secure_url'];
            $desktop_image_id = $upload['public_id'];
        }
        
        if(!empty($this->mobile_image)){
            $upload = app(CloudinaryService::class)
                ->upload($this->mobile_image, 'easycart/banners');

            $mobile_image_path = $upload['secure_url'];
            $mobile_image_id = $upload['public_id'];
        }
        
        if(!empty($this->background_image)){
            $upload = app(CloudinaryService::class)
                ->upload($this->background_image, 'easycart/banners');

            $background_image_path = $upload['secure_url'];
            $background_image_id = $upload['public_id'];
        }

        if(empty($this->edit_id)){
            Banner::create([
    
                'title' => $this->title,
                'subtitle' => $this->subtitle,
    
                'button_text' => $this->button_text,
                'button_link' => $this->button_link,
    
                'secondary_button_text' => $this->secondary_button_text,
                'secondary_button_link' => $this->secondary_button_link,
    
                'desktop_image_id' => $desktop_image_id,
                'desktop_image' => $desktop_image_path,
    
                'mobile_image' => $mobile_image_path,
                'mobile_image_id' => $mobile_image_id,
    
                'background_type' => $this->background_type,
    
                'background_color' => $this->background_color,
    
                'position' => $this->position,
    
                'sort_order' => $this->sort_order,
    
                'is_active' => $this->is_active,
    
                'starts_at' => $this->starts_at,
                'expires_at' => $this->expires_at,

                'background_image' => $background_image_path,
                'background_image_id' => $background_image_id,
    
            ]);
    
            $this->dispatch('banner-updated');
            $this->dispatch(
                'send-message',
                'Banner created successfully.'
            );
        }else{
            if(!empty($this->desktop_image)){
                $oldPath = Banner::where('id', $this->edit_id)->value('desktop_image_id');
                
                app(CloudinaryService::class)
                    ->destroy($oldPath);

                Banner::where('id', $this->edit_id)->update([
                    'desktop_image' => $desktop_image_path,
                    'desktop_image_id' => $desktop_image_id,
                ]);
            }

            if(!empty($this->mobile_image)){
                $oldPath = Banner::where('id', $this->edit_id)->value('mobile_image_id');
                
                app(CloudinaryService::class)
                    ->destroy($oldPath);
                
                Banner::where('id', $this->edit_id)->update([
                    'mobile_image' => $mobile_image_path,
                    'mobile_image_id' => $mobile_image_id,
                ]);
            }

            if (!empty($this->background_image)) {

                $oldPath = Banner::where('id', $this->edit_id)
                    ->value('background_image_id');

                app(CloudinaryService::class)
                    ->destroy($oldPath);

                Banner::where('id', $this->edit_id)->update([
                    'background_image' => $background_image_path,
                    'background_image_id' => $background_image_id
                ]);
            }

            Banner::where('id', $this->edit_id)->update([
    
                'title' => $this->title,
                'subtitle' => $this->subtitle,
    
                'button_text' => $this->button_text,
                'button_link' => $this->button_link,
    
                'secondary_button_text' => $this->secondary_button_text,
                'secondary_button_link' => $this->secondary_button_link,
                
                'background_type' => $this->background_type,
    
                'background_color' => $this->background_color,
    
                'position' => $this->position,
    
                'sort_order' => $this->sort_order,
    
                'is_active' => $this->is_active,
    
                'starts_at' => $this->starts_at,
                'expires_at' => $this->expires_at,
    
            ]);
        }

        $this->reset();
        $this->dispatch('banner-update');
    }

    public function cancel(){
        $this->reset();
        $this->dispatch('banner-update');
    }
};
?>

<div>
    <form wire:submit.prevent="save" class="flex flex-col gap-6">
        @csrf

        {{-- ================= Banner Content ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Banner Content
            </h3>

            <div class="space-y-5">

                <flux:input
                    wire:model="title"
                    label="Banner Title"
                    placeholder="Mega Summer Sale"
                />

                <flux:textarea
                    wire:model="subtitle"
                    label="Subtitle"
                    placeholder="Up to 70% OFF on selected products."
                />

            </div>
        </div>

        {{-- ================= Buttons ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Buttons
            </h3>

            <div class="grid gap-5 md:grid-cols-2">

                <flux:input
                    wire:model="button_text"
                    label="Primary Button Text"
                    placeholder="Shop Now"
                />

                <flux:input
                    wire:model="button_link"
                    label="Primary Button Link"
                    placeholder="/user/product"
                />

                <flux:input
                    wire:model="secondary_button_text"
                    label="Secondary Button Text"
                    placeholder="Learn More"
                />

                <flux:input
                    wire:model="secondary_button_link"
                    label="Secondary Button Link"
                    placeholder="/about"
                />

            </div>
        </div>

        {{-- ================= Images ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Banner Images
            </h3>

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <flux:input
                        wire:model="desktop_image"
                        type="file"
                        label="Desktop Image"
                    />

                    @if($desktop_image)
                        <img
                            src="{{ $desktop_image->temporaryUrl() }}"
                            class="mt-4 h-48 w-full rounded-xl border object-cover"
                        >
                    @elseif($tampDesktop_image && empty($desktop_image))
                        <img
                            src="{{ $tampDesktop_image }}"
                            class="mt-4 h-48 w-full rounded-xl border object-cover"
                        >
                    @endif
                </div>

                <div>
                    <flux:input
                        wire:model="mobile_image"
                        type="file"
                        label="Mobile Image"
                    />

                    @if($mobile_image)
                        <img
                            src="{{ $mobile_image->temporaryUrl() }}"
                            class="mt-4 h-48 w-full rounded-xl border object-cover"
                        >
                    @elseif($tampMobile_image && empty($mobile_image))
                        <img
                            src="{{ $tampMobile_image }}"
                            class="mt-4 h-48 w-full rounded-xl border object-cover"
                        >
                    @endif
                </div>

            </div>
        </div>

        {{-- ================= Appearance ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Appearance
            </h3>

            <div class="grid gap-5 md:grid-cols-2">

                <flux:select
                    wire:model.live="background_type"
                    label="Background Type"
                >
                    <flux:select.option value="gradient">
                        Gradient
                    </flux:select.option>

                    <flux:select.option value="solid">
                        Solid Color
                    </flux:select.option>

                    <flux:select.option value="image">
                        Background Image
                    </flux:select.option>

                    <flux:select.option value="gradient-image">
                        Gradient + Background Image
                    </flux:select.option>
                </flux:select>

                @if(in_array($background_type, ['gradient', 'solid', 'gradient-image']))
                    <flux:input
                        wire:model.live="background_color"
                        type="color"
                        label="Background Color"
                    />
                @endif

                @if(in_array($background_type, ['image', 'gradient-image']))

                    <div class="md:col-span-2">

                        <flux:input
                            wire:model="background_image"
                            type="file"
                            label="Background Image"
                        />

                        @if($background_image)

                            <img
                                src="{{ $background_image->temporaryUrl() }}"
                                class="mt-4 h-52 w-full rounded-xl border object-cover"
                            >

                        @elseif($tampBackground_image)

                            <img
                                src="{{ $tampBackground_image }}"
                                class="mt-4 h-52 w-full rounded-xl border object-cover"
                            >

                        @endif

                    </div>

                @endif

            </div>
        </div>

        {{-- ================= Placement ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Placement
            </h3>

            <div class="grid gap-5 md:grid-cols-2">

                <flux:select
                    wire:model="position"
                    label="Banner Position"
                >
                    <flux:select.option value="hero">
                        Hero Banner
                    </flux:select.option>

                    <flux:select.option value="middle">
                        Middle Banner
                    </flux:select.option>

                    <flux:select.option value="bottom">
                        Bottom Banner
                    </flux:select.option>

                    <flux:select.option value="popup">
                        Popup
                    </flux:select.option>
                </flux:select>

                <flux:input
                    wire:model="sort_order"
                    type="number"
                    label="Display Order"
                />

            </div>
        </div>

        {{-- ================= Schedule ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="mb-5 text-lg font-semibold">
                Schedule
            </h3>

            <div class="grid gap-6 md:grid-cols-2">

                <div class="space-y-3">

                    <label class="text-sm font-medium">
                        Starts At
                    </label>

                    <div class="grid grid-cols-2 gap-3">

                        <flux:input
                            wire:model.live="startDate"
                            type="date"
                        />

                        <flux:input
                            wire:model.live="startTime"
                            type="time"
                        />

                    </div>

                </div>

                <div class="space-y-3">

                    <label class="text-sm font-medium">
                        Expires At
                    </label>

                    <div class="grid grid-cols-2 gap-3">

                        <flux:input
                            wire:model.live="expireDate"
                            type="date"
                        />

                        <flux:input
                            wire:model.live="expireTime"
                            type="time"
                        />

                    </div>

                </div>

            </div>
        </div>

        {{-- ================= Status ================= --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">

            <flux:checkbox
                wire:model="is_active"
                label="Banner Active"
            />

        </div>

        {{-- ================= Buttons ================= --}}
        <div class="space-y-3">

            <flux:button
                variant="primary"
                type="submit"
                class="w-full"
            >
                @if(empty($edit_id))
                    Create Banner
                @else
                    Update Banner
                @endif
            </flux:button>

            <flux:button
                wire:click="cancel"
                variant="danger"
                type="button"
                class="w-full"
            >
                Cancel
            </flux:button>

        </div>

    </form>
</div>