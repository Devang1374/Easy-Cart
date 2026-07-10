<?php

use Livewire\Component;

// all database models that is used
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Banner;

use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $categories;
    public $featuredProducts;
    public $latestProducts;

    // get the hero Banners from database
    public function getHeroBannersProperty()
    {
        return Cache::remember('hero_banners', now()->addHours(12), function () {
            return Banner::active()->where('position', 'hero')->orderBy('sort_order')->get()->toArray();
        });
    }

    // mount the initial products and categories
    public function mount()
    {
        $this->categories = $this->getCategories();

        $this->featuredProducts = $this->getFeaturedProducts();

        $this->latestProducts = $this->getLatestProducts();
        Cache::remember('hero_latest_products', now()->addHours(12), function () {
            return Product::query()
                ->with('images')
                ->with('images')
                ->withAvg(['approvedReviews as average_rating' => function ($query) {}], 'rating')
                ->withCount(['approvedReviews as total_reviews'])
                ->where('is_active', true)
                ->latest()
                ->take(8)
                ->get()
                ->toArray();
        });

        if (auth()->check()) {
            $this->wishlist = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }
    }

    public function getCategories()
    {
        return Cache::remember('hero_categories', now()->addHours(12), function () {
            return Category::query()->where('is_active', true)->latest()->take(8)->get()->toArray();
        });
    }

    public function getFeaturedProducts()
    {
        return Cache::remember('hero_featured_products', now()->addHours(12), function () {
            return Product::query()
                ->with('images')
                ->withAvg(['approvedReviews as average_rating' => function ($query) {}], 'rating')
                ->withCount(['approvedReviews as total_reviews'])
                ->where('is_active', true)
                ->where('featured', true)
                ->latest()
                ->take(8)
                ->get()
                ->toArray();
        });
    }

    public function getLatestProducts()
    {
        return Cache::remember('hero_latest_products', now()->addHours(12), function () {
            return Product::query()
                ->with('images')
                ->with('images')
                ->withAvg(['approvedReviews as average_rating' => function ($query) {}], 'rating')
                ->withCount(['approvedReviews as total_reviews'])
                ->where('is_active', true)
                ->latest()
                ->take(8)
                ->get()
                ->toArray();
        });
    }

    // add and remove product from whishlist
    public array $wishlist = [];
    public function toggleWishlist($productId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $wishlist = Wishlist::where('user_id', auth()['id']())->where('product_id', $productId);

        if ($wishlist->exists()) {
            $wishlist->delete();

            $this->wishlist = array_values(array_diff($this->wishlist, [$productId]));

            Flux::toast(variant: 'warning', heading: 'Removed from Wishlist', text: 'Product removed successfully.');

            $this->dispatch('wishlist-updated');
        } else {
            Wishlist::create([
                'user_id' => auth()['id'](),
                'product_id' => $productId,
            ]);

            $this->wishlist[] = $productId;

            Flux::toast(variant: 'success', heading: 'Added to Wishlist', text: 'Product added successfully.');

            $this->dispatch('wishlist-updated');
        }
    }
};
?>


<div>
    <!-- hero section -->
    <section class="relative overflow-hidden min-h-[700px]">

        <div class="hero-slider splide">

            <div class="splide__track">

                <ul class="splide__list">

                    {{-- Default Hero --}}
                    <li class="splide__slide">

                        <section class="relative overflow-hidden min-h-[700px]">

                            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-950"></div>

                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute left-10 top-10 h-72 w-72 rounded-full bg-blue-500 blur-3xl"></div>
                                <div class="absolute right-10 bottom-10 h-72 w-72 rounded-full bg-purple-500 blur-3xl">
                                </div>
                            </div>

                            <div class="relative mx-auto flex min-h-[700px] max-w-7xl items-center px-8">

                                <div class="max-w-3xl">

                                    <span
                                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur">
                                        ✨ New Products Added Weekly
                                    </span>

                                    <h1 class="mt-6 text-5xl font-black leading-tight text-white lg:text-7xl">
                                        Discover Products You'll Actually Love
                                    </h1>

                                    <p class="mt-6 max-w-2xl text-lg text-zinc-300">
                                        Browse our curated collection of premium products with fast shipping,
                                        secure checkout and great prices.
                                    </p>

                                    <div class="mt-10 flex flex-wrap gap-4">

                                        <a href="#featured-products">
                                            <button
                                                class="rounded-xl bg-white px-7 py-4 font-semibold text-black transition hover:scale-105">
                                                Shop Now
                                            </button>
                                        </a>

                                        <a wire:navigate href="/user/product">
                                            <button
                                                class="rounded-xl border border-white/20 bg-white/5 px-7 py-4 font-semibold text-white backdrop-blur transition hover:bg-white/10">
                                                Explore Products
                                            </button>
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </section>

                    </li>

                    {{-- Dynamic Banners --}}
                    @foreach ($this->heroBanners as $banner)
                        <div class="absolute top-4 left-4 z-50 text-white bg-black/50 px-2 py-1 rounded">
                            {{ $banner['background_type'] }}
                            <br>
                            {{ $banner['background_color'] }}
                        </div>
                        <li class="splide__slide">

                            <section class="relative overflow-hidden min-h-[700px]">

                                {{-- Background --}}
                                @if ($banner['background_type'] === 'gradient')
                                    <div class="absolute inset-0"
                                        style="background:linear-gradient(135deg, {{ $banner['background_color'] }}, #0f172a);">
                                    </div>
                                @elseif($banner['background_type'] === 'solid')
                                    <div class="absolute inset-0" style="background:{{ $banner['background_color'] }};">
                                    </div>
                                @elseif($banner['background_type'] === 'image')
                                    <div class="absolute inset-0 bg-cover bg-center"
                                        style="background-image:url('{{ asset('storage/' . $banner['background_image']) }}');">
                                    </div>
                                @elseif($banner['background_type'] === 'gradient-image')
                                    <div class="absolute inset-0 bg-cover bg-center"
                                        style="background-image:url('{{ asset('storage/' . $banner['background_image']) }}');">
                                    </div>

                                    <div class="absolute inset-0"
                                        style="background:linear-gradient(135deg, {{ $banner['background_color'] }}cc, rgba(15,23,42,.75));">
                                    </div>
                                @endif

                                {{-- Background Effects --}}
                                <div class="absolute inset-0 overflow-hidden pointer-events-none">

                                    @if (in_array($banner['background_type'], ['gradient', 'solid', 'gradient-image']))
                                        <div class="absolute -top-40 -left-40 h-[32rem] w-[32rem] rounded-full opacity-20 blur-[140px]"
                                            style="background: {{ $banner['background_color'] }};"></div>
                                    @endif

                                    <div
                                        class="absolute right-0 bottom-0 h-[38rem] w-[38rem] rounded-full bg-white/10 blur-[170px]">
                                    </div>

                                </div>

                                <div
                                    class="relative mx-auto grid min-h-[700px] max-w-7xl items-center gap-10 px-8 lg:grid-cols-2">

                                    {{-- LEFT --}}
                                    <div class="z-10">

                                        @if ($banner['subtitle'])
                                            <span
                                                class="inline-flex rounded-full border border-white/20 bg-white/10 px-5 py-2 text-sm font-medium text-white backdrop-blur">
                                                {{ $banner['subtitle'] }}
                                            </span>
                                        @endif

                                        <h1 class="mt-6 text-5xl font-black leading-tight text-white lg:text-7xl">
                                            {{ $banner['title'] }}
                                        </h1>

                                        @if (!empty($banner['description']))
                                            <p class="mt-8 max-w-xl text-lg leading-8 text-white/80">
                                                {{ $banner['description'] }}
                                            </p>
                                        @endif

                                        <div class="mt-10 flex flex-wrap gap-4">

                                            @if ($banner['button_text'])
                                                <a href="{{ $banner['button_link'] }}"
                                                    class="rounded-xl bg-white px-8 py-4 font-semibold text-black transition duration-300 hover:scale-105">
                                                    {{ $banner['button_text'] }}
                                                </a>
                                            @endif

                                            @if ($banner['secondary_button_text'])
                                                <a href="{{ $banner['secondary_button_link'] }}"
                                                    class="rounded-xl border border-white/20 bg-white/5 px-8 py-4 font-semibold text-white backdrop-blur transition hover:bg-white/10">
                                                    {{ $banner['secondary_button_text'] }}
                                                </a>
                                            @endif

                                        </div>

                                    </div>

                                    <!-- Right -->
                                    <div
                                        class="hero-image relative flex items-center justify-center lg:justify-center px-6 lg:px-12">

                                        {{-- Luxury Blue Glow --}}
                                        <div
                                            class="absolute inset-0 flex items-center justify-center pointer-events-none">

                                            <div
                                                class="absolute h-[600px] w-[600px] rounded-full bg-sky-500/15 blur-[170px]">
                                            </div>

                                            <div
                                                class="absolute h-[420px] w-[420px] rounded-full bg-cyan-400/20 blur-[110px]">
                                            </div>

                                            <div
                                                class="absolute h-[260px] w-[260px] rounded-full bg-white/30 blur-[60px]">
                                            </div>

                                        </div>

                                        {{-- Decorative Ring --}}
                                        <div class="absolute h-[38rem] w-[38rem] rounded-full border border-white/10">
                                        </div>

                                        @if ($banner['desktop_image'])
                                            <img src="{{ asset('storage/' . $banner['desktop_image']) }}"
                                                alt="{{ $banner['title'] }}"
                                                class="relative z-10 mx-auto max-h-[500px] max-w-full object-contain drop-shadow-[0_35px_60px_rgba(0,0,0,.45)] transition duration-500 hover:scale-105">
                                        @endif

                                    </div>

                                </div>

                            </section>

                        </li>
                    @endforeach

                </ul>

            </div>

        </div>

    </section>

    <!-- categories section -->
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

            @foreach ($categories as $category)
                <a href="{{ route('user/product', ['category' => $category['id']]) }}"
                    class="group rounded-3xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-2 hover:border-blue-500/20 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 dark:bg-zinc-800 dark:group-hover:bg-blue-950/30">

                        @if ($category['image'])
                            <img src="{{ asset('storage/' . $category['image']) }}" alt="{{ $category['name'] }}"
                                class="h-9 w-9 object-contain">
                        @else
                            <span class="text-2xl">
                                📦
                            </span>
                        @endif

                    </div>
                    <h3 class="font-semibold transition group-hover:text-blue-600">
                        {{ $category['name'] }}
                    </h3>
                </a>
            @endforeach

        </div>

    </section>

    <!-- featured products section -->
    <section id="featured-products" class="relative overflow-hidden py-24">

        {{-- Background Effects --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute left-0 top-0 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-96 w-96 rounded-full bg-purple-500/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-6">

            {{-- Section Header --}}
            <div class="mb-14 text-center">

                <span
                    class="inline-flex rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-1 text-sm font-medium text-blue-600 dark:text-blue-400">
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

                @foreach ($featuredProducts as $product)
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] dark:border-zinc-800 dark:bg-zinc-900">

                        {{-- Wishlist Button --}}
                        <button type="button" wire:click="toggleWishlist({{ $product['id'] }})"
                            class="absolute right-3 top-3 z-20 rounded-full bg-white/90 p-2 shadow transition hover:scale-110 dark:bg-zinc-900/90">
                            @if (in_array($product['id'], $wishlist))
                                {{-- Filled Heart --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-red-500 text-red-500"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M12 21s-7-4.35-9.5-8.28C.89 10.28 2.1 6.5 5.8 5.4A5.24 5.24 0 0112 8a5.24 5.24 0 016.2-2.6c3.7 1.1 4.91 4.88 3.3 7.32C19 16.65 12 21 12 21z" />
                                </svg>
                            @else
                                {{-- Outline Heart --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-600 dark:text-zinc-300"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364 4.318 12.682a4.5 4.5 0 010-6.364z" />
                                </svg>
                            @endif
                        </button>

                        <a href="{{ route('user/productDetails', $product['slug']) }}" wire:navigate class="block">

                            {{-- Product Image --}}
                            <div class="relative overflow-hidden">

                                {{-- Featured Badge --}}
                                <div class="absolute left-4 top-4 z-20">
                                    <span
                                        class="rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                        ⭐ Featured
                                    </span>
                                </div>

                                @if (isset($product['images'][0]))
                                    <img src="{{ asset('storage/' . $product['images'][0]['image']) }}"
                                        alt="{{ $product['name'] }}"
                                        class="h-72 w-full object-cover transition duration-700 group-hover:scale-110">
                                @else
                                    <div class="flex h-72 items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                        <span class="text-zinc-400">
                                            No Image
                                        </span>
                                    </div>
                                @endif

                                {{-- Overlay --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 transition duration-300 group-hover:opacity-100">
                                </div>

                                {{-- Hover Button --}}
                                <div
                                    class="absolute bottom-4 left-4 right-4 z-20 translate-y-4 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">

                                    <span
                                        class="block w-full rounded-xl bg-white py-3 text-center font-semibold text-black transition hover:bg-zinc-100">
                                        View Product
                                    </span>

                                </div>

                            </div>

                            {{-- Product Info --}}
                            <div class="p-6">

                                <h3 class="line-clamp-2 text-lg font-bold tracking-tight">
                                    {{ $product['name'] }}
                                </h3>

                                {{-- product average rating --}}
                                <div class="mt-2 flex items-center gap-2">

                                    <div class="flex text-amber-400 text-sm">

                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= round($product['average_rating'] ?? 0))
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor

                                    </div>

                                    <span class="text-sm font-medium">
                                        {{ number_format($product['average_rating'] ?? 0, 1) }}
                                    </span>

                                    <span class="text-sm text-zinc-500">
                                        ({{ $product['total_reviews'] ?? 0 }})
                                    </span>

                                </div>

                                <div class="mt-4 flex items-center justify-between">

                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-zinc-500">
                                            Starting From
                                        </p>

                                        <p class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                            ₹{{ number_format($product['price'], 2) }}
                                        </p>
                                    </div>

                                    @if ($product['stock'] > 0)
                                        <span
                                            class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            In Stock
                                        </span>
                                    @else
                                        <span
                                            class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
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

    <!-- latest product section -->
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

            @foreach ($latestProducts as $product)
                <div
                    class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">

                    {{-- Wishlist Button --}}
                    <button type="button" wire:click="toggleWishlist({{ $product['id'] }})"
                        class="absolute right-3 top-3 z-20 rounded-full bg-white/90 p-2 shadow transition hover:scale-110 dark:bg-zinc-900/90">
                        @if (in_array($product['id'], $wishlist))
                            {{-- Filled Heart --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-red-500 text-red-500"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 21s-7-4.35-9.5-8.28C.89 10.28 2.1 6.5 5.8 5.4A5.24 5.24 0 0112 8a5.24 5.24 0 016.2-2.6c3.7 1.1 4.91 4.88 3.3 7.32C19 16.65 12 21 12 21z" />
                            </svg>
                        @else
                            {{-- Outline Heart --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-600 dark:text-zinc-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364 4.318 12.682a4.5 4.5 0 010-6.364z" />
                            </svg>
                        @endif
                    </button>

                    {{-- Product Link --}}
                    <a href="{{ route('user/productDetails', $product['slug']) }}" wire:navigate class="block">
                        {{-- Image --}}
                        <div class="overflow-hidden">
                            @if (isset($product['images'][0]))
                                <img src="{{ asset('storage/' . $product['images'][0]['image']) }}"
                                    alt="{{ $product['name'] }}"
                                    class="h-64 w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <p class="text-xs uppercase tracking-wider text-zinc-500">
                                {{ $product['category']['name'] ?? 'Category' }}
                            </p>

                            <h3 class="mt-2 line-clamp-2 font-bold">
                                {{ $product['name'] }}
                            </h3>

                            {{-- product average rating --}}
                            <div class="mt-2 flex items-center gap-2">

                                <div class="flex text-amber-400 text-sm">

                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= round($product['average_rating'] ?? 0))
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endfor

                                </div>

                                <span class="text-sm font-medium">
                                    {{ number_format($product['average_rating'] ?? 0, 1) }}
                                </span>

                                <span class="text-sm text-zinc-500">
                                    ({{ $product['total_reviews'] ?? 0 }})
                                </span>

                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xl font-black text-blue-600 dark:text-blue-400">
                                    ₹{{ number_format($product['price'], 2) }}
                                </span>

                                @if ($product['stock'] > 0)
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

        </div>

    </section>
</div>