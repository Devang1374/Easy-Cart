<?php

use Livewire\Component;
use Livewire\Attributes\On; 
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

use App\Models\Category;
use App\Models\product;

use Illuminate\Support\Facades\Storage;

use App\Services\CloudinaryService;

use App\Support\ClearCache;

new class extends Component
{
    use WithPagination;

    public $categories;
    #[on('product-updated')]
    public function mount(){
        $this->message = "";
        $this->create = false;
        $this->categories = Category::latest()->get();
    }

    public $selectedCategory = "";
    public string $search = "";
    #[Computed]
    public function categorys(){
        return Category::latest()
            // 1. Filter categories by selected category name
            ->where('name', 'like', "%{$this->selectedCategory}%")

            // 2. Filter categories to only those containing matching products
            ->when($this->search, function($query) {
                $query->whereHas('product', function($q) {
                    $q->where(function($innerQuery) {
                        $innerQuery->where('name', 'like', "%{$this->search}%")
                                   ->orWhere('slug', 'like', "%{$this->search}%");
                    });
                });
            })

            // 3. FIX: Filter the actual loaded products to ONLY show matching ones
            ->with(['product' => function($q) {
                $q->when($this->search, function($query) {
                    $query->where(function($innerQuery) {
                        $innerQuery->where('name', 'like', "%{$this->search}%")
                                   ->orWhere('slug', 'like', "%{$this->search}%");
                    });
                });
            }])
            ->paginate(10);
    }
    
    public string $message;
    #[on('send-message')]
    public function handleMessage($message){
        $this->message = $message;
    }
    
    public $edit_id;
    public bool $create;
    public function showCreate($id = ""){
        $this->edit_id = $id;
        $this->create = !$this->create;
    }

    public function delete($id){
        if(!auth()->user()->is_admin){
            return;
        }
        
        $product = product::where('id', $id)->first();
        $images = $product->images()->get();
        
        foreach($images as $image){
            app(CloudinaryService::class)
                ->destroy($image['image_id']);
            
        }

        product::where('id', $id)->delete();
        ClearCache::clearProduct();
        $this->message = "Product Deleted Successfully";
    }
};
?>

<div class="relative w-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 md:p-5">
    @if($create)
        <livewire:admin.product.create :edit_id="$edit_id" :categories="$categories" />
    @else

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
        class="fixed bottom-5 right-5 z-50 max-w-sm w-[calc(100%-2.5rem)]"
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
        <div class="grid grid-cols-1 gap-3 w-full sm:grid-cols-2 sm:max-w-xl">
            <flux:input wire:model.live="search" name="Search" type="text" placeholder="Search Products..." class="w-full"/>
            <flux:select required name="selectedCategory" wire:model.live="selectedCategory" placeholder="Choose Category..." class="w-full">
                <flux:select.option value="">All Categories</flux:select.option>
                @foreach($categories as $category)
                    <flux:select.option value="{{$category['name']}}">{{$category['name']}}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="category_id" />
        </div>

        <flux:button wire:click="showCreate" variant="primary" type="button" class="w-full sm:w-auto shrink-0" data-test="Add-button">
            {{ __('Add') }}
        </flux:button>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-2 overflow-hidden">
        <flux:table scrollable container:class="max-h-115 w-full" :paginate="$this->categorys">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                <flux:table.column>Category</flux:table.column>
                <flux:table.column sticky>Product Name</flux:table.column>
                <flux:table.column>Slug</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Price</flux:table.column>
                <flux:table.column>Stock</flux:table.column>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Featured</flux:table.column>
                <flux:table.column>Images</flux:table.column>
                <flux:table.column>Remove</flux:table.column>
                <flux:table.column>Update</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->categorys as $category)
                @foreach($category['product'] as $product)
                
                <flux:table.row class="{{ $loop->first ? 'border-t border-neutral-200 dark:border-neutral-800' : 'border-t-0' }}">
                    
                    <flux:table.cell 
                        class="bg-white dark:bg-zinc-900 border-r border-neutral-200 dark:border-neutral-700 font-bold text-neutral-800 dark:text-neutral-200 whitespace-nowrap px-4 text-center"
                    >
                        @if($loop->first)
                            {{ $category['name'] }}
                        @endif
                    </flux:table.cell>

                    <flux:table.cell sticky class="whitespace-normal min-w-40 max-w-50">{{$product['name']}}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{$product['slug']}}</flux:table.cell>
                    <flux:table.cell class="whitespace-normal min-w-60 max-w-70 text-sm">{{$product['description']}}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap font-medium">₹{{ number_format($product['price'], 2) }}</flux:table.cell>
                    <flux:table.cell>{{$product['stock']}}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs font-mono">{{$product['sku']}}</flux:table.cell>
                    
                    <flux:table.cell>
                        <flux:badge color="{{ $product['is_active'] ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                            {{ $product['is_active'] ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge color="{{ $product['featured'] ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                            {{ $product['featured'] ? 'Yes' : 'No' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell variant="strong" class="whitespace-nowrap min-w-[200px]">
                        @if(!empty($product['images']))
                            <div class="flex flex-row gap-2 overflow-x-auto py-1">
                            @foreach($product['images'] as $image)
                                <img class="h-12 w-12 rounded-lg border border-neutral-200 object-cover shrink-0 dark:border-neutral-700" src="{{ $image['image'] }}" alt="product-image">
                            @endforeach
                            </div>
                        @else
                            <span class="text-zinc-400 text-xs">No Images</span>
                        @endif
                    </flux:table.cell>
                    
                    <flux:table.cell variant="strong">
                        <flux:button wire:click="delete({{$product['id']}})" variant="danger" size="sm">Delete</flux:button>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        <flux:button wire:click="showCreate({{$product['id']}})" variant="primary" color="blue" size="sm">Edit</flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @endif
</div>