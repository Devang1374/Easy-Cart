<?php

use Livewire\Component;

use App\Models\product;
use App\Models\Category;
use App\Models\orderTable;

use Livewire\Attributes\On;  

new class extends Component
{
    public $stats;
    public $recentProducts = [];
    public $lowStockProducts;

    public $showUpdate = false;

    #[on('stats-updated')]
    public function mount(){
        $this->stats = [
            'products' => product::count(),
            'categories' => Category::count(),
            'active_products' => product::where('is_active', true)->count(),
            'stock' => product::sum('stock'),
            'totalOrders' => orderTable::count(),
            'pendingOrders' => orderTable::where('status','pending')->count(),
            'processingOrders' => orderTable::where('status','processing')->count(),
            'deliveredOrders' => orderTable::where('status','delivered')->count(),
        ];

        $this->recentProducts = product::latest()
            ->take(5)
            ->get()
            ->toArray();

        $this->lowStockProducts = product::where('stock', '<=', '2')
            ->where('is_active', '1')
            ->take(5)
            ->get()
            ->toArray();
    }

    public $selectedProduct;
    public function updateStock($id){
        $this->selectedProduct = product::with('images')->findOrFail($id);
        $this->showUpdate = true;
    }

    public $stock;
    public function update($id){
        $this->validate([
            'stock' => 'required'
        ]);

        product::where('id', $id)->update(['stock' => $this->stock]);
        $this->mount();
        $this->showUpdate = false;
    }
};
?>

<div class="flex w-full flex-col gap-6 rounded-xl p-2 sm:p-0">
    
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
        
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

        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders</span>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['totalOrders'] }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Pending Orders</span>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['pendingOrders'] }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders In Processing</span>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-3-6.7"/>
                        <polyline points="21 3 21 9 15 9"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['processingOrders'] }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-zinc-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Delivered Orders</span>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <p class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ $stats['deliveredOrders'] }}
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-col rounded-xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">Recent Products</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">A detailed breakdown of your latest additions</p>
        </div>
        
        <div class="w-full overflow-x-auto p-2">
            <flux:table scrollable container:class="w-full min-w-[600px]">
                <flux:table.columns>
                    <flux:table.column sticky class="bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">Product</flux:table.column>
                    <flux:table.column class="text-zinc-600 dark:text-zinc-400">Category</flux:table.column>
                    <flux:table.column class="text-zinc-600 dark:text-zinc-400">Price</flux:table.column>
                    <flux:table.column class="text-zinc-600 dark:text-zinc-400">Stock</flux:table.column>
                    <flux:table.column class="text-zinc-600 dark:text-zinc-400">Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($recentProducts as $product)
                        <flux:table.row class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900 whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $product['name'] }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                {{ category::where('id', $product['category_id'])->value('name') ?? 'Uncategorized' }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="whitespace-nowrap font-mono text-zinc-700 dark:text-zinc-300">
                                ${{ number_format($product['price'], 2) }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                @if($product['stock'] == 0)
                                    <span class="text-red-600 dark:text-red-400 font-medium">Out of stock</span>
                                @elseif($product['stock'] < 10)
                                    <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $product['stock'] }} left</span>
                                @else
                                    {{ $product['stock'] }}
                                @endif
                            </flux:table.cell>
                            
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

    @if($lowStockProducts)
    <div class="flex flex-col rounded-xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">Low Stock Products</h2>
        </div>
        
        <div class="w-full overflow-x-auto p-2">
            <flux:table
                class="w-full min-w-[900px] table-fixed"
                container:class="w-full"
            >
                <flux:table.columns>
                    <flux:table.column sticky class="w-1/6 bg-white dark:bg-zinc-900">
                        Product
                    </flux:table.column>
        
                    <flux:table.column class="w-1/6">
                        Category
                    </flux:table.column>
        
                    <flux:table.column class="w-1/6">
                        Price
                    </flux:table.column>
        
                    <flux:table.column class="w-1/6">
                        Stock
                    </flux:table.column>
        
                    <flux:table.column class="w-1/6">
                        Status
                    </flux:table.column>
        
                    <flux:table.column class="w-1/6">
                        Action
                    </flux:table.column>
                </flux:table.columns>
        
                <flux:table.rows>
                    @foreach($lowStockProducts as $product)
                        <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
        
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900">
                                <span class="block truncate font-medium">
                                    {{ $product['name'] }}
                                </span>
                            </flux:table.cell>
        
                            <flux:table.cell>
                                {{ category::where('id', $product['category_id'])->value('name') ?? 'Uncategorized' }}
                            </flux:table.cell>
        
                            <flux:table.cell class="font-mono">
                                ₹{{ number_format($product['price'], 2) }}
                            </flux:table.cell>
        
                            <flux:table.cell>
                                @if($product['stock'] == 0)
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        Out of stock
                                    </span>
                                @elseif($product['stock'] < 10)
                                    <span class="font-medium text-amber-600 dark:text-amber-400">
                                        {{ $product['stock'] }} left
                                    </span>
                                @else
                                    {{ $product['stock'] }}
                                @endif
                            </flux:table.cell>
        
                            <flux:table.cell>
                                @if($product['is_active'])
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        Draft
                                    </span>
                                @endif
                            </flux:table.cell>
        
                            <flux:table.cell>
                                <flux:button
                                    wire:click="updateStock({{ $product['id'] }})"
                                    variant="primary"
                                    color="blue"
                                    size="sm"
                                >
                                    Update Stock
                                </flux:button>
                            </flux:table.cell>
        
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <flux:modal wire:model="showUpdate" class="w-full max-w-4xl flex flex-col gap-4">
        @if(isset($selectedProduct))
        <div>
            <div class="rounded-xl border p-4">
                <h3 class="mb-4 font-semibold">
                    Product Details
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        @if(isset($selectedProduct->images[0]))
                            <img
                                src="{{ $selectedProduct->images[0]->image }}"
                                class="h-16 w-16 shrink-0 rounded-lg object-cover"
                            >
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">
                                {{ $selectedProduct['name'] }}
                            </div>
                            <div class="text-sm text-zinc-500">
                                Stock: {{ $selectedProduct->stock}}
                            </div>
                        </div>
                        <div class="shrink-0 font-medium">
                            ₹{{ number_format($selectedProduct->price, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <flux:input
                    wire:model="stock"
                    name="stock"
                    :label="__('Product stock')"
                    type="number"
                    :placeholder="__('Enter Product stock...')"
                />
        <flux:table.cell variant="strong">
            <flux:button wire:click="update({{$selectedProduct['id']}})" variant="primary" color="blue" size="sm">Update</flux:button>
        </flux:table.cell>
        @endif
    </flux:modal>
    @endif
</div>