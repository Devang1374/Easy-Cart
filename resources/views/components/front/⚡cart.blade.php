<?php

use Livewire\Component;
use App\Models\product;

new class extends Component
{
    public $cart = [];

    public function mount()
    {
        $this->cart = session('cart', []);
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)
            ->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function increaseQuantity($productId)
    {
        $cart = session('cart', []);
    
        if (! isset($cart[$productId])) {
            return;
        }
    
        if ($cart[$productId]['quantity'] >= product::where('id', $productId)->value('stock')) {
    
            Flux::toast(
                variant: 'danger',
                heading: 'Stock Limit Reached',
                text: "Only {$cart[$productId]['stock']} items available."
            );
    
            return;
        }
    
        $cart[$productId]['quantity']++;
    
        session()->put('cart', $cart);
    
        $this->cart = $cart;
    
        $this->dispatch('cart-updated');
    }

    public function decreaseQuantity($productId)
    {
        $cart = session('cart', []);

        if (!isset($cart[$productId])) {
            return;
        }

        if ($cart[$productId]['quantity'] > 1) {
            $cart[$productId]['quantity']--;
        }

        session()->put('cart', $cart);

        $this->cart = $cart;

        $this->dispatch('cart-updated');
    }

    public function removeItem($productId)
    {
        $cart = session('cart', []);

        if (!isset($cart[$productId])) {
            return;
        }

        unset($cart[$productId]);

        session()->put('cart', $cart);

        $this->cart = $cart;

        $this->dispatch('cart-updated');

        Flux::toast(
            heading: 'Removed',
            text: 'Product removed from cart.'
        );
    }

    public function clearCart()
    {
        session()->forget('cart');

        $this->cart = [];

        $this->dispatch('cart-updated');

        Flux::toast(
            heading: 'Cart Cleared',
            text: 'All items removed from cart.'
        );
    }

    public function getTotalItemsProperty()
    {
        return collect($this->cart)
            ->sum('quantity');
    }

    public function updateQuantity($productId, $quantity)
    {
        $cart = session('cart', []);

        if (! isset($cart[$productId])) {
            return;
        }

        $quantity = (int) $quantity;

        if ($quantity < 1) {
            $quantity = 1;
        }

        $productQuntity = product::where('id', $productId)->value('stock');

        if ($quantity > $productQuntity) {

            Flux::toast(
                variant: 'danger',
                heading: 'Stock Limit Reached',
                text: "Only {$productQuntity} items available."
            );
        }

        $cart[$productId]['quantity'] = $quantity;

        session()->put('cart', $cart);

        $this->cart = $cart;

        $this->dispatch('cart-updated');

    }
};
?>

<div>
    @if(empty($cart))

        <div class="py-24 text-center">

            <div class="text-6xl">
                🛒
            </div>

            <h2 class="mt-6 text-2xl font-bold">
                Your cart is empty
            </h2>

            <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                Looks like you haven't added anything yet.
            </p>

            <a
                href="{{ route('user/product') }}"
                class="mt-6 inline-block"
            >
                <flux:button variant="primary">
                    Continue Shopping
                </flux:button>
            </a>

        </div>

    @else
        <div class="mx-auto max-w-7xl px-6 py-12">
        
            <div class="mb-6 flex items-center justify-between">

                <h1 class="text-4xl font-black">
                    Shopping Cart
                </h1>

                <div>
                    <span class="rounded-full bg-zinc-100 px-4 py-2 text-sm dark:bg-zinc-800">
                        {{ $this->totalItems }} Items
                    </span>
                    <flux:button
                        variant="ghost"
                        wire:click="clearCart"
                        wire:confirm="Are you sure you want to clear your cart?"
                    >
                        Clear Cart
                    </flux:button>
                </div>
            </div>
        
            <div class="grid gap-8 lg:grid-cols-3">
        
                {{-- Cart Items --}}
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        @foreach($cart as $item)

                            @php
                                $stock = product::where('id', $item['id'])->value('stock');
                            @endphp
                            <div class="rounded-3xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">

                                <div class="flex flex-col gap-4 sm:flex-row">

                                    {{-- Image --}}
                                    <div class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl">
                                        <a href="{{ route('user/productDetails', $item['slug']) }}">
                                            <img
                                                src="{{ asset('storage/'.$item['image']) }}"
                                                class="h-full w-full object-cover"
                                            >
                                        </a>
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1">

                                        <h3 class="font-bold text-lg">
                                            <a
                                                href="{{ route('user/productDetails', $item['slug']) }}"
                                                class="font-bold text-lg hover:text-blue-600 dark:hover:text-blue-400"
                                            >
                                                {{ $item['name'] }}
                                            </a>    
                                        </h3>

                                        <button
                                            wire:click="removeItem({{ $item['id'] }})"
                                            class="mt-2 text-sm font-medium text-red-500 hover:text-red-600"
                                        >
                                            Remove
                                        </button>

                                        <p class="mt-2 text-sm text-zinc-500">
                                            ₹{{ number_format($item['price'], 2) }}
                                        </p>

                                        <div class="mt-4 flex items-center justify-between">

                                            <span class="text-sm text-zinc-500">
                                                <div class="flex items-center rounded-xl border border-zinc-200 dark:border-zinc-800">

                                                    <button
                                                        wire:click="decreaseQuantity({{ $item['id'] }})"
                                                        class="px-3 py-2 transition hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                                    >
                                                        −
                                                    </button>

                                                    @if($item['quantity'] > $stock)
                                                        @php
                                                            $item['quantity'] = $stock; 
                                                            $cart[$item['id']]['quantity'] = $stock;
                                                            session()->put('cart', $cart);
                                                            $this->dispatch('cart-updated');
                                                        @endphp
                                                    @endif   

                                                    <input
                                                        type="number"
                                                        min="1"
                                                        max="{{ $stock }}"
                                                        wire:model.blur="cart.{{ $item['id'] }}.quantity",
                                                        wire:change.live="updateQuantity({{ $item['id'] }}, $event.target.value)"
                                                        class="w-16 border-0 bg-transparent text-center focus:ring-0"
                                                    />

                                                    <button
                                                        wire:click="increaseQuantity({{ $item['id'] }})"
                                                        @disabled($item['quantity'] >= $stock)
                                                        class="px-4 py-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        +
                                                    </button>

                                                </div>
                                                <p class="mt-2 text-xs text-zinc-500">
                                                    {{ $stock }} available
                                                </p>
                                            </span>

                                            <span class="font-bold">
                                                ₹{{ number_format($item['price'] * $item['quantity'], 2) }}
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach
        
                    </div>
                </div>
        
                {{-- Order Summary --}}
                <div>
                    <div class="sticky top-24 rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                        <h2 class="text-xl font-bold">
                            Order Summary
                        </h2>

                        <div class="flex justify-between">
                            <span class="text-zinc-500">
                                Items
                            </span>

                            <span class="font-medium">
                                {{ $this->totalItems }}
                            </span>
                        </div>

                        <div class="mt-6 space-y-4">

                            <div class="flex justify-between">
                                <span class="text-zinc-500">
                                    Subtotal
                                </span>

                                <span class="font-semibold">
                                    ₹{{ number_format($this->cartTotal, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-zinc-500">
                                    Shipping
                                </span>

                                <span class="font-semibold">
                                    Free
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-zinc-500">
                                    Discount
                                </span>

                                <span class="font-medium text-green-600">
                                    ₹0.00
                                </span>
                            </div>

                            <hr class="border-zinc-200 dark:border-zinc-800">

                            <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-800">

                                <div class="flex items-center justify-between">

                                    <span class="text-lg font-bold">
                                        Total
                                    </span>

                                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                        ₹{{ number_format($this->cartTotal, 2) }}
                                    </span>

                                </div>
                                <div class="mt-4 rounded-2xl bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                    🚚 Free shipping on all orders
                                </div>
                            </div>

                        </div>

                        @auth  
                        
                            <a href="{{ route('user/checkout') }}">
                                <flux:button
                                    variant="primary"
                                    class="mt-6 w-full"
                                >
                                    Proceed To Checkout
                                </flux:button>
                            </a>

                        @else

                            <a href="{{ route('login') }}">
                                <flux:button
                                    variant="primary"
                                    class="w-full"
                                >
                                    Login To Checkout
                                </flux:button>
                            </a>

                        @endauth
                        <div class="mt-6 text-center text-xs text-zinc-500">

                            <p>🔒 Secure Checkout</p>

                            <p class="mt-1">
                                Your information is protected.
                            </p>

                        </div>

                        <a
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
        
        </div>
    @endif
</div>