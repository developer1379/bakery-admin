<?php
session_start();
if (!isset($_SESSION['bakery_logged_in']) || $_SESSION['bakery_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'config.php';

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
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF7F2]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ovens & Baking - L'Amour Du Pain</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Material Icons (Round) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

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
            <div class="flex items-center gap-4">
                <button id="open-sidebar-btn" class="flex h-10 w-10 items-center justify-center rounded-xl bg-bakery-100 text-bakery-800 hover:bg-bakery-200 lg:hidden shadow-sm transition-all">
                    <span class="material-icons-round">menu</span>
                </button>
                <div class="hidden sm:block">
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-bakery-600">Bakery Management</h2>
                    <h1 id="page-title" class="font-serif text-2xl font-bold text-espresso-950">Ovens & Baking Operations</h1>
                </div>
            </div>

            <div class="flex items-center gap-4 sm:gap-6">
                <div class="hidden md:flex items-center gap-3 bg-bakery-100/60 border border-bakery-200/50 rounded-xl px-4 py-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-semibold text-bakery-800 uppercase tracking-wider">Kitchen Status: active</span>
                    <span class="text-xs text-bakery-500 font-medium">| Ovens temp: 375°F</span>
                </div>

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

            <!-- OVENS AND BATCHES VIEW -->
            <section class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Oven A status card -->
                    <div class="card p-6 bg-white rounded-2xl border border-amber-300 shadow-m-elevated flex flex-col justify-between space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <span class="material-icons-round text-2xl animate-pulse">local_fire_department</span>
                                </div>
                                <div>
                                    <h3 class="font-serif text-lg font-bold text-espresso-950">Oven Deck A</h3>
                                    <span class="text-xxs font-bold text-amber-600 uppercase tracking-wider">Primary deck oven</span>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200 uppercase">Heating</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-espresso-600">
                                <span>Temperature</span>
                                <span class="text-amber-600">385°F / 400°F</span>
                            </div>
                            <div class="w-full bg-espresso-50 h-2 rounded-full overflow-hidden">
                                <div class="bg-amber-400 h-full rounded-full" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="bg-espresso-50 rounded-xl p-3 border border-[#EAE3D5] text-xs space-y-1">
                            <div class="flex justify-between font-semibold text-espresso-800">
                                <span>Active Batch:</span>
                                <span>Classic Croissants</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Batch size:</span>
                                <span>60 Units</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Est. bake time:</span>
                                <span>3 mins left</span>
                            </div>
                        </div>
                        <button class="w-full bg-[#FFE170] text-espresso-950 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:bg-[#FFCE3B] transition-all">
                            Adjust Temperature
                        </button>
                    </div>

                    <!-- Oven B status card -->
                    <div class="card p-6 bg-white rounded-2xl border border-bakery-300 shadow-m-elevated flex flex-col justify-between space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-xl bg-bakery-100 text-bakery-600 flex items-center justify-center">
                                    <span class="material-icons-round text-2xl">local_fire_department</span>
                                </div>
                                <div>
                                    <h3 class="font-serif text-lg font-bold text-espresso-950">Oven Convection B</h3>
                                    <span class="text-xxs font-bold text-espresso-400 uppercase tracking-wider">Patisserie oven</span>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-bakery-700 bg-bakery-50 px-2 py-1 rounded border border-bakery-200 uppercase">Steady</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-espresso-600">
                                <span>Temperature</span>
                                <span class="text-bakery-600">360°F / 360°F</span>
                            </div>
                            <div class="w-full bg-espresso-50 h-2 rounded-full overflow-hidden">
                                <div class="bg-bakery-500 h-full rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="bg-espresso-50 rounded-xl p-3 border border-[#EAE3D5] text-xs space-y-1">
                            <div class="flex justify-between font-semibold text-espresso-800">
                                <span>Active Batch:</span>
                                <span>Chocolate Muffins</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Batch size:</span>
                                <span>24 Units</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Est. bake time:</span>
                                <span>18 mins left</span>
                            </div>
                        </div>
                        <button class="w-full bg-bakery-500 text-espresso-950 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:bg-bakery-600 transition-all">
                            Adjust Temperature
                        </button>
                    </div>

                    <!-- Oven C status card -->
                    <div class="card p-6 bg-white rounded-2xl border border-emerald-300 shadow-m-elevated flex flex-col justify-between space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <span class="material-icons-round text-2xl">power_settings_new</span>
                                </div>
                                <div>
                                    <h3 class="font-serif text-lg font-bold text-espresso-950">Oven Rotary C</h3>
                                    <span class="text-xxs font-bold text-espresso-400 uppercase tracking-wider">Heavy loaf oven</span>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200 uppercase">Idle / Ready</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-espresso-600">
                                <span>Temperature</span>
                                <span class="text-espresso-400">Off / Ambient</span>
                            </div>
                            <div class="w-full bg-espresso-50 h-2 rounded-full overflow-hidden">
                                <div class="bg-espresso-300 h-full rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="bg-espresso-50 rounded-xl p-3 border border-[#EAE3D5] text-xs space-y-1">
                            <div class="flex justify-between font-semibold text-espresso-800">
                                <span>Active Batch:</span>
                                <span>None</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Batch size:</span>
                                <span>-</span>
                            </div>
                            <div class="flex justify-between text-espresso-500">
                                <span>Est. bake time:</span>
                                <span>Ready</span>
                            </div>
                        </div>
                        <button class="w-full bg-emerald-500 text-espresso-950 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-600 transition-all">
                            Power On Oven
                        </button>
                    </div>
                </div>

                <!-- Baking schedule / batch queue -->
                <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300">
                    <div class="pb-4 border-b border-[#F5EDE0]">
                        <h3 class="font-serif text-lg font-bold text-espresso-950">Baking & Kitchen Schedule</h3>
                        <p class="text-xs text-espresso-500">Planned batch queues for morning and afternoon shifts.</p>
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#FAF6F0] text-xxs font-bold uppercase tracking-wider text-espresso-400">
                                    <th class="py-3 px-2">Scheduled Time</th>
                                    <th class="py-3 px-2">Bake Item</th>
                                    <th class="py-3 px-2">Planned Qty</th>
                                    <th class="py-3 px-2">Oven Assigned</th>
                                    <th class="py-3 px-2">Estimated Duration</th>
                                    <th class="py-3 px-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#FAF6F0] text-sm text-espresso-800 font-medium">
                                <tr class="hover:bg-bakery-50/50 transition-colors">
                                    <td class="py-3 px-2">04:30 AM</td>
                                    <td class="py-3 px-2 font-bold text-espresso-950">French Baguettes</td>
                                    <td class="py-3 px-2">120 Loaves</td>
                                    <td class="py-3 px-2">Oven Rotary C</td>
                                    <td class="py-3 px-2">35 Mins</td>
                                    <td class="py-3 px-2"><span class="text-xxs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 uppercase border border-emerald-200">Completed</span></td>
                                </tr>
                                <tr class="hover:bg-bakery-50/50 transition-colors">
                                    <td class="py-3 px-2">05:15 AM</td>
                                    <td class="py-3 px-2 font-bold text-espresso-950">Butter Croissants</td>
                                    <td class="py-3 px-2">80 Units</td>
                                    <td class="py-3 px-2">Oven Deck A</td>
                                    <td class="py-3 px-2">20 Mins</td>
                                    <td class="py-3 px-2"><span class="text-xxs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 uppercase border border-amber-200">In Oven</span></td>
                                </tr>
                                <tr class="hover:bg-bakery-50/50 transition-colors">
                                    <td class="py-3 px-2">06:00 AM</td>
                                    <td class="py-3 px-2 font-bold text-espresso-950">Sourdough Boules</td>
                                    <td class="py-3 px-2">45 Boules</td>
                                    <td class="py-3 px-2">Oven Rotary C</td>
                                    <td class="py-3 px-2">40 Mins</td>
                                    <td class="py-3 px-2"><span class="text-xxs font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 uppercase border border-rose-200">Queued</span></td>
                                </tr>
                                <tr class="hover:bg-bakery-50/50 transition-colors">
                                    <td class="py-3 px-2">07:30 AM</td>
                                    <td class="py-3 px-2 font-bold text-espresso-950">Belgian Chocolate Tarts</td>
                                    <td class="py-3 px-2">30 Tarts</td>
                                    <td class="py-3 px-2">Oven Convection B</td>
                                    <td class="py-3 px-2">15 Mins</td>
                                    <td class="py-3 px-2"><span class="text-xxs font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 uppercase border border-rose-200">Queued</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- CUSTOM FLOATING TOAST -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 bg-espresso-900 text-white px-5 py-3.5 rounded-2xl shadow-m-high border border-espresso-800 flex items-center gap-3 transition-all duration-300 translate-y-20 opacity-0 pointer-events-none">
        <span class="material-icons-round text-bakery-400" id="toast-icon">check_circle</span>
        <span class="text-sm font-bold" id="toast-message">Success!</span>
    </div>

    <script>
        var productsData = <?php echo json_encode($products); ?>;
        var ordersData = <?php echo json_encode($orders); ?>;
    </script>
    <!-- MAIN APP JS -->
    <script src="js/app.js"></script>
</body>
</html>
