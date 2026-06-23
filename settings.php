<?php
session_start();
if (!isset($_SESSION['bakery_logged_in']) || $_SESSION['bakery_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF7F2]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - L'Amour Du Pain</title>
    
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
                    <h1 id="page-title" class="font-serif text-2xl font-bold text-espresso-950">Settings</h1>
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

            <!-- SETTINGS FORM -->
            <section class="max-w-4xl space-y-6">
                <div class="bg-white rounded-2xl border border-[#EAE3D5] p-6 space-y-6">
                    <h3 class="font-serif text-xl font-bold text-espresso-950 border-b border-[#F5EDE0] pb-3">Bakery Profile Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Bakery Brand Name</label>
                            <input type="text" value="L'Amour Du Pain" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Registered Email Address</label>
                            <input type="email" value="operations@lamourdupain.in" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Kitchen Contact Number</label>
                            <input type="text" value="+91 98765 43210" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Default Base Currency</label>
                            <select class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                                <option value="INR" selected>Indian Rupee (₹)</option>
                                <option value="USD">US Dollar ($)</option>
                                <option value="EUR">Euro (€)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#EAE3D5] p-6 space-y-6">
                    <h3 class="font-serif text-xl font-bold text-espresso-950 border-b border-[#F5EDE0] pb-3">Operational Thresholds</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Low Stock Warning Threshold (Flour/Sugar)</label>
                            <input type="text" value="30 kg" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Low Stock Warning Threshold (Fats/Butter)</label>
                            <input type="text" value="15 kg" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">Max Active Baking Batches Limit</label>
                            <input type="number" value="10" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-espresso-600 uppercase tracking-wider block">GST / VAT Rate Applied (%)</label>
                            <input type="number" step="0.1" value="5.5" class="w-full px-4 py-2.5 text-sm bg-espresso-50 border border-[#EAE3D5] rounded-xl focus:outline-none focus:ring-2 focus:ring-bakery-400 font-medium">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button class="px-6 py-2.5 text-xs font-bold text-espresso-600 hover:text-espresso-950 transition-colors">
                        Reset Defaults
                    </button>
                    <button onclick="showToast('Settings saved successfully!')" class="bg-bakery-500 hover:bg-bakery-600 text-espresso-950 px-6 py-2.5 rounded-xl text-sm font-bold shadow-m-flat hover:shadow-m-fab transition-all">
                        Save Configurations
                    </button>
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
