<?php
$host = '193.203.184.228';
$user = 'u793412290_bakery';
$pass = 'Bakery@1020';
$db   = 'u793412290_bakery';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected successfully to database.\n";

    // 1. Table: users
    echo "Creating 'users' table...\n";
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");

    // Insert admin/12345678 (using plaintext as requested in prompt, or password_hash if preferred, but we will store it securely or handle direct match. Let's use plaintext for simplicity of 'admin, 12345678' direct check or password_hash. Let's use password_hash and we will verify in PHP)
    $hashedPassword = password_hash('12345678', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashedPassword]);
    echo "User 'admin' created.\n";

    // 2. Table: products
    echo "Creating 'products' table...\n";
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        description TEXT,
        status VARCHAR(50) NOT NULL,
        stock INT NOT NULL,
        limit_val INT NOT NULL,
        image_url VARCHAR(255)
    )");

    // Seed products
    $products = [
        [
            'name' => 'Butter Croissant',
            'category' => 'Pastries',
            'price' => 150.00,
            'description' => 'Crispy, flaky french style layered puff pastry made with 100% Normandy butter.',
            'status' => 'In Stock',
            'stock' => 80,
            'limit_val' => 120,
            'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&auto=format&fit=crop&q=80'
        ],
        [
            'name' => 'Artisan Sourdough',
            'category' => 'Bread',
            'price' => 320.00,
            'description' => 'Wild yeast slow fermented boule with a robust blistered crust and open airy crumb.',
            'status' => 'Low Stock',
            'stock' => 5,
            'limit_val' => 30,
            'image_url' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=400&auto=format&fit=crop&q=80'
        ],
        [
            'name' => 'Chocolate Truffle Gateau',
            'category' => 'Cakes',
            'price' => 1200.00,
            'description' => 'Decadent 3-layer Belgian dark chocolate cake iced with creamy chocolate ganache.',
            'status' => 'In Stock',
            'stock' => 8,
            'limit_val' => 10,
            'image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&auto=format&fit=crop&q=80'
        ],
        [
            'name' => 'Gourmet Macarons',
            'category' => 'Pastries',
            'price' => 650.00,
            'description' => 'Assorted box of 12 french almond shells filled with custom buttercreams and curds.',
            'status' => 'Out of Stock',
            'stock' => 0,
            'limit_val' => 50,
            'image_url' => 'https://images.unsplash.com/photo-1569864358642-9d1684040f43?w=400&auto=format&fit=crop&q=80'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, description, status, stock, limit_val, image_url) VALUES (:name, :category, :price, :description, :status, :stock, :limit_val, :image_url)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }
    echo "Products seeded.\n";

    // 3. Table: orders
    echo "Creating 'orders' table...\n";
    $pdo->exec("DROP TABLE IF EXISTS orders");
    $pdo->exec("CREATE TABLE orders (
        id VARCHAR(50) PRIMARY KEY,
        customer VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        priority VARCHAR(50) NOT NULL,
        type VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        time_ago VARCHAR(50) NOT NULL,
        total DECIMAL(10, 2) NOT NULL,
        items_json TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed orders
    $orders = [
        [
            'id' => 'ORD-4920',
            'customer' => 'Henri Matisse',
            'email' => 'henri@fauvism-art.fr',
            'priority' => 'ASAP',
            'type' => 'Delivery',
            'status' => 'pending',
            'time_ago' => '15m ago',
            'total' => 1200.00,
            'items_json' => json_encode([['name' => 'Chocolate Gateau (8")', 'qty' => 1, 'price' => 1200.00]])
        ],
        [
            'id' => 'ORD-4921',
            'customer' => 'Charlotte Corday',
            'email' => 'charlotte@normandy-mail.fr',
            'priority' => 'Oven A',
            'type' => 'Dine-in',
            'status' => 'baking',
            'time_ago' => '5m ago',
            'total' => 1090.00,
            'items_json' => json_encode([
                ['name' => 'Artisan Sourdough', 'qty' => 2, 'price' => 320.00],
                ['name' => 'Butter Croissant', 'qty' => 3, 'price' => 150.00]
            ])
        ],
        [
            'id' => 'ORD-4922',
            'customer' => 'Albert Camus',
            'email' => 'albert@existential.com',
            'priority' => 'Standard',
            'type' => 'Takeaway',
            'status' => 'pending',
            'time_ago' => '22m ago',
            'total' => 860.00,
            'items_json' => json_encode([
                ['name' => 'Lemon Tart', 'qty' => 4, 'price' => 180.00],
                ['name' => 'Espresso', 'qty' => 1, 'price' => 140.00]
            ])
        ],
        [
            'id' => 'ORD-4919',
            'customer' => 'Simone de Beauvoir',
            'email' => 'simone@existential.com',
            'priority' => 'Driver Dave',
            'type' => 'Delivery',
            'status' => 'dispatched',
            'time_ago' => '1h ago',
            'total' => 650.00,
            'items_json' => json_encode([['name' => 'Mixed Macarons', 'qty' => 1, 'price' => 650.00]])
        ],
        [
            'id' => 'ORD-4918',
            'customer' => 'Jean-Paul Sartre',
            'email' => 'jeanpaul@existential.com',
            'priority' => 'Standard',
            'type' => 'Delivery',
            'status' => 'delivered',
            'time_ago' => '2h ago',
            'total' => 430.00,
            'items_json' => json_encode([
                ['name' => 'Baguette', 'qty' => 2, 'price' => 90.00],
                ['name' => 'Salted Caramel Spread', 'qty' => 1, 'price' => 250.00]
            ])
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO orders (id, customer, email, priority, type, status, time_ago, total, items_json) VALUES (:id, :customer, :email, :priority, :type, :status, :time_ago, :total, :items_json)");
    foreach ($orders as $o) {
        $stmt->execute($o);
    }
    echo "Orders seeded.\n";

    // 4. Table: inventory
    echo "Creating 'inventory' table...\n";
    $pdo->exec("DROP TABLE IF EXISTS inventory");
    $pdo->exec("CREATE TABLE inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        stock DECIMAL(10, 2) NOT NULL,
        max_stock DECIMAL(10, 2) NOT NULL,
        limit_threshold DECIMAL(10, 2) NOT NULL,
        unit_cost DECIMAL(10, 2) NOT NULL,
        supplier VARCHAR(100) NOT NULL
    )");

    // Seed inventory
    $inventory = [
        [
            'name' => 'President French Butter',
            'category' => 'Dairy & Fats',
            'stock' => 8.00,
            'max_stock' => 100.00,
            'limit_threshold' => 30.00,
            'unit_cost' => 750.00,
            'supplier' => 'Normandy Import Co.'
        ],
        [
            'name' => 'T55 French Flour',
            'category' => 'Dry Goods',
            'stock' => 45.00,
            'max_stock' => 200.00,
            'limit_threshold' => 50.00,
            'unit_cost' => 180.00,
            'supplier' => 'Euro Flour Mills'
        ],
        [
            'name' => 'Active Dry Yeast',
            'category' => 'Baking Supplies',
            'stock' => 12.00,
            'max_stock' => 20.00,
            'limit_threshold' => 5.00,
            'unit_cost' => 1200.00,
            'supplier' => 'Saf-Levure Dist.'
        ],
        [
            'name' => 'Organic Cane Sugar',
            'category' => 'Dry Goods',
            'stock' => 95.00,
            'max_stock' => 150.00,
            'limit_threshold' => 20.00,
            'unit_cost' => 120.00,
            'supplier' => 'Sweet Harvest Co.'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO inventory (name, category, stock, max_stock, limit_threshold, unit_cost, supplier) VALUES (:name, :category, :stock, :max_stock, :limit_threshold, :unit_cost, :supplier)");
    foreach ($inventory as $i) {
        $stmt->execute($i);
    }
    echo "Inventory seeded.\n";

    echo "DATABASE INITIALIZATION COMPLETE!\n";

} catch (PDOException $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
