<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="sticky top-0 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            @if(auth()->user()->is_admin)
            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>

                <flux:navbar.item icon="layout-grid" :href="route('category')" :current="request()->routeIs('category')" wire:navigate>
                    {{ __('Category') }}
                </flux:navbar.item>

                <flux:navbar.item icon="layout-grid" :href="route('product')" :current="request()->routeIs('product')" wire:navigate>
                    {{ __('Product') }}
                </flux:navbar.item>

                <flux:navbar.item icon="layout-grid" :href="route('orderPage')" :current="request()->routeIs('orderPage')" wire:navigate>
                    {{ __('Order-Page') }}
                </flux:navbar.item>
                
                <flux:navbar.item icon="layout-grid" :href="route('analytics')" :current="request()->routeIs('analytics')" wire:navigate>
                    {{ __('Analytics') }}
                </flux:navbar.item>

                <flux:navbar.item icon="layout-grid" :href="route('coupon')" :current="request()->routeIs('coupon')" wire:navigate>
                    {{ __('Coupon') }}
                </flux:navbar.item>

                <flux:navbar.item icon="layout-grid" :href="route('banner')" :current="request()->routeIs('banner')" wire:navigate>
                    {{ __('Banner') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        :label="__('Documentation')"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
            @endif
        </flux:header>

        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('category')" :current="request()->routeIs('category')" wire:navigate>
                        {{ __('Categories')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('product')" :current="request()->routeIs('product')" wire:navigate>
                        {{ __('Products')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('orderPage')" :current="request()->routeIs('orderPage')" wire:navigate>
                        {{ __('Order-Page')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('analytics')" :current="request()->routeIs('analytics')" wire:navigate>
                        {{ __('Analytics')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('coupon')" :current="request()->routeIs('coupon')" wire:navigate>
                        {{ __('Coupon')  }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('banner')" :current="request()->routeIs('banner')" wire:navigate>
                        {{ __('Banner')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        <main class="w-full min-h-[calc(100vh-4rem)] p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>