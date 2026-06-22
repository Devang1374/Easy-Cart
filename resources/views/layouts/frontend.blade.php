<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">

    <div class="flex min-h-screen flex-col">

        {{-- Top Announcement Bar --}}
        <div class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                🚚 Free shipping on orders above ₹999
            </div>
        </div>

        {{-- Navbar --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">

                {{-- Logo --}}
                <a href="/" class="flex shadow-xl shadow-cyan-500/50 items-center gap-3">
                    <x-app-logo  href="{{ route('dashboard') }}" wire:navigate />
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden items-center gap-8 lg:flex">
                    <a wire:navigate href="{{route('homePage')}}" class="@if(request()->routeIs('homePage')) text-blue-700 @endif font-medium hover:text-blue-600">
                        Home
                    </a>

                    <a wire:navigate href="{{route('user/product')}}" wire:navigate class="@if(request()->routeIs('user/product')) text-blue-700 @endif font-medium hover:text-blue-600">
                        Products
                    </a>

                    <a wire:navigate href="{{route('user/category')}}" class="@if(request()->routeIs('user/category')) text-blue-700 @endif font-medium hover:text-blue-600">
                        Categories
                    </a>
                </nav>

                {{-- Search --}}
                <div class="hidden w-full max-w-md px-6 lg:block">
                    <flux:input
                        icon="magnifying-glass"
                        placeholder="Search products..."
                    />
                </div>

                {{-- Right Side --}}
                <div class="flex items-center gap-2">

                    {{-- Theme Toggle --}}
                    <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />

                    {{-- Cart --}}
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="shopping-bag"
                    >
                        <span class="hidden sm:inline">
                            Cart
                        </span>

                        <span class="rounded-full bg-blue-600 px-2 py-0.5 text-xs text-white">
                            0
                        </span>
                    </flux:button>

                    @auth
                         <x-desktop-user-menu />
                    @else

                        <a href="/login">
                            <flux:button size="sm">
                                Login
                            </flux:button>
                        </a>

                    @endauth

                    {{-- Mobile Menu --}}
                    <flux:modal.trigger name="mobile-menu">
                        <flux:button
                            variant="ghost"
                            icon="bars-3"
                            class="lg:hidden"
                        />
                    </flux:modal.trigger>

                </div>
            </div>
        </header>

        {{-- Mobile Menu --}}
        <flux:modal name="mobile-menu" class="max-w-sm">

            <div class="space-y-6">

                <flux:input
                    icon="magnifying-glass"
                    placeholder="Search..."
                />

                <nav class="space-y-3">

                    <a href="{{route('homePage')}}" class="block">
                        Home
                    </a>

                    <a href="{{route('user/product')}}" class="block">
                        Products
                    </a>

                    <a href="{{route('user/category')}}" class="block">
                        Categories
                    </a>

                    <a href="/cart" class="block">
                        Cart
                    </a>

                </nav>

            </div>

        </flux:modal>

        {{-- Main Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-16">

                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <h3 class="mb-4 text-lg font-bold">
                            Easy-Cart
                        </h3>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Modern eCommerce platform built with Laravel,
                            Livewire and Flux UI.
                        </p>
                    </div>

                    <div>
                        <h3 class="mb-4 font-semibold">
                            Quick Links
                        </h3>

                        <ul class="space-y-2 text-sm">
                            <li><a href="/">Home</a></li>
                            <li><a href="/products">Products</a></li>
                            <li><a href="/categories">Categories</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-4 font-semibold">
                            Customer Service
                        </h3>

                        <ul class="space-y-2 text-sm">
                            <li>Contact Us</li>
                            <li>Shipping Policy</li>
                            <li>Returns</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-4 font-semibold">
                            Contact
                        </h3>

                        <ul class="space-y-2 text-sm">
                            <li>support@example.com</li>
                            <li>+91 99999 99999</li>
                        </ul>
                    </div>

                </div>

                <div class="mt-12 border-t border-zinc-200 pt-6 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    © {{ date('Y') }} Easy-Cart. All rights reserved.
                </div>

            </div>
        </footer>

    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

</body>
</html>