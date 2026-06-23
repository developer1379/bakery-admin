<?php
session_start();
if (!isset($_SESSION['bakery_logged_in']) || $_SESSION['bakery_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['name']) || !isset($data['category']) || !isset($data['price']) || !isset($data['stock']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $img = !empty($data['img']) ? $data['img'] : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop&q=80';
    $limit_val = $data['stock'] > 0 ? intval($data['stock'] * 1.5) : 30;
    
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, description, status, stock, limit_val, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['category'],
        $data['price'],
        $data['desc'] ?? '',
        $data['status'],
        $data['stock'],
        $limit_val,
        $img
    ]);

    $productId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'product' => [
            'id' => intval($productId),
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => floatval($data['price']),
            'desc' => $data['desc'] ?? '',
            'status' => $data['status'],
            'stock' => intval($data['stock']),
            'limit' => $limit_val,
            'img' => $img
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
