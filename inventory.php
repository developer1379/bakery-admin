<?php
require_once 'auth_check.php';

// Fetch inventory items
$invStmt = $pdo->query("SELECT name, category, stock, max_stock, limit_threshold, unit_cost, supplier FROM inventory ORDER BY name ASC");
$inventory = $invStmt->fetchAll();

// Count low stock items (where stock <= limit_threshold)
$lowStockCount = 0;
foreach ($inventory as $item) {
    if ($item['stock'] <= $item['limit_threshold']) {
        $lowStockCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF7F2]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - L'Amour Du Pain</title>
    
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
                    <h1 id="page-title" class="font-serif text-2xl font-bold text-espresso-950">Inventory Registry</h1>
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

            <!-- INVENTORY REGISTRY VIEW -->
            <section class="space-y-6">
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-[#EAE3D5]">
                    <div class="relative w-full sm:w-80">
                        <span class="material-icons-round text-espresso-400 absolute left-3 top-2.5 text-lg">search</span>
                        <input type="text" placeholder="Search ingredients..." class="w-full pl-9 pr-4 py-2 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                    </div>
                    <button class="flex items-center gap-1.5 bg-bakery-500 text-espresso-950 px-4 py-2 rounded-xl text-sm font-bold shadow-m-flat hover:bg-bakery-600 transition-all">
                        <span class="material-icons-round text-lg">add</span> Add Ingredient
                    </button>
                </div>

                <?php if ($lowStockCount > 0): ?>
                <!-- Stock warning banner -->
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                            <span class="material-icons-round animate-bounce">warning</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-espresso-950">Critical Stock Warning!</h4>
                            <p class="text-xs text-espresso-600 mt-0.5"><?php echo $lowStockCount; ?> key ingredient<?php echo $lowStockCount > 1 ? 's are' : ' is'; ?> below safety limit thresholds. Replenish immediately to avoid baking interruption.</p>
                        </div>
                    </div>
                    <button class="bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-rose-700 transition-all">
                        Order Refills
                    </button>
                </div>
                <?php endif; ?>

                <!-- Ingredient Table -->
                <div class="card bg-white rounded-2xl border border-[#EAE3D5] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#FAF6F0] border-b border-[#EAE3D5] text-xxs font-bold uppercase tracking-wider text-espresso-600">
                                    <th class="py-3 px-4">Ingredient Name</th>
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4">Stock level</th>
                                    <th class="py-3 px-4">Minimum Limit</th>
                                    <th class="py-3 px-4">Unit Cost</th>
                                    <th class="py-3 px-4">Supplier</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F5EDE0] text-sm text-espresso-800 font-medium">
                                <?php foreach ($inventory as $item): 
                                    $isLow = $item['stock'] <= $item['limit_threshold'];
                                    $percent = $item['max_stock'] > 0 ? round(($item['stock'] / $item['max_stock']) * 100) : 0;
                                    $statusColor = 'bg-emerald-500';
                                    $textColor = 'text-emerald-600';
                                    if ($isLow) {
                                        $statusColor = 'bg-rose-500';
                                        $textColor = 'text-rose-600';
                                    } elseif ($percent <= 25) {
                                        $statusColor = 'bg-amber-500';
                                        $textColor = 'text-amber-600';
                                    }
                                ?>
                                <tr class="hover:bg-bakery-50/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full <?php echo $statusColor; ?>"></span>
                                            <span class="font-bold text-espresso-950"><?php echo htmlspecialchars($item['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td class="py-3 px-4 <?php echo $textColor; ?> font-bold"><?php echo htmlspecialchars($item['stock']); ?> kg / <?php echo htmlspecialchars($item['max_stock']); ?> kg</td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['limit_threshold']); ?> kg</td>
                                    <td class="py-3 px-4">₹<?php echo number_format($item['unit_cost'], 2); ?> / kg</td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($item['supplier']); ?></td>
                                    <td class="py-3 px-4 text-right">
                                        <button class="text-xs font-bold text-bakery-600 hover:text-bakery-800">Quick Order</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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

    <!-- MAIN APP JS -->
    <script src="js/app.js"></script>
</body>
</html>
