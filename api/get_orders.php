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

$orders = [
    [ "id" => "ORD-4920", "customer" => "Henri Matisse", "email" => "henri@fauvism-art.fr", "items" => [[ "name" => 'Chocolate Gateau (8")', "qty" => 1, "price" => 1200.00 ]], "priority" => "ASAP", "type" => "Delivery", "status" => "pending", "time" => "15m ago", "total" => 1200.00 ],
    [ "id" => "ORD-4921", "customer" => "Charlotte Corday", "email" => "charlotte@normandy-mail.fr", "items" => [[ "name" => 'Artisan Sourdough', "qty" => 2, "price" => 320.00 ], [ "name" => 'Butter Croissant', "qty" => 3, "price" => 150.00 ]], "priority" => "Oven A", "type" => "Dine-in", "status" => "baking", "time" => "5m ago", "total" => 1090.00 ],
    [ "id" => "ORD-4922", "customer" => "Albert Camus", "email" => "albert@existential.com", "items" => [[ "name" => 'Lemon Tart', "qty" => 4, "price" => 180.00 ], [ "name" => 'Espresso', "qty" => 1, "price" => 140.00 ]], "priority" => "Standard", "type" => "Takeaway", "status" => "pending", "time" => "22m ago", "total" => 860.00 ],
    [ "id" => "ORD-4919", "customer" => "Simone de Beauvoir", "email" => "simone@existential.com", "items" => [[ "name" => 'Mixed Macarons', "qty" => 1, "price" => 650.00 ]], "priority" => "Driver Dave", "type" => "Delivery", "status" => "dispatched", "time" => "1h ago", "total" => 650.00 ],
    [ "id" => "ORD-4918", "customer" => "Jean-Paul Sartre", "email" => "jeanpaul@existential.com", "items" => [[ "name" => 'Baguette', "qty" => 2, "price" => 90.00 ], [ "name" => 'Salted Caramel Spread', "qty" => 1, "price" => 250.00 ]], "priority" => "Standard", "type" => "Delivery", "status" => "delivered", "time" => "2h ago", "total" => 430.00 ]
];

header('Content-Type: application/json');
echo json_encode($orders);
?>
