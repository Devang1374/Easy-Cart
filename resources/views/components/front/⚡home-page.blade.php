<?php

use Livewire\Component;

use App\Models\Category;
use App\Models\Product;

new class extends Component
{
    public $categories;
    public $featuredProducts;
    public $latestProducts;

    public function mount()
    {
        $this->categories = Category::query()->where('is_active', true)->latest()->take(8)->get();
        $this->featuredProducts = Product::query()->with('images')->where('is_active', true)->where('featured', true)->latest()->take(8)->get();
        $this->latestProducts = Product::query()->with('images')->where('is_active', true)->latest()->take(8)->get();
    }
};
?>


<div>

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-950"></div>
    
        <div class="absolute inset-0 opacity-20">
            <div class="absolute left-10 top-10 h-72 w-72 rounded-full bg-blue-500 blur-3xl"></div>
            <div class="absolute right-10 bottom-10 h-72 w-72 rounded-full bg-purple-500 blur-3xl"></div>
        </div>
    
        <div class="relative mx-auto max-w-7xl px-6 py-24 lg:py-32">
    
            <div class="max-w-3xl">
    
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1 text-sm text-white backdrop-blur">
                    ✨ New Products Added Weekly
                </span>
    
                <h1 class="mt-6 text-5xl font-black leading-tight text-white lg:text-7xl">
                    Discover Products You'll Actually Love
                </h1>
    
                <p class="mt-6 max-w-2xl text-lg text-zinc-300">
                    Browse our curated collection of premium products with fast shipping,
                    secure checkout and great prices.
                </p>
    
                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:gap-4">
                    <a wire:navigate href="/user/product">
                        <button class="rounded-xl px-6 py-3 font-medium text-black bg-white transition hover:bg-white/90">
                            Show Now
                        </button>
                    </a>

                    <a href="#featured-products">
                        <button class="rounded-xl border border-white/20 px-6 py-3 font-medium text-white transition hover:bg-white/10">
                            Explore Products
                        </button>
                    </a>
                </div>
    
            </div>
    
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-20">

        <div class="mb-10 flex items-center justify-between">

            <div>
                <h2 class="text-3xl font-bold">
                    Shop by Category
                </h2>

                <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                    Browse products by category.
                </p>
            </div>

        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">

            @foreach($categories as $category)
                <a
                    href="#"
                    class="group rounded-3xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-2 hover:border-blue-500/20 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 dark:bg-zinc-800 dark:group-hover:bg-blue-950/30">

                        @if($category->image)

                            <img
                                src="{{ asset('storage/' . $category->image) }}"
                                alt="{{ $category->name }}"
                                class="h-9 w-9 object-contain"
                            >

                        @else

                            <span class="text-2xl">
                                📦
                            </span>

                        @endif

                    </div>
                    <h3 class="font-semibold transition group-hover:text-blue-600">
                        {{ $category->name }}
                    </h3>
                </a>

            @endforeach

        </div>

    </section>

    <section id="featured-products" class="relative overflow-hidden py-24">

        {{-- Background Effects --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute left-0 top-0 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-96 w-96 rounded-full bg-purple-500/10 blur-3xl"></div>
        </div>
    
        <div class="mx-auto max-w-7xl px-6">
    
            {{-- Section Header --}}
            <div class="mb-14 text-center">
    
                <span class="inline-flex rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                    Featured Collection
                </span>
    
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    Trending Right Now
                </h2>
    
                <p class="mt-3 text-zinc-500 dark:text-zinc-400">
                    Discover the products our customers are loving most.
                </p>
    
            </div>
    
            {{-- Products Grid --}}
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
    
                @foreach($featuredProducts as $product)
                <div class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] dark:border-zinc-800 dark:bg-zinc-900">
                        <a href="{{ route('user/productDetails', $product->slug) }}">
    
                        {{-- Product Image --}}
                        <div class="relative overflow-hidden">
    
                            {{-- Featured Badge --}}
                            <div class="absolute left-4 top-4 z-20">
                                <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                    ⭐ Featured
                                </span>
                            </div>
    
                            @if(isset($product->images[0]))
    
                                <img
                                    src="{{ asset('storage/'.$product->images[0]->image) }}"
                                    alt="{{ $product->name }}"
                                    class="h-72 w-full object-cover transition duration-700 group-hover:scale-110"
                                >
    
                            @else
    
                                <div class="flex h-72 items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                    <span class="text-zinc-400">
                                        No Image
                                    </span>
                                </div>
    
                            @endif
    
                            {{-- Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></div>
        
                                {{-- Hover Button --}}
                                <div class="absolute bottom-4 left-4 right-4 z-20 translate-y-4 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
        
                                    <a href="{{ route('user/productDetails', $product->slug) }}">
                                        <button class="w-full rounded-xl bg-white py-3 font-semibold text-black transition hover:bg-zinc-100">
                                            View Product
                                        </button>
                                    </a>
        
                                </div>
        
                            </div>
        
                            {{-- Product Info --}}
                            <div class="p-6">
        
                                <h3 class="line-clamp-2 text-lg font-bold tracking-tight">
                                    {{ $product->name }}
                                </h3>
        
                                <div class="mt-4 flex items-center justify-between">
        
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-zinc-500">
                                            Starting From
                                        </p>
        
                                        <p class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                            ₹{{ number_format($product->price, 2) }}
                                        </p>
                                    </div>
        
                                    @if($product->stock > 0)
        
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            In Stock
                                        </span>
        
                                    @else
        
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                            Out of Stock
                                        </span>
        
                                    @endif
        
                                </div>
        
                            </div>
        
                        </a>
                        </div>
                    @endforeach
        
                </div>
        
            </div>
        
    </section>

    <section id="latest-products" class="mx-auto max-w-7xl px-6 py-20">

        <div class="mb-10 flex items-center justify-between">

            <div>
                <h2 class="text-3xl font-bold">
                    Latest Products
                </h2>

                <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                    Latest products from our collection.
                </p>
            </div>

        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

        @foreach($latestProducts as $product)
        <div class="group overflow-hidden rounded-3xl border border-zinc-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            <a href="{{ route('user/productDetails', $product->slug) }}">
            <div class="relative overflow-hidden">

                @if(isset($product->images[0]))

                    <img
                        src="{{ asset('storage/'.$product->images[0]->image) }}"
                        alt="{{ $product->name }}"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                    >

                @else

                    <div class="flex h-64 items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                        No Image
                    </div>

                @endif

            </div>

            <div class="p-5">

                <h3 class="line-clamp-2 font-semibold">
                    {{ $product->name }}
                </h3>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xl font-bold">
                        ₹{{ number_format($product->price, 2) }}
                    </span>

                    @if($product->stock > 0)

                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            In Stock
                        </span>

                    @else

                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            Out of Stock
                        </span>

                    @endif

                </div>

                <div class="mt-5">

                    <a href="{{ route('user/productDetails', $product->slug)}}">
                        <flux:button variant="primary" class="w-full">
                            View Product
                        </flux:button>
                    </a>

                </div>
            </div>
        </a>
        </div>
        @endforeach

        </div>

    </section>
</div>