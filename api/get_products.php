<?php
$authenticated = false;
if (isset($_COOKIE['bakery_session_token']) && !empty($_COOKIE['bakery_session_token'])) {
    $authenticated = true;
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$products = [
    [ "id" => 1, "name" => "Butter Croissant", "category" => "Pastries", "price" => 150.00, "desc" => "Crispy, flaky french style layered puff pastry made with 100% Normandy butter.", "status" => "In Stock", "stock" => 80, "limit" => 120, "img" => "https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&auto=format&fit=crop&q=80" ],
    [ "id" => 2, "name" => "Artisan Sourdough", "category" => "Bread", "price" => 320.00, "desc" => "Wild yeast slow fermented boule with a robust blistered crust and open airy crumb.", "status" => "Low Stock", "stock" => 5, "limit" => 30, "img" => "https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=400&auto=format&fit=crop&q=80" ],
    [ "id" => 3, "name" => "Chocolate Truffle Gateau", "category" => "Cakes", "price" => 1200.00, "desc" => "Decadent 3-layer Belgian dark chocolate cake iced with creamy chocolate ganache.", "status" => "In Stock", "stock" => 8, "limit" => 10, "img" => "https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&auto=format&fit=crop&q=80" ],
    [ "id" => 4, "name" => "Gourmet Macarons", "category" => "Pastries", "price" => 650.00, "desc" => "Assorted box of 12 french almond shells filled with custom buttercreams and curds.", "status" => "Out of Stock", "stock" => 0, "limit" => 50, "img" => "https://images.unsplash.com/photo-1569864358642-9d1684040f43?w=400&auto=format&fit=crop&q=80" ]
];

header('Content-Type: application/json');
echo json_encode($products);
?>
