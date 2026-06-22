<?php

use Livewire\Component;

use App\Models\Product;
use App\Models\Category;

new class extends Component
{
    public $stats;
    public $recentProducts = [];

    public function mount(){
        $this->stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'stock' => Product::sum('stock'),
        ];

         $this->recentProducts = Product::latest()
            ->take(5)
            ->get()
            ->toArray();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2 sm:p-0">
    
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        
        <!-- Total Products Card -->
        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Products</span>
                <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['products'] }}
                </p>
            </div>
        </div>

        <!-- Total Categories Card -->
        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Categories</span>
                <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><path d="M17 17A5 5 0 0 0 12 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2V7Z"/><circle cx="16" cy="12" r="2"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['categories'] }}
                </p>
            </div>
        </div>

        <!-- Active Products Card -->
        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Products</span>
                <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['active_products'] }}
                </p>
            </div>
        </div>

        <!-- Total Stock Card -->
        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Stock</span>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22" x2="12" y2="12"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['stock'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- Table Container Card -->
    <div class="flex flex-col rounded-xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
        <!-- Table Header (Since it's exactly 5 items, this adds a clean title) -->
        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">Recent Products</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">A detailed breakdown of your latest additions</p>
        </div>
        
        <!-- Flux Table Wrapper -->
        <div class="relative p-2 h-full flex-1 overflow-hidden">
            <flux:table scrollable>
                <flux:table.columns>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Product</flux:table.column>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Category</flux:table.column>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Price</flux:table.column>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Stock</flux:table.column>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($recentProducts as $product)
                        <flux:table.row class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                            <!-- Product Name Bolded -->
                            <flux:table.cell class="whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $product['name'] }}
                            </flux:table.cell>
                            
                            <!-- Eager-loaded Category Optimization -->
                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                {{ category::where('id', $product['category_id'])->value('name') ?? 'Uncategorized' }}
                            </flux:table.cell>
                            
                            <!-- Price formatted cleanly -->
                            <flux:table.cell class="whitespace-nowrap font-mono text-zinc-700 dark:text-zinc-300">
                                ${{ number_format($product['price'], 2) }}
                            </flux:table.cell>
                            
                            <!-- Dynamic Stock badges depending on inventory levels -->
                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                @if($product['stock'] == 0)
                                    <span class="text-red-600 dark:text-red-400 font-medium">Out of stock</span>
                                @elseif($product['stock'] < 10)
                                    <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $product['stock'] }} left</span>
                                @else
                                    {{ $product['stock'] }}
                                @endif
                            </flux:table.cell>
                            
                            <!-- Cool pills/badges for Active status -->
                            <flux:table.cell class="whitespace-nowrap">
                                @if($product['is_active'])
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-zinc-400/10 dark:text-zinc-400 dark:ring-zinc-400/20">
                                        Draft
                                    </span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>