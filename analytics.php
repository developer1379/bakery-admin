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
    <title>Analytics - L'Amour Du Pain</title>
    
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
            <div class="flex items-center gap-4">
                <button id="open-sidebar-btn" class="flex h-10 w-10 items-center justify-center rounded-xl bg-bakery-100 text-bakery-800 hover:bg-bakery-200 lg:hidden shadow-sm transition-all">
                    <span class="material-icons-round">menu</span>
                </button>
                <div class="hidden sm:block">
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-bakery-600">Bakery Management</h2>
                    <h1 id="page-title" class="font-serif text-2xl font-bold text-espresso-950">Yield & Demand Reports</h1>
                </div>
            </div>

            <div class="flex items-center gap-4 sm:gap-6">
                <div class="hidden md:flex items-center gap-3 bg-bakery-100/60 border border-bakery-200/50 rounded-xl px-4 py-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-semibold text-bakery-800 uppercase tracking-wider">Kitchen Status: active</span>
                    <span class="text-xs text-bakery-500 font-medium">| Ovens temp: 375°F</span>
                </div>

                <div class="relative">
                    <button id="notify-btn" class="relative flex h-11 w-11 items-center justify-center rounded-xl bg-espresso-50 hover:bg-espresso-100 text-espresso-700 transition-all border border-[#EAE3D5]">
                        <span class="material-icons-round">notifications</span>
                        <span class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                    </button>
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

            <!-- ANALYTICS PANELS -->
            <section id="tab-analytics" class="space-y-6">
                
                <!-- Filter Summary Ribbon -->
                <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-[#EAE3D5]">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-round text-bakery-500">calendar_today</span>
                        <span class="text-xs font-bold text-espresso-800">Date Range: June 15 - June 22 (Last 7 Days)</span>
                    </div>
                    <button class="px-4 py-2 border border-[#EAE3D5] text-xs font-bold text-espresso-700 hover:bg-espresso-50 rounded-xl transition-all flex items-center gap-1.5">
                        <span class="material-icons-round text-sm">download</span> Export CSV
                    </button>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart 1: Sales Trend -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300">
                        <div class="pb-4 border-b border-[#F5EDE0]">
                            <h3 class="font-serif text-lg font-bold text-espresso-950">Artisan Bake Sales Trend</h3>
                            <p class="text-xs text-espresso-500">Sales value by bread vs pastries categories.</p>
                        </div>
                        <div class="mt-6 relative h-72">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2: Peak Hours -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300">
                        <div class="pb-4 border-b border-[#F5EDE0]">
                            <h3 class="font-serif text-lg font-bold text-espresso-950">Hourly Peak Demand</h3>
                            <p class="text-xs text-espresso-500">Peak baking times and hourly order volume.</p>
                        </div>
                        <div class="mt-6 relative h-72">
                            <canvas id="hourlyPeakChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 3: Ingredient consumption -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300">
                        <div class="pb-4 border-b border-[#F5EDE0]">
                            <h3 class="font-serif text-lg font-bold text-espresso-950">Daily Ingredient Yield (kg)</h3>
                            <p class="text-xs text-espresso-500">Weight of primary flour, butter, and baking supplies utilized.</p>
                        </div>
                        <div class="mt-6 relative h-72">
                            <canvas id="ingredientChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 4: Category distribution -->
                    <div class="card bg-white p-6 rounded-2xl border border-[#EAE3D5] hover:shadow-m-elevated transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="pb-4 border-b border-[#F5EDE0]">
                                <h3 class="font-serif text-lg font-bold text-espresso-950">Baked Category Share</h3>
                                <p class="text-xs text-espresso-500">Proportional representation of baking recipes.</p>
                            </div>
                            <div class="my-6 relative h-48 flex items-center justify-center">
                                <canvas id="categoryShareChart"></canvas>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xxs font-bold text-espresso-500 uppercase tracking-wide border-t border-[#F5EDE0] pt-4">
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
