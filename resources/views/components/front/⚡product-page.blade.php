<?php

use Livewire\Component;

use App\Models\Category;
use App\Models\Product;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $categories;

    public function mount()
    {
       $this->categories = Category::query()
        ->where('is_active', true)
        ->get();

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
                <a
                        href="{{ route('user/productDetails', $product->slug) }}"
                        class="group block"
                    >      
                    <div class="group overflow-hidden rounded-3xl border border-zinc-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
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
                    </div>
                </a>
            @endforeach
            @endif
            </div>
        <div class="mt-10">
            {{ $products->links(data: ['scrollTo' => '#products-grid']) }}
        </div>
    </section>
</div>