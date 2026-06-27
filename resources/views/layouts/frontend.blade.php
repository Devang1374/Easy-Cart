<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Critical for mobile device scaling -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="flex min-h-screen flex-col">
        
        {{-- Top Announcement Bar --}}
        <div class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-2 text-center text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                🚚 Free shipping on orders above ₹999
            </div>
        </div>

        {{-- Navbar --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 gap-2">
                
                {{-- Logo --}}
                <a href="/user/homePage" class="flex shadow-xl shadow-cyan-500/50 items-center shrink-0">
                    <x-app-logo href="{{ route('homePage') }}" wire:navigate />
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden items-center gap-6 lg:flex">
                    <a wire:navigate href="{{route('homePage')}}" class="@if(request()->routeIs('homePage')) text-blue-700 @endif font-medium hover:text-blue-600"> Home </a>
                    <a wire:navigate href="{{route('user/product')}}" class="@if(request()->routeIs('user/product')) text-blue-700 @endif font-medium hover:text-blue-600"> Products </a>
                    @auth
                        <a wire:navigate href="{{route('user/order')}}" class="break-keep @if(request()->routeIs('user/order')) text-blue-700 @endif font-medium hover:text-blue-600"> Orders </a>
                    @endauth
                    <a wire:navigate href="{{route('user/cart')}}" class="@if(request()->routeIs('user/cart')) text-blue-700 @endif font-medium hover:text-blue-600"> Cart </a>
                </nav>

                {{-- Search (Desktop Only) --}}
                <div class="hidden w-full max-w-md px-4 lg:block">
                    <flux:input icon="magnifying-glass" placeholder="Search products..." />
                </div>

                {{-- Right Side Actions --}}
                <div class="flex items-center gap-1 sm:gap-2">
                    {{-- Theme Toggle --}}
                    <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />

                    <!-- Wishlist Count -->
                    <a
                        href="{{route('user/wishlist')}}"
                        wire:navigate
                        class="relative flex items-center"
                    >
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="heart"
                        >
                            <span class="hidden xl:inline">Wishlist</span>
                        </flux:button>

                        <div class="absolute -right-1 -top-1">
                            <livewire:front.wishlist-count />
                        </div>
                    </a>

                    {{-- Cart --}}
                    <a href="{{route('user/cart')}}">
                        <flux:button variant="ghost" size="sm" icon="shopping-bag">
                            <span class="hidden sm:inline"> Cart </span>
                            <livewire:front.cart-count/>
                        </flux:button>
                    </a>

                    {{-- Auth Actions --}}
                    @auth
                        <!-- Responsive menu wrapper -->
                        <div class="hidden sm:block">
                            <x-desktop-user-menu />
                        </div>
                    @else
                        <a href="/login">
                            <flux:button size="sm"> Login </flux:button>
                        </a>
                    @endauth

                    {{-- Mobile Menu Trigger --}}
                    <flux:modal.trigger name="mobile-menu">
                        <flux:button variant="ghost" icon="bars-3" class="lg:hidden" />
                    </flux:modal.trigger>
                </div>
            </div>
        </header>

        {{-- Mobile Drawer Menu --}}
        <flux:modal name="mobile-menu" class="max-w-xs sm:max-w-sm" variant="flyout">
            <div class="space-y-6 pt-4">
                {{-- Mobile Search --}}
                <flux:input icon="magnifying-glass" placeholder="Search..." />
                
                {{-- Mobile Navigation Links --}}
                <nav class="flex flex-col space-y-4 text-base font-medium">
                    <a wire:navigate href="{{route('homePage')}}" class="hover:text-blue-600 py-1"> Home </a>
                    <a wire:navigate href="{{route('user/product')}}" class="hover:text-blue-600 py-1"> Products </a>
                    @auth
                        <a wire:navigate href="{{route('user/order')}}" class="hover:text-blue-600 py-1"> My Order </a>
                    @endauth
                    <a wire:navigate href="{{route('user/cart')}}" class="hover:text-blue-600 py-1"> Cart </a>
                    
                    {{-- Mobile Specific Auth/Profile Action --}}
                    @auth
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4 mt-2">
                            <x-desktop-user-menu />
                        </div>
                    @endauth
                </nav>
            </div>
        </flux:modal>

        {{-- Main Content --}}
        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16">
                <div class="grid gap-8 grid-cols-2 lg:grid-cols-4">
                    <div class="col-span-2 lg:col-span-1">
                        <h3 class="mb-4 text-lg font-bold"> Easy-Cart </h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400"> 
                            Modern eCommerce platform built with Laravel, Livewire and Flux UI. 
                        </p>
                    </div>
                    <div>
                        <h3 class="mb-4 font-semibold text-sm sm:text-base"> Quick Links </h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{route('homePage')}}" class="hover:underline">Home</a></li>
                            <li><a href="{{route('user/product')}}" class="hover:underline">Products</a></li>
                            <li><a href="{{route('user/cart')}}" class="hover:underline">Cart</a></li>
                            @auth
                                <li><a href="{{route('user/order')}}" class="hover:underline">My Orders</a></li>
                            @endauth
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 font-semibold text-sm sm:text-base"> Customer Service </h3>
                        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li class="hover:underline cursor-pointer">Contact Us</li>
                            <li class="hover:underline cursor-pointer">Shipping Policy</li>
                            <li class="hover:underline cursor-pointer">Returns</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 font-semibold text-sm sm:text-base"> Contact </h3>
                        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li>easycart123@gmail.com</li>
                            <li>+91 99999 99999</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 border-t border-zinc-200 pt-6 text-center text-xs sm:text-sm text-zinc-500 dark:border-zinc-800">
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
