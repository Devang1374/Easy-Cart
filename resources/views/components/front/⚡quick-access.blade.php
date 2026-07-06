<?php

use Livewire\Component;
use App\Models\product;
use App\Models\Category;

new class extends Component
{
    public string $search = '';

    public function updatedSearch()
    {
        $this->selectedIndex = 0;
    }

    public function getFilteredProductsProperty()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Product::with(['images', 'category'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->take(6)
            ->get();
    }

    public function getPagesProperty()
    {
        $pages = [
            [
                'name' => 'Products',
                'icon' => '🛍️',
                'route' => 'user/product',
            ],
        ];

        if (auth()->check()) {
            $pages = array_merge($pages, [
                [
                    'name' => 'Wishlist',
                    'icon' => '❤️',
                    'route' => 'user/wishlist',
                ],
                [
                    'name' => 'Cart',
                    'icon' => '🛒',
                    'route' => 'user/cart',
                ],
                [
                    'name' => 'My Orders',
                    'icon' => '📦',
                    'route' => 'user/order',
                ],
            ]);
        }

        return collect($pages);
    }

    public function getFilteredPagesProperty()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return $this->pages->filter(function ($page) {
            return str_contains(
                strtolower($page['name']),
                strtolower($this->search)
            );
        })->values();
    }

    public function getFilteredCategoriesProperty()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Category::where('name', 'like', '%' . $this->search . '%')
            ->select('id', 'name', 'slug')
            ->take(5)
            ->get();
    }

    public int $selectedIndex = 0;
    public function getResultsProperty()
    {
        return collect()

            ->merge(
                $this->filteredPages->map(fn ($page) => [
                    'type' => 'page',
                    'title' => $page['name'],
                    'url' => route($page['route']),
                ])
            )

            ->merge(
                $this->filteredCategories->map(fn ($category) => [
                    'type' => 'category',
                    'title' => $category->name,
                    'url' => route('user/product', ['category' => $category->id]),
                ])
            )

            ->merge(
                $this->filteredProducts->map(fn ($product) => [
                    'type' => 'product',
                    'title' => $product->name,
                    'url' => route('user/productDetails', $product->slug),
                ])
            );
    }

    public function moveDown()
    {
        if ($this->flatResults->isEmpty()) return;

        $this->selectedIndex++;

        if ($this->selectedIndex >= $this->flatResults->count()) {
            $this->selectedIndex = 0;
        }
    }

    public function moveUp()
    {
        if ($this->flatResults->isEmpty()) return;

        $this->selectedIndex--;

        if ($this->selectedIndex < 0) {
            $this->selectedIndex = $this->flatResults->count() - 1;
        }
    }

    public function selectResult()
    {
        $item = $this->flatResults->firstWhere('index', $this->selectedIndex);

        if (!$item) return;

        return redirect()->to($item['url']);
    }

    public function getFlatResultsProperty()
    {
        $items = collect();
        $index = 0;

        foreach ($this->filteredPages as $page) {
            $items->push([
                'index' => $index++,
                'type' => 'page',
                'name' => $page['name'],
                'route' => $page['route'],
                'url' => route($page['route']),
            ]);
        }
        
        foreach ($this->filteredCategories as $category) {
            $items->push([
                'index' => $index++,
                'type' => 'category',
                'id' => $category->id,
                'url' => route('user/product', ['category' => $category->id]),
            ]);
        }
        
        foreach ($this->filteredProducts as $product) {
            $items->push([
                'index' => $index++,
                'type' => 'product',
                'id' => $product->id,
                'url' => route('user/productDetails', $product->slug),
            ]);
        }

        return $items;
    }
};
?>

<flux:modal
    name="quick-access"
    class="max-w-2xl"
    wire:keydown.arrow-down="moveDown"
    wire:keydown.arrow-up="moveUp"
    wire:keydown.enter="selectResult"
    wire:keydown.escape="$dispatch('close-modal', { name: 'quick-access' })"
>
    <div class="space-y-6 p-6">

        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Search products, orders, pages..."
            autofocus
        />

        @if(strlen($search) < 2)

            <div>

                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500">
                    Quick Access
                </h3>

                <div class="space-y-2">

                    <a
                        href="{{ route('user/product') }}"
                        wire:navigate
                        class="flex items-center rounded-xl p-3 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        🛍️
                        <span class="ml-3">Products</span>
                    </a>

                    @auth

                        <a
                            href="{{ route('user/wishlist') }}"
                            wire:navigate
                            class="flex items-center rounded-xl p-3 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        >
                            ❤️
                            <span class="ml-3">Wishlist</span>
                        </a>

                        <a
                            href="{{ route('user/cart') }}"
                            wire:navigate
                            class="flex items-center rounded-xl p-3 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        >
                            🛒
                            <span class="ml-3">Cart</span>
                        </a>

                        <a
                            href="{{ route('user/order') }}"
                            wire:navigate
                            class="flex items-center rounded-xl p-3 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        >
                            📦
                            <span class="ml-3">My Orders</span>
                        </a>

                    @endauth

                </div>

            </div>

        @else
            @if($this->filteredPages->isNotEmpty())

                <div class="mb-5">

                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500">
                        Pages
                    </h3>

                    <div class="space-y-2">

                        @foreach($this->filteredPages as $page)

                            @php
                                $globalIndex = optional(
                                    $this->flatResults->firstWhere('name', $page['name'])
                                )['index'] ?? -1;
                            @endphp


                            <a
                                href="{{ route($page['route']) }}"
                                wire:navigate
                                class="flex items-center rounded-xl p-3 transition
                                {{ (int)$selectedIndex === (int)$globalIndex
                                    ? 'bg-blue-100 dark:bg-blue-900/40 ring-2 ring-blue-500'
                                    : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                                }}"
                            >
                                <span class="text-lg">
                                    {{ $page['icon'] }}
                                </span>

                                <span class="ml-3">
                                    {{ $page['name'] }}
                                </span>
                            </a>

                        @endforeach

                    </div>

                </div>

            @endif

            @if($this->filteredCategories->isNotEmpty())

                <div class="mb-5">

                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500">
                        Categories
                    </h3>

                    <div class="space-y-2">

                        @foreach($this->filteredCategories as $category)

                            @php
                                $globalIndex = optional(
                                    $this->flatResults->firstWhere('id', $category->id)
                                )['index'] ?? -1;
                            @endphp

                            <a
                                href="{{ route('user/product', ['category' => $category->id]) }}"
                                wire:navigate
                                class="flex items-center rounded-xl p-3 transition {{ (int)$selectedIndex === (int)$globalIndex
                                    ? 'bg-blue-100 dark:bg-blue-900/40 ring-2 ring-blue-500'
                                    : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                                }}"
                            >
                                <span class="text-lg">
                                    📂
                                </span>

                                <div class="ml-3">
                                    <p class="font-medium">
                                        {{ $category->name }}
                                    </p>

                                    <p class="text-xs text-zinc-500">
                                        Category
                                    </p>
                                </div>

                            </a>

                        @endforeach

                    </div>

                </div>

            @endif

            <div>

                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500">
                    Products
                </h3>

                @foreach($this->filteredProducts as $product)
                        @php
                            $globalIndex = optional(
                                $this->flatResults->firstWhere('id', $product->id)
                            )['index'] ?? -1;
                        @endphp
                        <a
                            href="{{ route('user/productDetails', $product->slug) }}"
                            wire:navigate
                            class="flex items-center gap-4 rounded-xl p-3 transition {{ (int)$selectedIndex === (int)$globalIndex
                                ? 'bg-blue-100 dark:bg-blue-900/40 ring-2 ring-blue-500'
                                : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                            }}"
                        >

                            {{-- Product Image --}}
                            <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">

                                @if(isset($product->images[0]))

                                    <img
                                        src="{{ $product->images[0]->image }}"
                                        alt="{{ $product->name }}"
                                        class="h-full w-full object-cover"
                                    >

                                @else

                                    <div class="flex h-full w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                        📦
                                    </div>

                                @endif

                            </div>

                            {{-- Product Details --}}
                            <div class="min-w-0 flex-1">

                                <p class="truncate font-semibold">
                                    {{ $product->name }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </p>

                            </div>

                            {{-- Price --}}
                            <div class="text-right">

                                <p class="font-bold text-blue-600 dark:text-blue-400">
                                    ₹{{ number_format($product->price, 2) }}
                                </p>

                            </div>

                        </a>
                @endforeach

                @if(
                    $this->filteredProducts->isEmpty()
                    && $this->filteredPages->isEmpty()
                    && $this->filteredCategories->isEmpty()
                )

                    <div class="rounded-xl p-6 text-center text-zinc-500">
                        <p class="text-lg">😕</p>
                        <p class="mt-2">No results found.</p>
                    </div>

                @endif

            </div>

        @endif

    </div>

</flux:modal>