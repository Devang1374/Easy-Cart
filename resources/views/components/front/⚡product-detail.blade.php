<?php

use Livewire\Component;
use App\Models\product;

new class extends Component
{
    public $product;
    public $slug;

    public $selectedImage = null;

    public $relatedProducts = [];

    public function mount()
    {
        $this->product = product::query()
            ->with([
                'images',
                'category',
            ])
            ->where('slug', $this->slug)
            ->where('is_active', true)
            ->firstOrFail();

        $this->selectedImage = $this->product->images->first()?->image;

        $this->relatedProducts = product::query()
            ->with('images')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();
    }

    public function selectImage($image)
    {
        $this->selectedImage = $image;
    }

    public $quantity = 1;
    public function increaseQuantity()
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        $cart = session()->get('cart', []);

        $currentQty = $cart[$this->product->id]['quantity'] ?? 0;

        $newQty = $currentQty + $this->quantity;

        if ($newQty > $this->product->stock) {
            // Later replace with Flux toast
            Flux::toast(
                variant: 'danger',
                heading: 'Stock Limit Reached',
                text: "Only {$this->product->stock} items available. You alread have $currentQty in Your Cart."
            );
            
            return;
        }

        if (isset($cart[$this->product->id])) {

            $cart[$this->product->id]['quantity'] = $newQty;

        } else {

            $cart[$this->product->id] = [
                'id'       => $this->product->id,
                'name'     => $this->product->name,
                'slug'     => $this->product->slug,
                'price'    => $this->product->price,
                'quantity' => $this->quantity,
                'image'    => $this->product->images->first()?->image,
            ];

        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');

        Flux::toast(
            heading: 'Success',
            text: 'Product added to cart.'
        );
    }
};
?>

<div class="mx-auto max-w-7xl px-6 py-12">
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    <div class="grid gap-10 lg:grid-cols-2">

        {{-- Gallery --}}
        <div class="overflow-hidden rounded-3xl border border-zinc-200 dark:border-zinc-800">
            <img
                src="{{ $selectedImage }}"
                class="h-[500px] w-full object-cover"
            >

            <div class="mt-4 flex gap-3 overflow-x-auto">

                @foreach($product->images as $image)

                    <button
                        wire:click="selectImage('{{ $image->image }}')"
                        class="overflow-hidden rounded-xl border-2 border-transparent hover:border-blue-500"
                    >

                        <img
                            src="{{ $image->image }}"
                            class="h-20 w-20 object-cover"
                        >

                    </button>

                @endforeach

            </div>
        </div>

        {{-- Product Info --}}
        <div>

            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
                {{ $product->category?->name }}
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight">
                {{ $product->name }}
            </h1>

            <div class="mt-3 flex items-center gap-3">

                <div class="text-xl text-yellow-400">
                    @for($i = 1; $i <= 5; $i++)
                        {{ $i <= floor($product->averageRating()) ? '★' : '☆' }}
                    @endfor
                </div>
            
                <span class="font-semibold">
                    {{ $product->averageRating() }}
                </span>
            
                <span class="text-zinc-500">
                    ({{ $product->totalReviews() }} Reviews)
                </span>
            
            </div>

            <p class="mt-6 text-4xl font-black text-blue-600 dark:text-blue-400">
                ₹{{ number_format($product->price, 2) }}
            </p>

            @if($product->stock > 0)

                <div class="mt-6">
                    <span class="rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        ✓ In Stock
                    </span>
                </div>

            @else

                <div class="mt-6">
                    <span class="rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        ✕ Out of Stock
                    </span>
                </div>

            @endif

            <div class="mt-6 space-y-2 text-sm text-zinc-500 dark:text-zinc-400">

                <div>
                    SKU:
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $product->sku }}
                    </span>
                </div>

                <div>
                    Available:
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $product->stock }}
                    </span>
                </div>

            </div>
            <div class="mt-8">

                <p class="mb-3 text-sm font-medium">
                    Quantity
                </p>

                <div class="flex w-fit items-center rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <button
                            wire:click="decreaseQuantity"
                            class="px-4 py-3"
                        >
                            -
                        </button>

                        <span class="min-w-12 text-center">
                            {{ $quantity }}
                        </span>

                        <button
                            wire:click="increaseQuantity"
                            class="px-4 py-3"
                        >
                            +
                        </button>
                </div>

                <div class="mt-8">
                    <flux:button
                        wire:click="addToCart"
                        :disabled="$product->stock <= 0"
                        variant="primary"
                        class="w-full"
                    >
                        {{ $product->stock > 0 ? 'Add To Cart' : 'Out Of Stock' }}
                    </flux:button>
                </div>

                <a
                    wire:navigate
                    href="{{ route('user/product') }}"
                    class="mt-3 block"
                >
                    <flux:button
                        variant="ghost"
                        class="w-full"
                    >
                        Continue Shopping
                    </flux:button>
                </a>
            </div>
        </div>

    </div>

    <section class="mt-20">

        <div class="rounded-3xl border border-zinc-200 bg-white p-8 dark:border-zinc-800 dark:bg-zinc-900">

            <h2 class="text-2xl font-bold">
                Product Description
            </h2>

            <div class="mt-6 text-zinc-600 dark:text-zinc-300">
                {!! $product->description !!}
            </div>

        </div>

    </section>

    <section class="mt-20">

        <livewire:front.product-review
            :product="$product"
            :key="'reviews-'.$product->id"
        />

    </section>

    @if(!empty($relatedProducts[0]))
    <section class="mt-20">

        <div class="mb-8">

            <h2 class="text-3xl font-bold">
                Related Products
            </h2>

            <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                You may also like these products.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

            @foreach($relatedProducts as $related)

                <a
                    href="{{ route('user/productDetails', $related->slug) }}"
                    class="group block"
                >

                    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">

                        @if(isset($related->images[0]))

                            <img
                                src="{{ $related->images[0]->image }}"
                                class="h-60 w-full object-cover transition duration-500 group-hover:scale-105"
                            >

                        @endif

                        <div class="p-5">

                            <h3 class="line-clamp-2 font-semibold">
                                {{ $related->name }}
                            </h3>

                            <p class="mt-3 text-xl font-bold text-blue-600 dark:text-blue-400">
                                ₹{{ number_format($related->price, 2) }}
                            </p>

                        </div>

                    </div>

                </a>

            @endforeach

        </div>

    </section>
    @endif
</div>