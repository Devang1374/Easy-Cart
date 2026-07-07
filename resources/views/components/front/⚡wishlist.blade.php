<?php

use Livewire\Component;

use App\Models\Wishlist;

new class extends Component
{
    public $wishlist;

    public function mount(){
        $this->wishlist = Wishlist::with([
               'product.images',
               'product.category',
           ])
           ->where('user_id', auth()->id())
           ->latest()
           ->get();
    }

    public function remove($id)
    {
        Wishlist::where('user_id', auth()->id())
            ->where('product_id', $id)
            ->delete();

        $this->dispatch('wishlist-updated');

        Flux::toast(
            heading: 'Removed from Wishlist',
            text: 'Product removed successfully.'
        );
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-6 md:py-10">

    <h1 class="mb-6 text-2xl font-bold md:mb-8 md:text-3xl">
        My Wishlist
    </h1>

    @if($wishlist->isEmpty())

        <div class="rounded-3xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700 md:p-16">

            <div class="text-5xl md:text-6xl">
                ❤️
            </div>

            <h2 class="mt-4 text-xl font-bold md:mt-6 md:text-2xl">
                Your wishlist is empty
            </h2>

            <p class="mt-2 text-sm text-zinc-500 md:text-base">
                Save products you love for later.
            </p>

            <a
                href="{{ route('user/product') }}"
                wire:navigate
            >
                <flux:button class="mt-6 md:mt-8">
                    Browse Products
                </flux:button>
            </a>

        </div>

    @else

        <div class="space-y-4 md:space-y-5">

            @foreach($wishlist as $item)

                @php
                    $product = $item->product;
                @endphp

                <div class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:gap-6 sm:p-5">

                    {{-- Image --}}
                    <a
                        href="{{ route('user/productDetails', $product->slug) }}"
                        wire:navigate
                        class="flex justify-center sm:block shrink-0"
                    >
                        <img
                            src="{{ $product->images->first()->image }}"
                            class="h-40 w-40 rounded-2xl object-cover sm:h-28 sm:w-28"
                        >
                    </a>

                    {{-- Info --}}
                    <div class="flex-1 text-center sm:text-left min-w-0">

                        <h2 class="text-lg font-bold truncate">
                            {{ $product->name }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $product->category->name }}
                        </p>

                        <p class="mt-2 text-xl font-black text-blue-600 md:mt-3 md:text-2xl">
                            ₹{{ number_format($product->price,2) }}
                        </p>

                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-row gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800 sm:pt-0 sm:border-0 sm:flex-col sm:w-auto">

                        <a
                            href="{{ route('user/productDetails', $product->slug) }}"
                            wire:navigate
                            class="flex-1 sm:flex-none"
                        >
                            <flux:button class="w-full">
                                View
                            </flux:button>
                        </a>

                        <flux:button
                            variant="danger"
                            wire:click="remove({{ $product->id }})"
                            class="flex-1 sm:flex-none"
                        >
                            Remove
                        </flux:button>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>