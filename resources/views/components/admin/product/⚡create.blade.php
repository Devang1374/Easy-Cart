<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use App\Models\product;

use App\Services\CloudinaryService;

use App\Support\ClearCache;

new class extends Component
{
    use WithFileUploads;

    public $image_file = [];
    public $images = [];

    public $category_id;
    public string $title;
    public string $slug;
    public string $description;
    public float $price;
    public $stock;
    public string $sku;
    public bool $active = true;
    public bool $featured = false;

    public $tamp;

    public $edit_id;
    public $categories;
    public function mount(){
        $this->category_id = "";

        if($this->edit_id){
            $product = product::where('id', $this->edit_id)->first();

            $this->category_id = $product['category_id'];
            $this->title = $product['name'];
            $this->slug = $product['slug'];
            $this->description = $product['description'];
            $this->price = (float)$product['price'];
            $this->stock = $product['stock'];
            $this->sku = $product['sku'];
            $this->active = $product['is_active'];
            $this->featured = $product['featured'];

            $images = $product->images()->get();

            foreach($images as $image){
                $this->images[] = $image['image'];
            }
        }
    }

    public function save(){
        if(!auth()->user()->is_admin){
            return;
        }

        $this->validate([
            'category_id' => 'required',
            'title' => 'required|min:3',
            'description' => 'required|min:3',
            'price' => 'required',
            'stock' => 'required',
            'sku' => 'required',
            'image_file.*' => 'image'
        ]);

        if(empty($this->slug)){
            $this->slug = $this->title;
        }

        if(empty($this->edit_id)){

        $product = product::create([
            "category_id" => $this->category_id,
            "name" => $this->title,
            "slug" => $this->slug,
            "description" => $this->description,
            "price" => $this->price,
            "stock" => $this->stock,
            "sku" => $this->sku,
            "is_active" => $this->active,
            "featured" => $this->featured,
        ]);

        if(!empty($this->image_file)){    
            foreach ($this->image_file as $index => $image) {
                $upload = app(CloudinaryService::class)
                    ->upload($image, 'easycart/products');

                $path = $upload['secure_url'];
                $publicId = $upload['public_id'];

                $product->images()->create([
                    'image' => $path,
                    'image_id' => $publicId,
                    'sort_order' => $index,
                ]);
            }
        }

        ClearCache::clearProduct();
        $this->dispatch("product-updated");
        $this->dispatch("send-message", message:"Product Save Successfully");
        
        }else{

            if($this->image_file){
                $product = product::where('id', $this->edit_id)->first();

                $images = $product->images()->get();

                foreach($images as $img){
                    app(CloudinaryService::class)
                        ->destroy($img['image_id']);
                }

                $product->images()->where('product_id', $this->edit_id)->delete();

                foreach ($this->image_file as $index => $image) {
                    $upload = app(CloudinaryService::class)
                        ->upload($image, 'easycart/products');

                    $path = $upload['secure_url'];
                    $publicId = $upload['public_id'];

                    $product->images()->create([
                        'image' => $path,
                        'image_id' => $publicId,
                        'sort_order' => $index,
                    ]); 
                }
            }
            

            product::where('id', $this->edit_id)->update([
                "category_id" => $this->category_id,
                "name" => $this->title,
                "slug" => $this->slug,
                "description" => $this->description,
                "price" => $this->price,
                "stock" => $this->stock,
                "sku" => $this->sku,
                "is_active" => $this->active,
                "featured" => $this->featured,
            ]); 

            ClearCache::clearProduct();
            $this->dispatch("product-updated");
            $this->dispatch("send-message", message:"Product Update Successfully");
        }
    }

    public function cancel(){
        $this->dispatch("product-updated");
        $this->reset('title', 'price', 'stock', 'slug', 'images', 'image_file', 'description', 'sku', 'active', 'category_id');
    }
};
?>

<div>
    <form wire:submit.prevent="save" class="flex flex-col gap-6">
            @csrf
        
            <flux:select required name="category_id" wire:model.live="category_id" placeholder="Choose Category...">
                @foreach($categories as $category)
                    <flux:select.option value="{{$category['id']}}">{{$category['name']}}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="category_id" />


            <!-- Email Address -->
            <flux:input
                wire:model="title"
                name="title"
                :label="__('Product Title')"
                :value="old('title')"
                type="text"
                required
                autofocus
                autocomplete="title"
                placeholder="Product Title..."
            />
            
            <flux:input
                wire:model="slug"
                name="slug"
                :label="__('Product Slug')"
                type="text"
                autocomplete="slug"
                :placeholder="__('Product Slug')"
            />
            
            <flux:textarea
                wire:model="description"
                name="description"
                :label="__('Product description')"
                type="text"
                required
                autocomplete="description"
                :placeholder="__('Product description')"
            />
            
            <div class="grid grid-cols-2 gap-5">
                <flux:input
                    wire:model="price"
                    name="price"
                    :label="__('Product price')"
                    type="number"
                    required
                    autocomplete="price"
                    :placeholder="__('Product price')"
                    step="any"
                />
                
                <flux:input
                    wire:model="stock"
                    name="stock"
                    :label="__('Product stock')"
                    type="number"
                    required
                    autocomplete="stock"
                    :placeholder="__('Product stock')"
                />
            </div>

            <flux:input
                wire:model="sku"
                name="sku"
                :label="__('Product sku')"
                type="text"
                required
                autocomplete="sku"
                :placeholder="__('Product sku')"
            />
            
            
            <div class="grid grid-cols-2 gap-5"> 
                <flux:checkbox wire:model="active" name="active" :label="__('Activate')" :checked="old('active')" />
                <flux:checkbox wire:model="featured" name="featured" :label="__('Featured')" :checked="old('featured')" />
            </div>

            @if(!empty($images) && !empty($edit_id) && empty($image_file))
                <div class="grid grid-cols-4 gap-4">
                    @foreach($images as $image)
                        <img
                            src="{{ $image }}"
                            class="w-32 h-32 object-cover rounded"
                        >
                    @endforeach
                </div>
            @elseif(!empty($image_file))
                <div class="grid grid-cols-4 gap-4">
                    @foreach($image_file as $image)
                        <img
                            src="{{ $image->temporaryUrl() }}"
                            class="w-32 h-32 object-cover rounded"
                        >
                    @endforeach
                </div>
            @endif

            <div 
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true; progress = 0"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                class="space-y-3"
            >
                <!-- Base Flux Input -->
                <flux:input
                    wire:model="image_file"
                    name="image"
                    :label="__('Product Image')"
                    type="file"
                    :placeholder="__('Product Image...')"
                    multiple
                />
            
                <!-- Reactive Progress Bar Wrapper -->
                <div x-show="isUploading" x-collapse x-cloak class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Uploading file...') }}</span>
                        <span x-text="progress + '%'">0%</span>
                    </div>
                    
                    <!-- Progress Bar Track -->
                    <div class="w-full bg-zinc-200/60 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <!-- Progress Bar Fill (Matches Flux Accent Color) -->
                        <div 
                            class="bg-zinc-900 dark:bg-white h-1.5 rounded-full transition-all duration-150 ease-out" 
                            :style="`width: ${progress}%`"
                        ></div>
                    </div>
                </div>
            </div>

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