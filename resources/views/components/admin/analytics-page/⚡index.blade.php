<?php

use Livewire\Component;

//
    use App\Models\product;
    use App\Models\User;
    use App\Models\orderTable;

new class extends Component
{
   
        public $todayRevenue = 0;
        public $monthRevenue = 0;
        public $yearRevenue = 0;
        public $averageOrderValue = 0;
        public $paidOrders = 0;
        public $pendingPayments = 0;
        public $customers = 0;
        public $lowStockProducts = 0;
 

    public function mount()
    {
        $this->loadAnalytics();
        $this->loadTopProducts();
        $this->loadRevenueChart();  
        $this->loadCategorySales();
        $this->loadOrderStatusChart();
    }

    public function loadAnalytics()
    {
        $paidOrders = orderTable::where('pyment', 'PAID');

        $this->todayRevenue = (clone $paidOrders)
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $this->monthRevenue = (clone $paidOrders)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $this->yearRevenue = (clone $paidOrders)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $this->averageOrderValue = round(
            orderTable::where('pyment', 'PAID')->avg('total_amount'),
            2
        );

        $this->paidOrders = orderTable::where('pyment', 'PAID')->count();

        $this->pendingPayments = orderTable::where('pyment', 'pending')->count();

        $this->customers = User::count();

        $this->lowStockProducts = Product::where('stock', '<=', 5)->count();
    }

    // loadRevenueChart
    public $revenueChart = [];
    public function loadRevenueChart()
    {
        $orders = orderTable::query()
            ->where('pyment', 'PAID')
            ->whereDate('created_at', '>=', now()->subDays(29))
            ->get();

        $data = [];

        // Initialize last 30 days with 0 revenue
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[$date] = 0;
        }

        // Sum revenue for each day
        foreach ($orders as $order) {
            $date = $order->created_at->format('Y-m-d');

            if (isset($data[$date])) {
                $data[$date] += $order->total_amount;
            }
        }

        $this->revenueChart = [
            'categories' => array_keys($data),
            'series' => array_values($data),
        ];

    }

    // order status chart
    public $orderStatusChart = [];
    public function loadOrderStatusChart()
    {
        $statuses = [
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ];

        $series = [];

        foreach ($statuses as $status) {
            $series[] = orderTable::where('status', $status)->count();
        }

        $this->orderStatusChart = [
            'labels' => [
                'Pending',
                'Processing',
                'Shipped',
                'Delivered',
                'Cancelled',
            ],
            'series' => $series,
        ];
    }

    // Top selling Products
    public $topProducts = [];
    public function loadTopProducts()
    {
        $this->topProducts = product::query()
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('order_tables', 'order_items.order_table_id', '=', 'order_tables.id')
            ->where('order_tables.pyment', 'PAID')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }

    // Top Selling categories
    public $categorySales = [];
    public function loadCategorySales()
    {
        $this->categorySales = \App\Models\Category::query()
            ->select('categories.id', 'categories.name')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as revenue')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('order_tables', 'order_items.order_table_id', '=', 'order_tables.id')
            ->where('order_tables.pyment', 'PAID')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
    }


};
?>

<div class="space-y-6 p-4 md:p-6 w-full">
    <div class="relative w-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <div class="mb-4">

            <h1 class="text-3xl font-bold">
                Analytics
            </h1>

            <p class="mt-2 text-zinc-500">
                Monitor your store performance and sales.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">

            <livewire:admin.analytics-page.analytics-card
                title="Revenue Today"
                :value="'₹'.number_format($todayRevenue, 2)"
                icon="currency-rupee"
                color="bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Revenue This Month"
                :value="'₹'.number_format($monthRevenue, 2)"
                icon="calendar-days"
                color="bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Revenue This Year"
                :value="'₹'.number_format($yearRevenue, 2)"
                icon="chart-bar"
                color="bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Average Order Value"
                :value="'₹'.number_format($averageOrderValue, 2)"
                icon="shopping-bag"
                color="bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Paid Orders"
                :value="$paidOrders"
                icon="check-circle"
                color="bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Pending Payments"
                :value="$pendingPayments"
                icon="clock"
                color="bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Total Customers"
                :value="$customers"
                icon="users"
                color="bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400"
            />

            <livewire:admin.analytics-page.analytics-card
                title="Low Stock Products"
                :value="$lowStockProducts"
                icon="exclamation-triangle"
                color="bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400"
            />

        </div>
    </div>

    <div class="relative w-full flex flex-col gap-5 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6">
                <h2 class="text-xl font-bold">
                    Revenue (Last 30 Days)
                </h2>

                <p class="text-sm text-zinc-500">
                    Daily revenue from paid orders.
                </p>
            </div>

            <div
                id="revenue-chart"
                wire:ignore
                class="h-[350px]"
            ></div>

        </div>

        <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6">
                <h2 class="text-xl font-bold">
                    Orders by Status
                </h2>

                <p class="text-sm text-zinc-500">
                    Distribution of all orders.
                </p>
            </div>

            <div
                id="order-status-chart"
                class="h-[350px]"
                wire:ignore
            ></div>

        </div>

        <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6">

                <h2 class="text-xl font-bold">
                    Top Selling Products
                </h2>

                <p class="text-sm text-zinc-500">
                    Based on paid orders.
                </p>

            </div>

            <div class="space-y-4">

                @forelse($topProducts as $index => $product)

                    <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">

                        <div class="flex items-center gap-4 min-w-0">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">

                                #{{ $index + 1 }}

                            </div>

                            <div class="min-w-0">

                                <p class="font-semibold truncate">
                                    {{ $product->name }}
                                </p>

                                <p class="text-sm text-zinc-500">
                                    {{ $product->total_sold }} sold
                                </p>

                            </div>

                        </div>

                        <flux:badge color="green" class="shrink-0">
                            Bestseller
                        </flux:badge>

                    </div>

                @empty

                    <p class="text-center text-zinc-500 py-8">
                        No sales yet.
                    </p>

                @endforelse

            </div>

        </div>

        <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6">

                <h2 class="text-xl font-bold">
                    Sales by Category
                </h2>

                <p class="text-sm text-zinc-500">
                    Revenue generated from paid orders.
                </p>

            </div>

            <div
                id="category-sales-chart"
                wire:ignore
                class="h-[400px]"
            ></div>

        </div>
    </div>

    @script
        <script>
        
        document.addEventListener('livewire:navigated', () => {

            const revenueData = @json($revenueChart);

            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: true
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },

                series: [{
                    name: 'Revenue',
                    data: revenueData.series
                }],

                xaxis: {
                    categories: revenueData.categories
                },

                stroke: {
                    curve: 'smooth',
                    width: 3
                },

                dataLabels: {
                    enabled: true
                },

                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                },

                colors: ['#3B82F6'],
                
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },

                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return "₹" + value;
                        }
                    }
                },

                tooltip: {
                    y: {
                        formatter: function(value) {
                            return "₹" + value;
                        }
                    }
                }
            };

            document.querySelector("#revenue-chart").innerHTML = "";

            new ApexCharts(
                document.querySelector("#revenue-chart"),
                options
            ).render();

            const statusData = @json($orderStatusChart);

            const statusOptions = {

                chart: {
                    type: 'donut',
                    height: 350,
                    toolbar: {
                        show: true
                    }
                },

                series: statusData.series,

                labels: statusData.labels,

                legend: {
                    position: 'bottom'
                },

                dataLabels: {
                    enabled: true
                },

                colors: [
                    '#facc15', // Pending
                    '#3b82f6', // Processing
                    '#8b5cf6', // Shipped
                    '#22c55e', // Delivered
                    '#ef4444', // Cancelled
                ]
            };

            document.querySelector("#order-status-chart").innerHTML = "";

            new ApexCharts(
                document.querySelector("#order-status-chart"),
                statusOptions
            ).render();
        });

        const categorySales = @json($categorySales);

        const categoryOptions = {

            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: true
                }
            },

            series: [{
                name: 'Revenue',
                data: categorySales.map(item => Number(item.revenue))
            }],

            xaxis: {
                categories: categorySales.map(item => item.name)
            },

            colors: ['#3B82F6'],

            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: true
                }
            },

            dataLabels: {
                enabled: false
            },

            tooltip: {
                y: {
                    formatter: value => "₹" + value.toLocaleString()
                }
            },

            xaxis: {
                categories: categorySales.map(item => item.name),
                labels: {
                    formatter: value => "₹" + Number(value).toLocaleString()
                }
            }

        };

        document.querySelector("#category-sales-chart").innerHTML = "";

        new ApexCharts(
            document.querySelector("#category-sales-chart"),
            categoryOptions
        ).render();
        </script>
    @endscript
</div>