<?php

use Livewire\Component;

use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;

use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $categories;
    public $category;
    public array $wishlist = [];
    
    public function mount()
    {
       $this->categories = Category::query()
        ->where('is_active', true)
        ->get();

        if($this->category){
            $this->selectedCategory = $this->category;
            $this->loadProducts();
        }

        if (auth()->check()) {
            $this->wishlist = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

    }

    #[Computed]
    public function loadProducts()
    {
        $query = Product::query()
            ->with('images', 'category')
            ->where('is_active', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        switch ($this->sort) {
            case 'price_low':
                $query->orderBy('price');
                break;
            
            case 'price_high':
                $query->orderByDesc('price');
                break;
            
            case 'name_asc':
                $query->orderBy('name');
                break;
            
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            
            default:
                $query->latest();
        }

        return $query
            ->latest()->paginate(12);
    }

    public $selectedCategory = null;
    public function selectCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public $search = '';
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public $sort = 'latest';
    public function updatedSort()
    {
        $this->resetPage();
    }

    public function toggleWishlist($productId)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId);

        if ($wishlist->exists()) {

            $wishlist->delete();

            $this->wishlist = array_values(
                array_diff($this->wishlist, [$productId])
            );

            Flux::toast(
                variant: 'warning',
                heading: 'Removed from Wishlist',
                text: 'Product removed successfully.'
            );

            $this->dispatch('wishlist-updated');

        } else {

            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
            ]);

            $this->wishlist[] = $productId;

            Flux::toast(
                variant: 'success',
                heading: 'Added to Wishlist',
                text: 'Product added successfully.'
            );

            $this->dispatch('wishlist-updated');
        }
    }
};
?>

<div>
    <section class="border-b border-zinc-200 dark:border-zinc-800">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <span class="inline-flex rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                Our Collection
            </span>
            <h1 class="mt-4 text-4xl font-black lg:text-5xl">
                Explore Products
            </h1>
            <p class="mt-4 max-w-2xl text-zinc-500 dark:text-zinc-400">
                Discover products carefully selected for quality,
                performance and value.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-8">
        <div class="flex gap-3 overflow-x-auto pb-2">
            <button
                wire:click="selectCategory"
                class="
                    shrink-0 rounded-full px-5 py-2 text-sm font-medium transition

                    {{ $selectedCategory === null
                        ? 'bg-blue-600 text-white'
                        : 'border border-zinc-200 bg-white hover:border-blue-500 dark:border-zinc-800 dark:bg-zinc-900'
                    }}
                "
            >
                All Products
            </button>

            @foreach($categories as $category)
               <button
                    wire:click="selectCategory({{ $category->id }})"
                    class="
                        shrink-0 rounded-full px-5 py-2 text-sm font-medium transition

                        {{ $selectedCategory == $category->id
                            ? 'bg-blue-600 text-white'
                            : 'border border-zinc-200 bg-white hover:border-blue-500 dark:border-zinc-800 dark:bg-zinc-900'
                        }}
                    "
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
        <div class="mb-6 grid grid-cols-2 gap-4">
            <flux:select wire:model.live="sort">
                <flux:select.option value="latest">Newest</flux:select.option>
                <flux:select.option value="price_low">Price: Low to High</flux:select.option>
                <flux:select.option value="price_high">Price: High to Low</flux:select.option>
                <flux:select.option value="name_asc">Name: A-Z</flux:select.option>
                <flux:select.option value="name_desc">Name: Z-A</flux:select.option>
            </flux:select>
            <flux:input
                wire:model.live.debounce.500ms="search"
                placeholder="Search products..."
            />
        </div>        
    </section>

    <div class="mx-auto max-w-7xl px-6">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            @php
                $products = $this->loadProducts;
            @endphp
            Showing {{ $products->total() }} products
        </p>
    </div>

    <section  id="products-grid" class="mx-auto max-w-7xl px-6 py-8">
        <div wire:loading.remove wire:target="search,sort,page,selectCategory">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @if($products->isEmpty())

                <div class="py-20 text-center">

                    <div class="text-6xl">
                        🔍
                    </div>

                    <h3 class="mt-4 text-xl font-bold">
                        No products found
                    </h3>

                    <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                        Try changing your search or category filter.
                    </p>

                </div>
            @else
            @foreach($products as $product)    
            <div class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">

                {{-- Wishlist Button --}}
                <button
                    type="button"
                    wire:click="toggleWishlist({{ $product->id }})"
                    class="absolute right-3 top-3 z-20 rounded-full bg-white/90 p-2 shadow transition hover:scale-110 dark:bg-zinc-900/90"
                >
                    @if(in_array($product->id, $wishlist))
                        {{-- Filled Heart --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 fill-red-500 text-red-500"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 21s-7-4.35-9.5-8.28C.89 10.28 2.1 6.5 5.8 5.4A5.24 5.24 0 0112 8a5.24 5.24 0 016.2-2.6c3.7 1.1 4.91 4.88 3.3 7.32C19 16.65 12 21 12 21z"/>
                        </svg>
                    @else
                        {{-- Outline Heart --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-zinc-600 dark:text-zinc-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364 4.318 12.682a4.5 4.5 0 010-6.364z"/>
                        </svg>
                    @endif
                </button>

                {{-- Product Link --}}
                <a
                    href="{{ route('user/productDetails', $product->slug) }}"
                    wire:navigate
                    class="block"
                >
                    {{-- Image --}}
                    <div class="overflow-hidden">
                        @if(isset($product->images[0]))
                            <img
                                src="{{ asset('storage/'.$product->images[0]->image) }}"
                                alt="{{ $product->name }}"
                                class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                            >
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="p-5">
                        <p class="text-xs uppercase tracking-wider text-zinc-500">
                            {{ $product->category->name ?? 'Category' }}
                        </p>

                        <h3 class="mt-2 line-clamp-2 font-bold">
                            {{ $product->name }}
                        </h3>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xl font-black text-blue-600 dark:text-blue-400">
                                ₹{{ number_format($product->price, 2) }}
                            </span>

                            @if($product->stock > 0)
                                <span class="text-xs text-green-600">
                                    In Stock
                                </span>
                            @else
                                <span class="text-xs text-red-600">
                                    Out of Stock
                                </span>
                            @endif
                        </div>
                    </div>
                </a>

            </div>
            @endforeach
            @endif    
        </div>
        </div>

        <div
            wire:loading
            wire:target="search,sort,page"
            class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
        >
                    
            @for ($i = 0; $i < 10; $i++)
                    
                <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    
                    {{-- Image --}}
                    <div class="h-64 w-full animate-pulse bg-zinc-200 dark:bg-zinc-800"></div>
                    
                    {{-- Content --}}
                    <div class="space-y-4 p-5">
                    
                        {{-- Category --}}
                        <div class="h-3 w-20 animate-pulse rounded-full bg-zinc-200 dark:bg-zinc-800"></div>
                    
                        {{-- Product Name --}}
                        <div class="h-5 w-full animate-pulse rounded bg-zinc-200 dark:bg-zinc-800"></div>
                        <div class="h-5 w-3/4 animate-pulse rounded bg-zinc-200 dark:bg-zinc-800"></div>
                    
                        {{-- Price + Stock --}}
                        <div class="flex items-center justify-between pt-2">
                    
                            <div class="h-6 w-24 animate-pulse rounded bg-zinc-200 dark:bg-zinc-800"></div>
                    
                            <div class="h-4 w-16 animate-pulse rounded-full bg-zinc-200 dark:bg-zinc-800"></div>
                    
                        </div>
                    
                    </div>
                    
                </div>
                    
            @endfor
                    
        </div>

        <div class="mt-10">
            {{ $products->links(data: ['scrollTo' => '#products-grid']) }}
        </div>
    </section>
</div>