// bakery admin dashboard app logic

// Global State
var productsData = [];
var ordersData = [];
var charts = {};

function handleLogout() {
    sessionStorage.removeItem("bakery_logged_in");
    window.location.href = "logout.php";
}

// DOM Ready
document.addEventListener("DOMContentLoaded", () => {
    initNavigation();
    initNotifications();
    initSearchAndFilters();
    initRippleEffect();
    
    // Fetch dashboard/catalog data dynamically
    Promise.all([
        fetch('api/get_products.php').then(r => {
            if (!r.ok) throw new Error('Unauthorized or server error');
            return r.json();
        }),
        fetch('api/get_orders.php').then(r => {
            if (!r.ok) throw new Error('Unauthorized or server error');
            return r.json();
        })
    ])
    .then(([products, orders]) => {
        productsData = products;
        ordersData = orders;
        
        // Render dynamic page sections
        renderProductsGrid();
        renderOrdersBoard();
        renderDashboardRecentOrders();
        renderNewOrderProducts();
        renderOvensSchedule();
        initCharts();
        
        // Hide preloader once data is successfully loaded and rendered
        const preloader = document.getElementById('bakery-preloader');
        if (preloader) {
            preloader.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                preloader.remove();
            }, 600);
        }
    })
    .catch(err => {
        console.error('Failed to load dashboard data:', err);
        // Fallback hide preloader on failure so user isn't stuck
        const preloader = document.getElementById('bakery-preloader');
        if (preloader) {
            preloader.classList.add('opacity-0', 'pointer-events-none');
        }
    });
});


// 1. NAVIGATION & TABS
function initNavigation() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("mobile-sidebar-overlay");
    const openBtn = document.getElementById("open-sidebar-btn");
    const closeBtn = document.getElementById("close-sidebar-btn");
    const pageTitle = document.getElementById("page-title");

    // Open/Close Sidebar (Mobile)
    if (openBtn && sidebar && overlay) {
        openBtn.addEventListener("click", () => {
            sidebar.classList.remove("-translate-x-full");
            overlay.classList.remove("hidden");
            setTimeout(() => overlay.classList.add("opacity-100"), 50);
        });
    }

    const closeSidebar = () => {
        if (sidebar && overlay) {
            sidebar.classList.add("-translate-x-full");
            overlay.classList.remove("opacity-100");
            setTimeout(() => overlay.classList.add("hidden"), 300);
        }
    };

    if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
    if (overlay) overlay.addEventListener("click", closeSidebar);

    // Tab Swapping
    const navButtons = document.querySelectorAll("[data-tab]");
    navButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const targetTab = btn.getAttribute("data-tab");
            switchTab(targetTab);
            
            // On mobile, close sidebar after navigation
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });

    // Handle Quick Links (dashboard triggers)
    document.querySelectorAll("[data-tab-trigger]").forEach(trigger => {
        trigger.addEventListener("click", (e) => {
            e.preventDefault();
            const targetTab = trigger.getAttribute("data-tab-trigger");
            switchTab(targetTab);
        });
    });
}

function switchTab(tabId) {
    // Check if the tab ID is supported or falls back to dashboard
    const validTabs = ["dashboard", "orders", "products", "analytics", "settings"];
    let id = validTabs.includes(tabId) ? tabId : "dashboard";

    // Toggle Pane
    document.querySelectorAll(".tab-pane").forEach(pane => {
        pane.classList.add("hidden");
    });
    const activePane = document.getElementById(`tab-${id}`);
    if (activePane) {
        activePane.classList.remove("hidden");
    }

    // Toggle Nav Button highlight
    document.querySelectorAll("[data-tab]").forEach(btn => {
        btn.classList.remove("active");
        if (btn.getAttribute("data-tab") === id) {
            btn.classList.add("active");
        }
    });

    // Update Title
    const pageTitle = document.getElementById("page-title");
    const titles = {
        dashboard: "Grand Dashboard",
        orders: "Live Kitchen Queue",
        products: "Baking Catalog & Recipes",
        analytics: "Yield & demand Reports",
        settings: "Bakery Operations Panel"
    };
    pageTitle.textContent = titles[id] || "Bakery Suite";

    // Trigger chart redraw to fix scaling issues
    if (id === 'analytics' || id === 'dashboard') {
        setTimeout(() => {
            Object.values(charts).forEach(chart => chart.resize());
        }, 100);
    }
}

// 2. CHARTS SETUP (Chart.js)
function initCharts() {
    const ctxSales = document.getElementById('salesTrendChart')?.getContext('2d');
    const ctxShare = document.getElementById('categoryShareChart')?.getContext('2d');
    const ctxHourly = document.getElementById('hourlyPeakChart')?.getContext('2d');
    const ctxIngredients = document.getElementById('ingredientChart')?.getContext('2d');

    if (ctxSales) {
        const breadGradient = ctxSales.createLinearGradient(0, 0, 0, 300);
        breadGradient.addColorStop(0, 'rgba(139, 90, 43, 0.4)');
        breadGradient.addColorStop(1, 'rgba(139, 90, 43, 0.0)');

        const pastriesGradient = ctxSales.createLinearGradient(0, 0, 0, 300);
        pastriesGradient.addColorStop(0, 'rgba(189, 147, 92, 0.4)');
        pastriesGradient.addColorStop(1, 'rgba(189, 147, 92, 0.0)');

        charts.sales = new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Artisan Bread',
                        data: [420, 480, 510, 490, 620, 850, 790],
                        borderColor: '#8B5A2B',
                        backgroundColor: breadGradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    },
                    {
                        label: 'Pastries & Viennoiserie',
                        data: [310, 360, 420, 390, 510, 780, 710],
                        borderColor: '#BD935C',
                        backgroundColor: pastriesGradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 11 },
                            color: '#53433E'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#87746E' } },
                    y: { grid: { color: '#F5EDE0' }, ticks: { color: '#87746E' } }
                }
            }
        });
    }

    if (ctxShare) {
        charts.share = new Chart(ctxShare, {
            type: 'doughnut',
            data: {
                labels: ['Bread', 'Pastries', 'Cakes', 'Cookies'],
                datasets: [{
                    data: [42, 35, 15, 8],
                    backgroundColor: ['#8B5A2B', '#BD935C', '#E0B01D', '#FDA4AF'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '75%'
            }
        });
    }

    if (ctxHourly) {
        charts.hourly = new Chart(ctxHourly, {
            type: 'line',
            data: {
                labels: ['6 AM', '8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM'],
                datasets: [{
                    label: 'Order Volume',
                    data: [15, 48, 35, 24, 18, 30, 22],
                    borderColor: '#BD935C',
                    backgroundColor: 'rgba(189, 147, 92, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#87746E' } },
                    y: { grid: { color: '#F5EDE0' }, ticks: { color: '#87746E' } }
                }
            }
        });
    }

    if (ctxIngredients) {
        charts.ingredients = new Chart(ctxIngredients, {
            type: 'bar',
            data: {
                labels: ['Flour (T55)', 'Butter (Norman)', 'Yeast', 'Sugar', 'Berries'],
                datasets: [{
                    label: 'Used (kg)',
                    data: [120, 85, 8, 42, 15],
                    backgroundColor: '#BD935C',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#87746E' } },
                    y: { grid: { color: '#F5EDE0' }, ticks: { color: '#87746E' } }
                }
            }
        });
    }
}

// 3. TOAST & NOTIFICATIONS
function showToast(message, type = 'success') {
    const toast = document.getElementById("toast");
    const toastMsg = document.getElementById("toast-message");
    const toastIcon = document.getElementById("toast-icon");

    toastMsg.textContent = message;
    
    if (type === 'success') {
        toastIcon.textContent = 'check_circle';
        toastIcon.className = 'material-icons-round text-emerald-400';
    } else if (type === 'warning') {
        toastIcon.textContent = 'warning';
        toastIcon.className = 'material-icons-round text-amber-400';
    }

    toast.classList.remove("translate-y-20", "opacity-0");
    toast.classList.add("translate-y-0", "opacity-100");

    setTimeout(() => {
        toast.classList.add("translate-y-20", "opacity-0");
        toast.classList.remove("translate-y-0", "opacity-100");
    }, 3500);
}

function initNotifications() {
    const notifyBtn = document.getElementById("notify-btn");
    const notifyDropdown = document.getElementById("notify-dropdown");

    if (notifyBtn && notifyDropdown) {
        notifyBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            notifyDropdown.classList.toggle("hidden");
        });

        document.addEventListener("click", () => {
            notifyDropdown.classList.add("hidden");
        });

        notifyDropdown.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    }
}

// 4. PRODUCTS MANAGEMENT (CRUD & Render)
function renderProductsGrid(filterText = "", filterCategory = "All Items") {
    const grid = document.getElementById("products-grid");
    if (!grid) return;
    grid.innerHTML = "";

    const filtered = productsData.filter(p => {
        const matchesSearch = p.name.toLowerCase().includes(filterText.toLowerCase()) || 
                             p.desc.toLowerCase().includes(filterText.toLowerCase());
        const matchesCategory = filterCategory === "All Items" || p.category === filterCategory;
        return matchesSearch && matchesCategory;
    });

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-12 flex flex-col items-center justify-center text-espresso-400">
                <span class="material-icons-round text-6xl">bakery_dining</span>
                <p class="font-serif text-lg font-bold mt-2">No bakery items found</p>
                <p class="text-xs">Try broadening your search filters or add a new recipe.</p>
            </div>
        `;
        return;
    }

    filtered.forEach(p => {
        const statusColors = {
            'In Stock': 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Low Stock': 'bg-amber-50 text-amber-700 border-amber-200',
            'Out of Stock': 'bg-rose-50 text-rose-700 border-rose-200'
        };

        const card = document.createElement("div");
        card.className = "card bg-white rounded-2xl border border-[#EAE3D5] overflow-hidden flex flex-col hover:shadow-m-elevated transition-all duration-300";
        card.innerHTML = `
            <div class="relative h-48 bg-espresso-100 overflow-hidden">
                <img src="${p.img || 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400'}" alt="${p.name}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                <span class="absolute top-3 right-3 text-[10px] font-extrabold px-2.5 py-1 rounded-full border shadow-sm uppercase tracking-wider ${statusColors[p.status]}">${p.status}</span>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                    <span class="text-xxs font-extrabold text-bakery-600 uppercase tracking-widest">${p.category}</span>
                    <h3 class="font-serif text-lg font-bold text-espresso-950 mt-1">${p.name}</h3>
                    <p class="text-xs text-espresso-500 mt-2 line-clamp-2">${p.desc}</p>
                </div>
                <div class="mt-4 pt-4 border-t border-[#FAF6F0] flex items-center justify-between">
                    <div>
                        <span class="text-xxs text-espresso-400 font-bold block uppercase tracking-wider">Price</span>
                        <span class="text-lg font-bold text-espresso-950">₹${parseFloat(p.price).toFixed(2)}</span>
                    </div>
                    <div>
                        <span class="text-xxs text-espresso-400 font-bold block text-right uppercase tracking-wider">Daily Stock</span>
                        <span class="text-xs font-semibold ${p.status === 'Out of Stock' ? 'text-rose-500 font-bold' : 'text-espresso-700'} block text-right">
                            ${p.status === 'Out of Stock' ? 'Sold Out' : `${p.stock} / ${p.limit} left`}
                        </span>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function initSearchAndFilters() {
    // Products search
    const prodSearch = document.getElementById("product-search");
    if (prodSearch) {
        prodSearch.addEventListener("input", (e) => {
            const activeCategoryBtn = document.querySelector(".category-btn.active");
            const activeCategory = activeCategoryBtn ? activeCategoryBtn.textContent : "All Items";
            renderProductsGrid(e.target.value, activeCategory);
        });
    }

    // Products category switcher
    const categoryBtns = document.querySelectorAll(".category-btn");
    categoryBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            categoryBtns.forEach(b => b.classList.remove("active", "text-espresso-900", "bg-bakery-100"));
            categoryBtns.forEach(b => {
                if(b !== btn) b.classList.add("text-espresso-500", "hover:bg-espresso-50");
            });
            btn.classList.add("active");
            btn.classList.remove("text-espresso-500", "hover:bg-espresso-50");
            
            const searchVal = prodSearch ? prodSearch.value : "";
            renderProductsGrid(searchVal, btn.textContent);
        });
    });

    // Orders search
    const orderSearch = document.getElementById("order-search");
    if (orderSearch) {
        orderSearch.addEventListener("input", (e) => {
            renderOrdersBoard(e.target.value);
        });
    }
}

// Add Product Modal Toggles
function openAddProductModal() {
    const modal = document.getElementById("product-modal");
    modal.classList.remove("hidden");
    setTimeout(() => modal.classList.add("modal-active"), 50);
}

function closeAddProductModal() {
    const modal = document.getElementById("product-modal");
    modal.classList.remove("modal-active");
    setTimeout(() => modal.classList.add("hidden"), 300);
}

function saveProduct(event) {
    event.preventDefault();
    
    const name = document.getElementById("p-name").value;
    const category = document.getElementById("p-category").value;
    const price = parseFloat(document.getElementById("p-price").value);
    const desc = document.getElementById("p-desc").value;
    const stock = parseInt(document.getElementById("p-stock").value);
    const status = document.getElementById("p-status").value;
    const img = document.getElementById("p-img").value;

    fetch('api/add_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, category, price, desc, stock, status, img })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            productsData.unshift(res.product);
            renderProductsGrid();
            closeAddProductModal();
            showToast(`New item "${name}" added successfully!`);
            event.target.reset();
        } else {
            alert('Failed to add product: ' + (res.error || 'Unknown error'));
        }
    })
    .catch(e => {
        console.error(e);
        alert('An error occurred while adding the product.');
    });
}


// 5. ORDERS & KANBAN MANAGEMENT
let currentSelectedOrderId = null;

function renderOrdersBoard(filterText = "") {
    const cols = {
        pending: document.getElementById("col-pending"),
        baking: document.getElementById("col-baking"),
        dispatched: document.getElementById("col-dispatched"),
        delivered: document.getElementById("col-delivered")
    };

    // Clean columns
    Object.values(cols).forEach(col => { if(col) col.innerHTML = ""; });

    // Filtered orders
    const filtered = ordersData.filter(o => {
        return o.customer.toLowerCase().includes(filterText.toLowerCase()) || 
               o.id.toLowerCase().includes(filterText.toLowerCase()) ||
               o.items.some(item => item.name.toLowerCase().includes(filterText.toLowerCase()));
    });

    filtered.forEach(o => {
        const col = cols[o.status];
        if (!col) return;

        const priorityColors = {
            'ASAP': 'bg-rose-50 text-rose-700 border-rose-100',
            'Standard': 'bg-espresso-50 text-espresso-500 border-[#EAE3D5]',
            'Schedule': 'bg-indigo-50 text-indigo-700 border-indigo-100'
        };

        const itemSum = o.items.map(item => `${item.qty}x ${item.name}`).join(", ");
        const totalVal = o.items.reduce((acc, item) => acc + (parseFloat(item.price) * parseInt(item.qty)), 0);

        const card = document.createElement("div");
        card.className = "bg-white border rounded-xl p-4 hover:shadow-m-elevated transition-all cursor-pointer relative";
        if (o.status === 'baking') {
            card.classList.add('border-amber-200', 'shadow-sm');
        } else {
            card.classList.add('border-[#EAE3D5]');
        }
        if (o.status === 'delivered') {
            card.classList.add('opacity-75', 'hover:opacity-100');
        }

        card.innerHTML = `
            <div class="flex items-center justify-between" onclick="openOrderModal('${o.id}')">
                <span class="text-xs font-bold text-espresso-400">${o.id}</span>
                <span class="text-xxs font-bold px-2 py-0.5 rounded border uppercase ${priorityColors[o.priority] || 'bg-espresso-50 text-espresso-700 border-espresso-200'}">${o.priority}</span>
            </div>
            <div onclick="openOrderModal('${o.id}')">
                <h4 class="font-bold text-sm text-espresso-950 mt-2">${o.customer}</h4>
                <p class="text-xs text-espresso-600 mt-1 truncate">${itemSum}</p>
            </div>
            ${o.status === 'baking' ? `
                <div class="w-full bg-[#FAF6F0] h-1 rounded-full mt-3 overflow-hidden">
                    <div class="bg-amber-400 h-full rounded-full animate-pulse" style="width: 65%"></div>
                </div>
            ` : ''}
            <div class="mt-4 pt-3 border-t border-[#F5EDE0] flex items-center justify-between">
                <span class="text-sm font-bold text-espresso-950">₹${totalVal.toFixed(2)}</span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-espresso-400 font-semibold flex items-center gap-0.5">
                        <span class="material-icons-round text-xs">schedule</span> ${o.time}
                    </span>
                </div>
            </div>
        `;
        col.appendChild(card);
    });

    // Update Live Count inside Navbar & Stat Cards
    const countBadge = document.querySelector('[data-tab="orders"] .bg-bakery-500');
    if (countBadge) {
        const activeCount = ordersData.filter(o => o.status !== 'delivered').length;
        countBadge.textContent = activeCount;
    }
    renderDashboardRecentOrders();
}

// Order Modal Toggles
function openOrderModal(orderId) {
    currentSelectedOrderId = orderId;
    const order = ordersData.find(o => o.id === orderId);
    if (!order) return;

    // Set Customer Info
    document.getElementById("m-order-id").textContent = `Order ${order.id}`;
    document.getElementById("m-order-time").textContent = order.time;
    document.getElementById("m-cust-name").textContent = order.customer;
    document.getElementById("m-cust-email").textContent = order.email;
    document.getElementById("m-order-status").textContent = order.status.toUpperCase();
    
    // Initials
    const names = order.customer.split(" ");
    const initials = names.map(n => n[0]).join("").toUpperCase();
    document.getElementById("m-cust-initials").textContent = initials;

    // Status classes
    const statusEl = document.getElementById("m-order-status");
    statusEl.className = "ml-auto text-xs font-bold px-2.5 py-1 rounded-full border uppercase ";
    if (order.status === 'pending') statusEl.classList.add('text-rose-700', 'bg-rose-50', 'border-rose-200');
    if (order.status === 'baking') statusEl.classList.add('text-amber-700', 'bg-amber-50', 'border-amber-200');
    if (order.status === 'dispatched') statusEl.classList.add('text-indigo-700', 'bg-indigo-50', 'border-indigo-200');
    if (order.status === 'delivered') statusEl.classList.add('text-emerald-700', 'bg-emerald-50', 'border-emerald-200');

    // Build Items Table
    const itemsTbody = document.getElementById("m-order-items");
    itemsTbody.innerHTML = "";
    
    let subtotal = 0;
    order.items.forEach(item => {
        const itemCost = parseFloat(item.price) * parseInt(item.qty);
        subtotal += itemCost;
        
        const row = document.createElement("tr");
        row.innerHTML = `
            <td class="py-2.5 px-3 font-semibold">${item.name}</td>
            <td class="py-2.5 px-3 text-center font-bold">${item.qty}</td>
            <td class="py-2.5 px-3 text-right font-bold">₹${itemCost.toFixed(2)}</td>
        `;
        itemsTbody.appendChild(row);
    });

    const tax = subtotal * 0.055;
    const total = subtotal + tax;

    document.getElementById("m-order-subtotal").textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById("m-order-tax").textContent = `₹${tax.toFixed(2)}`;
    document.getElementById("m-order-total").textContent = `₹${total.toFixed(2)}`;

    // Show modal
    const modal = document.getElementById("order-modal");
    modal.classList.remove("hidden");
    setTimeout(() => modal.classList.add("modal-active"), 50);
}

function closeOrderModal() {
    const modal = document.getElementById("order-modal");
    modal.classList.remove("modal-active");
    setTimeout(() => modal.classList.add("hidden"), 300);
}

function updateOrderStatus(newStatus) {
    if (!currentSelectedOrderId) return;
    const order = ordersData.find(o => o.id === currentSelectedOrderId);
    if (!order) return;

    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentSelectedOrderId, status: newStatus })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            order.status = newStatus;
            if (newStatus === 'baking') order.priority = 'Oven A';
            if (newStatus === 'delivered') order.priority = 'Paid';
            renderOrdersBoard();
            closeOrderModal();
            showToast(`Order ${order.id} status updated to: ${newStatus}`);
        } else {
            alert('Failed to update order: ' + (res.error || 'Unknown error'));
        }
    })
    .catch(e => {
        console.error(e);
        alert('An error occurred while updating the order status.');
    });
}

// Add Order Modal Toggles
function openNewOrderModal() {
    const modal = document.getElementById("new-order-modal");
    modal.classList.remove("hidden");
    setTimeout(() => modal.classList.add("modal-active"), 50);
}

function closeNewOrderModal() {
    const modal = document.getElementById("new-order-modal");
    modal.classList.remove("modal-active");
    setTimeout(() => modal.classList.add("hidden"), 300);
}

function saveNewOrder(event) {
    event.preventDefault();

    const customer = document.getElementById("o-cust-name").value;
    const priority = document.getElementById("o-priority").value;
    const type = document.getElementById("o-type").value;

    // Collect checked items
    const checkedItems = [];
    const itemCheckboxes = document.querySelectorAll('input[name="o-items"]:checked');
    
    itemCheckboxes.forEach(cb => {
        const [name, price] = cb.value.split('|');
        // Find adjacent qty input
        const qtyInput = cb.closest('label').querySelector('input[type="number"]');
        const qty = qtyInput ? parseInt(qtyInput.value) : 1;
        checkedItems.push({
            name,
            price: parseFloat(price),
            qty
        });
    });

    if (checkedItems.length === 0) {
        alert("Please select at least one item.");
        return;
    }

    fetch('api/add_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ customer, priority, type, items: checkedItems })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            ordersData.unshift(res.order);
            renderOrdersBoard();
            closeNewOrderModal();
            showToast(`Logged new order ${res.order.id} successfully!`);
            event.target.reset();
        } else {
            alert('Failed to create order: ' + (res.error || 'Unknown error'));
        }
    })
    .catch(e => {
        console.error(e);
        alert('An error occurred while creating the order.');
    });
}


// 6. MICRO-INTERACTIONS (Ripple effect)
function initRippleEffect() {
    document.addEventListener("click", (e) => {
        const target = e.target.closest("button, .nav-item, .category-btn");
        if (!target) return;
        
        // Add ripple-btn style dynamically
        target.classList.add("ripple-btn");

        const rect = target.getBoundingClientRect();
        const ripple = document.createElement("span");
        
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        ripple.classList.add("ripple");
        
        // Remove existing ripples
        const existing = target.querySelectorAll(".ripple");
        existing.forEach(r => r.remove());

        target.appendChild(ripple);
    });
}

function renderDashboardRecentOrders() {
    const tableBody = document.getElementById("dashboard-recent-orders");
    if (!tableBody) return;
    tableBody.innerHTML = "";

    // Show top 5 recent orders
    const recentOrders = ordersData.slice(0, 5);

    recentOrders.forEach(o => {
        const itemSum = o.items.map(item => `${item.qty}x ${item.name}`).join(", ");
        const totalVal = o.items.reduce((acc, item) => acc + (parseFloat(item.price) * parseInt(item.qty)), 0);

        const statusClasses = {
            pending: "text-rose-700 bg-rose-50 border-rose-200",
            baking: "text-amber-700 bg-amber-50 border-amber-200",
            dispatched: "text-indigo-700 bg-indigo-50 border-indigo-200",
            delivered: "text-emerald-700 bg-emerald-50 border-emerald-200"
        };

        const tr = document.createElement("tr");
        tr.className = "hover:bg-bakery-50/50 transition-colors";
        tr.innerHTML = `
            <td class="py-3 px-2 font-bold text-espresso-950">${o.id}</td>
            <td class="py-3 px-2 font-semibold text-espresso-800">${o.customer}</td>
            <td class="py-3 px-2 text-espresso-600 truncate max-w-[200px]">${itemSum}</td>
            <td class="py-3 px-2 font-bold text-espresso-950">₹${totalVal.toFixed(2)}</td>
            <td class="py-3 px-2">
                <span class="text-xxs font-bold px-2 py-0.5 rounded-full border uppercase ${statusClasses[o.status] || ''}">${o.status}</span>
            </td>
            <td class="py-3 px-2 text-right">
                <button onclick="openOrderModal('${o.id}')" class="text-xs font-bold text-bakery-600 hover:text-bakery-800 transition-colors">Details</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });
}

function renderNewOrderProducts() {
    const listContainer = document.getElementById("new-order-items-list");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    
    productsData.forEach(p => {
        const label = document.createElement("label");
        label.className = "flex items-center justify-between text-xs font-semibold text-espresso-800";
        label.innerHTML = `
            <span class="flex items-center gap-2">
                <input type="checkbox" name="o-items" value="${p.name}|${parseFloat(p.price)}" class="rounded border-[#EAE3D5] text-bakery-600 focus:ring-bakery-400">
                ${p.name} (₹${parseFloat(p.price).toFixed(2)})
            </span>
            <input type="number" min="1" max="20" value="1" class="w-12 px-1 py-0.5 border border-[#EAE3D5] rounded text-center">
        `;
        listContainer.appendChild(label);
    });
}

function renderOvensSchedule() {
    const tableBody = document.getElementById("ovens-schedule-tbody");
    if (!tableBody) return;
    tableBody.innerHTML = "";

    ordersData.forEach((o, idx) => {
        const itemNames = o.items.map(item => item.name);
        const totalQty = o.items.reduce((acc, item) => acc + parseInt(item.qty), 0);
        const itemsStr = itemNames.join(', ');

        let statusLabel = 'Queued';
        let statusClass = 'bg-rose-100 text-rose-700 border-rose-200';
        if (o.status === 'delivered') {
            statusLabel = 'Completed';
            statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        } else if (o.status === 'baking') {
            statusLabel = 'In Oven';
            statusClass = 'bg-amber-100 text-amber-700 border-amber-200';
        } else if (o.status === 'dispatched') {
            statusLabel = 'Dispatched';
            statusClass = 'bg-indigo-100 text-indigo-700 border-indigo-200';
        }

        const ovensList = ['Oven Deck A', 'Oven Convection B', 'Oven Rotary C'];
        const assignedOven = ovensList[idx % 3];
        const duration = (15 + (idx * 5)) + ' Mins';
        
        const d = new Date();
        d.setMinutes(d.getMinutes() - (idx * 30));
        const hours = d.getHours();
        const minutes = d.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const timeStr = `${formattedHours}:${formattedMinutes} ${ampm}`;

        const tr = document.createElement("tr");
        tr.className = "hover:bg-bakery-50/50 transition-colors";
        tr.innerHTML = `
            <td class="py-3 px-2">${timeStr}</td>
            <td class="py-3 px-2 font-bold text-espresso-950">${itemsStr}</td>
            <td class="py-3 px-2">${totalQty} Units</td>
            <td class="py-3 px-2">${assignedOven}</td>
            <td class="py-3 px-2">${duration}</td>
            <td class="py-3 px-2">
                <span class="text-xxs font-bold px-2 py-0.5 rounded-full uppercase border ${statusClass}">
                    ${statusLabel}
                </span>
            </td>
        `;
        tableBody.appendChild(tr);
    });
}
