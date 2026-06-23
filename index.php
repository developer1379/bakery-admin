<?php
require_once 'auth_check.php';

// Fetch products
$pStmt = $pdo->query("SELECT id, name, category, price, description as `desc`, status, stock, limit_val as `limit`, image_url as img FROM products ORDER BY id DESC");
$products = $pStmt->fetchAll();

// Fetch orders
$oStmt = $pdo->query("SELECT id, customer, email, priority, type, status, time_ago as time, total, items_json FROM orders ORDER BY created_at DESC");
$dbOrders = $oStmt->fetchAll();
$orders = [];
foreach ($dbOrders as $o) {
    $o['items'] = json_decode($o['items_json'], true);
    unset($o['items_json']);
    $orders[] = $o;
}

// Stats computations
$salesStmt = $pdo->query("SELECT SUM(total) as total_sales FROM orders WHERE status = 'delivered'");
$salesData = $salesStmt->fetch();
$todaySales = $salesData['total_sales'] ?? 0.00;

$totalBakedItems = 0;
foreach ($orders as $o) {
    if (in_array($o['status'], ['delivered', 'dispatched', 'baking'])) {
        foreach ($o['items'] as $item) {
            $totalBakedItems += $item['qty'];
        }
    }
}

$activeStmt = $pdo->query("SELECT COUNT(*) as active_count FROM orders WHERE status != 'delivered'");
$activeData = $activeStmt->fetch();
$activeOrdersCount = $activeData['active_count'] ?? 0;

$delivStmt = $pdo->query("SELECT COUNT(*) as deliv_count FROM orders WHERE type = 'Delivery' AND status = 'dispatched'");
$delivData = $delivStmt->fetch();
$deliveriesCount = $delivData['deliv_count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF7F2]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Amour Du Pain - Bakery Admin Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Material Icons (Round) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bakery: {
                            50: '#FDFBF7',
                            100: '#FAF4E8',
                            200: '#F3E5CC',
                            300: '#E6CFAB',
                            400: '#D5B484',
                            500: '#BD935C',
                            600: '#A47743',
                            700: '#875B30',
                            800: '#6A4320',
                            900: '#4D2E14',
                            950: '#271406',
                        },
                        espresso: {
                            50: '#F5F5F5',
                            100: '#EAE9E6',
                            200: '#D1CECB',
                            300: '#AC9F9A',
                            400: '#87746E',
                            500: '#695752',
                            600: '#53433E',
                            700: '#3D312E',
                            800: '#2A201E',
                            900: '#1C1513',
                            950: '#120C0A',
                        },
                        gold: {
                            50: '#FFFDF0',
                            100: '#FFF9D4',
                            200: '#FFF0A8',
                            300: '#FFE170',
                            400: '#FFCE3B',
                            500: '#E0B01D',
                            600: '#C29314',
                            700: '#9C720D',
                            800: '#755409',
                            900: '#543C05',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    boxShadow: {
                        'm-flat': '0 2px 4px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.03)',
                        'm-elevated': '0 8px 30px rgba(77, 46, 20, 0.08)',
                        'm-high': '0 12px 40px rgba(0, 0, 0, 0.12)',
                        'm-fab': '0 4px 20px rgba(189, 147, 92, 0.4)',
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="h-full text-espresso-900 font-sans flex overflow-hidden antialiased">

    <!-- Screen overlay for mobile sidebar -->
    <div id="mobile-sidebar-overlay" class="fixed inset-0 z-40 bg-espresso-950/40 backdrop-blur-sm hidden transition-opacity duration-300 opacity-0 lg:hidden"></div>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

        <!-- HEADER -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-[#EAE3D5] flex items-center justify-between px-6 md:px-8 z-30 shrink-0">
            <!-- Sidebar Trigger (Mobile) -->
            <div class="flex items-center gap-4">
                <button id="open-sidebar-btn" class="flex h-10 w-10 items-center justify-center rounded-xl bg-bakery-100 text-bakery-800 hover:bg-bakery-200 lg:hidden shadow-sm transition-all">
                    <span class="material-icons-round">menu</span>
                </button>
                <div class="hidden sm:block">
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-bakery-600">Bakery Management</h2>
                    <h1 id="page-title" class="font-serif text-2xl font-bold text-espresso-950">Grand Dashboard</h1>
                </div>
            </div>

            <!-- Header Quick Info & Actions -->
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Kitchen Status Indicator (Desktop) -->
                <div class="hidden md:flex items-center gap-3 bg-bakery-100/60 border border-bakery-200/50 rounded-xl px-4 py-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-semibold text-bakery-800 uppercase tracking-wider">Kitchen Status: active</span>
                    <span class="text-xs text-bakery-500 font-medium">| Ovens temp: 375°F</span>
                </div>

                <!-- Notifications Panel -->
                <div class="relative">
                    <button id="notify-btn" class="relative flex h-11 w-11 items-center justify-center rounded-xl bg-espresso-50 hover:bg-espresso-100 text-espresso-700 transition-all border border-[#EAE3D5]">
                        <span class="material-icons-round">notifications</span>
                        <!-- Notification Badge -->
                        <span class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                    </button>
                    <!-- Notifications Dropdown (Hidden by default) -->
                    <div id="notify-dropdown" class="absolute right-0 mt-3 w-80 rounded-2xl bg-white p-4 shadow-m-high border border-[#EAE3D5] hidden transition-all z-50">
                        <div class="flex items-center justify-between pb-3 border-b border-[#F5EDE0]">
                            <h3 class="font-semibold text-espresso-900">Recent Alerts</h3>
                            <button class="text-xs font-semibold text-bakery-600 hover:text-bakery-800">Mark all read</button>
                        </div>
                        <ul class="mt-3 space-y-3 max-h-60 overflow-y-auto scrollbar-thin">
                            <li class="flex items-start gap-3 p-2 rounded-xl hover:bg-bakery-50 transition-colors">
                                <span class="material-icons-round text-amber-500 mt-0.5">warning</span>
                                <div>
                                    <h4 class="text-xs font-semibold text-espresso-900">Low Stock Warning</h4>
                                    <p class="text-xxs text-espresso-500 mt-0.5">Butter (President French) is below 10kg limit.</p>
                                    <span class="text-[10px] text-espresso-400 font-medium mt-1 block">5 mins ago</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 p-2 rounded-xl hover:bg-bakery-50 transition-colors">
                                <span class="material-icons-round text-emerald-500 mt-0.5">check_circle</span>
                                <div>
                                    <h4 class="text-xs font-semibold text-espresso-900">Oven Batch Completed</h4>
                                    <p class="text-xxs text-espresso-500 mt-0.5">Sourdough bread batch #14 is finished baking.</p>
                                    <span class="text-[10px] text-espresso-400 font-medium mt-1 block">15 mins ago</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 p-2 rounded-xl hover:bg-bakery-50 transition-colors">
                                <span class="material-icons-round text-blue-500 mt-0.5">shopping_cart</span>
                                <div>
                                    <h4 class="text-xs font-semibold text-espresso-900">Large Custom Cake Order</h4>
                                    <p class="text-xxs text-espresso-500 mt-0.5">Customer Sarah K. placed an order for a 3-tier Wedding Cake.</p>
                                    <span class="text-[10px] text-espresso-400 font-medium mt-1 block">1 hour ago</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Profile Dropdown (Mobile visible/clickable) -->
                <div class="relative flex items-center gap-3">
                    <img src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=100&auto=format&fit=crop&q=80" alt="Chef Avatar" class="h-11 w-11 rounded-xl object-cover ring-2 ring-bakery-200 border border-white lg:hidden">
                    <div class="hidden lg:block text-right">
                        <span class="text-xs font-bold text-bakery-600 block leading-tight">Bonjour!</span>
                        <span class="text-xs font-semibold text-espresso-900">Jean-Luc B.</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 scrollbar-thin">

            <!-- DASHBOARD VIEW -->
            <section id="tab-dashboard" class="space-y-6">
                <!-- Welcome & Stats Ribbon -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Stat Card 1 -->
                    <div class="card p-6 bg-white rounded-2xl border border-[#EAE3D5] flex items-center gap-5 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-bakery-100 text-bakery-700">
                            <span class="material-icons-round text-3xl">payments</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-espresso-500 uppercase tracking-wider block">Today's Sales</span>
                            <span class="text-2xl font-bold text-espresso-950 mt-1 block">₹<?php echo number_format($todaySales, 2); ?></span>
                            <span class="text-xs font-bold text-emerald-600 flex items-center mt-1">
                                <span class="material-icons-round text-sm">trending_up</span> +14.2% <span class="text-espresso-400 font-normal ml-1">vs yesterday</span>
                            </span>
                        </div>
                    </div>

                    <!-- Stat Card 2 -->
                    <div class="card p-6 bg-white rounded-2xl border border-[#EAE3D5] flex items-center gap-5 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <span class="material-icons-round text-3xl">cookie</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-espresso-500 uppercase tracking-wider block">Orders Baked</span>
                            <span class="text-2xl font-bold text-espresso-950 mt-1 block"><?php echo $totalBakedItems; ?> items</span>
                            <span class="text-xs font-bold text-emerald-600 flex items-center mt-1">
                                <span class="material-icons-round text-sm">trending_up</span> +8.5% <span class="text-espresso-400 font-normal ml-1">vs last week</span>
                            </span>
                        </div>
                    </div>

                    <!-- Stat Card 3 -->
                    <div class="card p-6 bg-white rounded-2xl border border-[#EAE3D5] flex items-center gap-5 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                            <span class="material-icons-round text-3xl">shopping_bag</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-espresso-500 uppercase tracking-wider block">Active Orders</span>
                            <span class="text-2xl font-bold text-espresso-950 mt-1 block"><?php echo $activeOrdersCount; ?> Pending</span>
                            <span class="text-xs font-bold text-rose-500 flex items-center mt-1">
                                <span class="material-icons-round text-sm animate-pulse">hourglass_empty</span> 4 in Ovens <span class="text-espresso-400 font-normal ml-1">right now</span>
                            </span>
                        </div>
                    </div>

                    <!-- Stat Card 4 -->
                    <div class="card p-6 bg-white rounded-2xl border border-[#EAE3D5] flex items-center gap-5 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <span class="material-icons-round text-3xl">delivery_dining</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-espresso-500 uppercase tracking-wider block">Deliveries</span>
                            <span class="text-2xl font-bold text-espresso-950 mt-1 block"><?php echo $deliveriesCount; ?> Dispatched</span>
                            <span class="text-xs font-bold text-indigo-600 flex items-center mt-1">
                                <span class="material-icons-round text-sm">navigation</span> 2 near target <span class="text-espresso-400 font-normal ml-1">ETA &lt;5m</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Interactive Baking Status Row -->
                <div class="bg-gradient-to-br from-espresso-900 to-espresso-950 rounded-2xl p-6 text-white border border-espresso-800 shadow-m-elevated relative overflow-hidden">
                    <!-- Aesthetic Background Accent -->
                    <div class="absolute right-0 top-0 h-64 w-64 bg-bakery-500/10 rounded-full blur-3xl -translate-y-12 translate-x-12"></div>
                    
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div>
                            <span class="bg-bakery-500/20 text-bakery-300 text-xxs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border border-bakery-500/30">Live Production Monitoring</span>
                            <h3 class="font-serif text-2xl font-bold text-white mt-2">Active Baking Ovens</h3>
                            <p class="text-espresso-300 text-xs mt-1">Keep an eye on current heat cycles, bake timers, and ingredient batches.</p>
                        </div>
                        
                        <!-- Oven Grid -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl">
                            <!-- Oven 1 Card -->
                            <div class="bg-espresso-850/80 border border-espresso-800 rounded-xl p-4 flex items-center gap-4">
                                <div class="relative flex items-center justify-center h-12 w-12 rounded-lg bg-amber-500/20 text-amber-400">
                                    <span class="material-icons-round text-2xl animate-pulse">local_fire_department</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-white">Oven A (Deck)</span>
                                        <span class="text-[10px] font-bold text-amber-400">385°F</span>
                                    </div>
                                    <h4 class="text-xs text-espresso-200 font-medium truncate mt-0.5">Classic Croissants</h4>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-espresso-800 h-1.5 rounded-full mt-2 overflow-hidden">
                                        <div class="bg-amber-400 h-full rounded-full transition-all duration-1000" style="width: 75%"></div>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-espresso-400">
                                        <span>75% done</span>
                                        <span class="flex items-center gap-0.5"><span class="material-icons-round text-[10px]">schedule</span> 3m left</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Oven 2 Card -->
                            <div class="bg-espresso-850/80 border border-espresso-800 rounded-xl p-4 flex items-center gap-4">
                                <div class="relative flex items-center justify-center h-12 w-12 rounded-lg bg-bakery-500/20 text-bakery-400">
                                    <span class="material-icons-round text-2xl">local_fire_department</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-white">Oven B (Convection)</span>
                                        <span class="text-[10px] font-bold text-bakery-400">360°F</span>
                                    </div>
                                    <h4 class="text-xs text-espresso-200 font-medium truncate mt-0.5">Chocolate Muffins</h4>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-espresso-800 h-1.5 rounded-full mt-2 overflow-hidden">
                                        <div class="bg-bakery-500 h-full rounded-full transition-all duration-1000" style="width: 30%"></div>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-espresso-400">
                                        <span>30% done</span>
                                        <span class="flex items-center gap-0.5"><span class="material-icons-round text-[10px]">schedule</span> 18m left</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Oven 3 Card -->
                            <div class="bg-espresso-850/80 border border-espresso-800 rounded-xl p-4 flex items-center gap-4">
                                <div class="relative flex items-center justify-center h-12 w-12 rounded-lg bg-emerald-500/20 text-emerald-400">
                                    <span class="material-icons-round text-2xl">done_all</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-white">Oven C (Rotary)</span>
                                        <span class="text-[10px] font-bold text-emerald-400">Off</span>
                                    </div>
                                    <h4 class="text-xs text-espresso-300 font-medium truncate mt-0.5">Sourdough Loaves</h4>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-espresso-800 h-1.5 rounded-full mt-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full" style="width: 100%"></div>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px] text-emerald-400">
                                        <span>Bake completed</span>
                                        <span class="font-bold text-emerald-400">Ready</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Sales Trend (Line Chart) -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] lg:col-span-2 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#F5EDE0]">
                            <div>
                                <h3 class="font-serif text-lg font-bold text-espresso-950">Bake Sales Trends</h3>
                                <p class="text-xs text-espresso-500">Weekly tracking of item group sales revenue.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="px-3 py-1.5 text-xs font-bold text-bakery-700 bg-bakery-100 rounded-xl">Weekly</button>
                                <button class="px-3 py-1.5 text-xs font-semibold text-espresso-500 hover:text-espresso-800 hover:bg-espresso-50 rounded-xl transition-all">Monthly</button>
                            </div>
                        </div>
                        <div class="mt-6 relative h-80">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Category Share (Doughnut Chart) -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300 flex flex-col justify-between">
                        <div class="pb-4 border-b border-[#F5EDE0]">
                            <h3 class="font-serif text-lg font-bold text-espresso-950">Top Baked Categories</h3>
                            <p class="text-xs text-espresso-500">Popularity index of items baked daily.</p>
                        </div>
                        <div class="my-6 relative h-60 flex items-center justify-center">
                            <canvas id="categoryShareChart"></canvas>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xxs font-bold text-espresso-500 mt-2 uppercase tracking-wide border-t border-[#F5EDE0] pt-4">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span class="h-2 w-2 rounded-full bg-[#8B5A2B]"></span>
                                <span>Bread (42%)</span>
                            </div>
                            <div class="flex items-center gap-1.5 justify-center">
                                <span class="h-2 w-2 rounded-full bg-[#BD935C]"></span>
                                <span>Pastries (35%)</span>
                            </div>
                            <div class="flex items-center gap-1.5 justify-center">
                                <span class="h-2 w-2 rounded-full bg-[#E0B01D]"></span>
                                <span>Cakes (15%)</span>
                            </div>
                            <div class="flex items-center gap-1.5 justify-center">
                                <span class="h-2 w-2 rounded-full bg-[#FDA4AF]"></span>
                                <span>Cookies (8%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders and Top Ingredients -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Orders Table -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] lg:col-span-2 hover:shadow-m-elevated transition-all duration-300">
                        <div class="flex items-center justify-between pb-4 border-b border-[#F5EDE0]">
                            <div>
                                <h3 class="font-serif text-lg font-bold text-espresso-950">Recent Kitchen Orders</h3>
                                <p class="text-xs text-espresso-500">Live feed of pending and baking orders.</p>
                            </div>
                            <a href="orders.php" class="text-xs font-bold text-bakery-600 hover:text-bakery-800 transition-colors flex items-center gap-1">
                                View Board <span class="material-icons-round text-sm">arrow_forward</span>
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-[#FAF6F0] text-xxs font-bold uppercase tracking-wider text-espresso-400">
                                        <th class="py-3 px-2">Order ID</th>
                                        <th class="py-3 px-2">Customer</th>
                                        <th class="py-3 px-2">Items</th>
                                        <th class="py-3 px-2">Total</th>
                                        <th class="py-3 px-2">Status</th>
                                        <th class="py-3 px-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#FAF6F0] text-sm" id="dashboard-recent-orders">
                                    <!-- Rendered dynamically by app.js -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Low Stock Alerts / Fast moving ingredients -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300">
                        <div class="pb-4 border-b border-[#F5EDE0]">
                            <h3 class="font-serif text-lg font-bold text-espresso-950">Inventory Level Warning</h3>
                            <p class="text-xs text-espresso-500">Critical ingredients requiring purchase refills.</p>
                        </div>
                        
                        <div class="mt-4 space-y-4">
                            <!-- Stock Alert 1 -->
                            <div class="flex items-center gap-4 bg-rose-50/50 border border-rose-100 rounded-xl p-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                                    <span class="material-icons-round text-xl">inventory</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold text-espresso-900 truncate">President French Butter</h4>
                                        <span class="text-xxs font-bold text-rose-600 bg-rose-100/50 px-1.5 py-0.5 rounded uppercase font-semibold">Low 8%</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-rose-100 h-1.5 rounded-full mt-2 overflow-hidden">
                                        <div class="bg-rose-500 h-full rounded-full" style="width: 8%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-espresso-500 mt-1 font-semibold">
                                        <span>Stock: 8 kg / 100 kg limit</span>
                                        <span class="text-rose-600">Reorder now</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Alert 2 -->
                            <div class="flex items-center gap-4 bg-amber-50/50 border border-amber-100 rounded-xl p-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                    <span class="material-icons-round text-xl">inventory</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold text-espresso-900 truncate">T55 French Flour</h4>
                                        <span class="text-xxs font-bold text-amber-600 bg-amber-100/50 px-1.5 py-0.5 rounded uppercase font-semibold">Warning 22%</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-amber-100 h-1.5 rounded-full mt-2 overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full" style="width: 22%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-espresso-500 mt-1 font-semibold">
                                        <span>Stock: 45 kg / 200 kg limit</span>
                                        <span class="text-amber-600">Refill requested</span>
                                    </div>
                                </div>
                            </div>

                            <a href="inventory.php" class="w-full block py-2.5 rounded-xl border border-bakery-300 text-xs font-bold text-bakery-700 hover:bg-bakery-50 hover:text-bakery-900 transition-all text-center">
                                Open Inventory Registry
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- MODAL: ORDER DETAILS -->
    <div id="order-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-950/40 backdrop-blur-sm hidden transition-all duration-300 opacity-0">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-m-high border border-[#EAE3D5] overflow-hidden transform scale-95 transition-transform duration-300">
            <!-- Modal Header -->
            <div class="flex items-center justify-between bg-espresso-900 px-6 py-4 text-white">
                <div>
                    <h3 class="font-serif text-lg font-bold" id="m-order-id">Order Details</h3>
                    <span class="text-xxs text-bakery-400 font-bold uppercase tracking-wider" id="m-order-time">15 mins ago</span>
                </div>
                <button onclick="closeOrderModal()" class="text-espresso-400 hover:text-white transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-6">
                <!-- Customer Details -->
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-bakery-100 text-bakery-800 flex items-center justify-center font-bold text-lg" id="m-cust-initials">
                        HM
                    </div>
                    <div>
                        <h4 class="font-bold text-espresso-900" id="m-cust-name">Henri Matisse</h4>
                        <p class="text-xs text-espresso-500" id="m-cust-email">henri@fauvism-art.fr</p>
                    </div>
                    <span class="ml-auto text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200" id="m-order-status">Pending</span>
                </div>

                <!-- Items Table -->
                <div class="space-y-3">
                    <h5 class="text-xs font-extrabold text-espresso-600 uppercase tracking-wider">Ordered Items</h5>
                    <div class="border border-[#EAE3D5] rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FAF6F0] border-b border-[#EAE3D5]">
                                <tr>
                                    <th class="py-2.5 px-3 font-bold text-espresso-600">Item</th>
                                    <th class="py-2.5 px-3 font-bold text-espresso-600 text-center">Qty</th>
                                    <th class="py-2.5 px-3 font-bold text-espresso-600 text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F5EDE0]" id="m-order-items">
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold">Chocolate Gateau (8")</td>
                                    <td class="py-2.5 px-3 text-center font-bold">1</td>
                                    <td class="py-2.5 px-3 text-right font-bold">₹45.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Price summary -->
                <div class="bg-[#FAF6F0]/80 rounded-xl p-4 space-y-2 border border-[#EAE3D5]">
                    <div class="flex justify-between text-xs text-espresso-600">
                        <span>Subtotal</span>
                        <span class="font-semibold" id="m-order-subtotal">₹45.00</span>
                    </div>
                    <div class="flex justify-between text-xs text-espresso-600">
                        <span>GST / VAT (5.5%)</span>
                        <span class="font-semibold" id="m-order-tax">₹2.48</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-espresso-950 pt-2 border-t border-[#EAE3D5]">
                        <span>Grand Total</span>
                        <span id="m-order-total">₹47.48</span>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="p-4 bg-[#FAF6F0] border-t border-[#EAE3D5] flex items-center justify-between gap-3">
                <button onclick="closeOrderModal()" class="px-4 py-2 text-xs font-bold text-espresso-600 hover:text-espresso-900">
                     Close Panel
                </button>
                <div class="flex items-center gap-2">
                    <button class="bg-[#FFE170] text-espresso-950 px-4 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-[#FFCE3B] transition-all" onclick="updateOrderStatus('baking')">
                        Send to Oven
                    </button>
                    <button class="bg-bakery-500 text-espresso-950 px-4 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-bakery-600 transition-all" onclick="updateOrderStatus('delivered')">
                        Complete Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOM FLOATING TOAST -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 bg-espresso-900 text-white px-5 py-3.5 rounded-2xl shadow-m-high border border-espresso-800 flex items-center gap-3 transition-all duration-300 translate-y-20 opacity-0 pointer-events-none">
        <span class="material-icons-round text-bakery-400" id="toast-icon">check_circle</span>
        <span class="text-sm font-bold" id="toast-message">Success! Order completed.</span>
    </div>

    <script>
        var productsData = <?php echo json_encode($products); ?>;
        var ordersData = <?php echo json_encode($orders); ?>;
    </script>
    <!-- MAIN APP JS -->
    <script src="js/app.js"></script>
</body>
</html>
