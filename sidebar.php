<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once 'config.php';

// Get active orders count
$badgeStmt = $pdo->query("SELECT COUNT(*) as active_count FROM orders WHERE status != 'delivered'");
$badgeData = $badgeStmt->fetch();
$sidebarActiveOrders = $badgeData['active_count'] ?? 0;

// Get low stock count
$lowStockStmt = $pdo->query("SELECT COUNT(*) as low_count FROM inventory WHERE stock <= limit_threshold");
$lowStockData = $lowStockStmt->fetch();
$sidebarLowStockCount = $lowStockData['low_count'] ?? 0;
?>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-espresso-900 text-espresso-100 transition-all duration-300 -translate-x-full lg:translate-x-0 lg:static lg:z-auto shadow-m-elevated border-r border-espresso-800">
    <div class="flex h-20 items-center justify-between px-6 border-b border-espresso-800">
        <a href="index.php" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-bakery-500 text-espresso-950 shadow-m-flat">
                <span class="material-icons-round text-2xl text-white">bakery_dining</span>
            </div>
            <div>
                <h1 class="font-serif text-lg font-bold leading-tight text-white tracking-wide">L'Amour Du Pain</h1>
                <span class="text-xs font-semibold text-bakery-400 uppercase tracking-widest">Bakery Suite</span>
            </div>
        </a>
        <button id="close-sidebar-btn" class="flex h-10 w-10 items-center justify-center rounded-lg text-espresso-400 hover:bg-espresso-800 hover:text-white lg:hidden">
            <span class="material-icons-round">close</span>
        </button>
    </div>

    <nav class="flex-1 space-y-1 px-4 py-6 overflow-y-auto scrollbar-thin">
        <div>
            <span class="px-3 text-xxs font-bold text-espresso-400 uppercase tracking-widest block mb-2">Workspace</span>
            <ul class="space-y-1">
                <li>
                    <a href="index.php" class="nav-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="nav-item <?php echo ($currentPage == 'orders.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">receipt_long</span>
                        <span>Live Orders</span>
                        <?php if ($sidebarActiveOrders > 0): ?>
                        <span id="sidebar-orders-badge" class="ml-auto bg-bakery-500 text-espresso-950 text-xs px-2 py-0.5 rounded-full font-bold"><?php echo $sidebarActiveOrders; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="products.php" class="nav-item <?php echo ($currentPage == 'products.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">cake</span>
                        <span>Bake Catalog</span>
                    </a>
                </li>
                <li>
                    <a href="analytics.php" class="nav-item <?php echo ($currentPage == 'analytics.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">query_stats</span>
                        <span>Analytics</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="pt-6">
            <span class="px-3 text-xxs font-bold text-espresso-400 uppercase tracking-widest block mb-2">Operations</span>
            <ul class="space-y-1">
                <li>
                    <a href="ovens.php" class="nav-item <?php echo ($currentPage == 'ovens.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">soup_kitchen</span>
                        <span>Ovens & Baking</span>
                        <span class="ml-auto text-amber-400 text-xs font-semibold flex items-center gap-0.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span> 3 Active
                        </span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="nav-item <?php echo ($currentPage == 'inventory.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">inventory_2</span>
                        <span>Inventory</span>
                        <?php if ($sidebarLowStockCount > 0): ?>
                        <span class="ml-auto bg-rose-500/20 text-rose-300 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Stock Warning</span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="pt-6">
            <span class="px-3 text-xxs font-bold text-espresso-400 uppercase tracking-widest block mb-2">Administration</span>
            <ul class="space-y-1">
                <li>
                    <a href="settings.php" class="nav-item <?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200">
                        <span class="material-icons-round text-xl">settings</span>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="p-4 border-t border-espresso-800 bg-espresso-950/30">
        <div class="flex items-center gap-3">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=100&auto=format&fit=crop&q=80" alt="Chef Avatar" class="h-11 w-11 rounded-xl object-cover ring-2 ring-bakery-500/30">
                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-espresso-900"></span>
            </div>
            <div class="flex-1 overflow-hidden">
                <h4 class="text-sm font-semibold text-white truncate">Jean-Luc Boulanger</h4>
                <span class="text-xs text-bakery-400 truncate block">Master Baker Chef</span>
            </div>
            <a href="logout.php" class="text-espresso-400 hover:text-white transition-colors duration-150" title="Logout">
                <span class="material-icons-round">logout</span>
            </a>
        </div>
    </div>
</aside>
