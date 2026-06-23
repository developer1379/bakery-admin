<?php
session_start();
if (!isset($_SESSION['bakery_logged_in']) || $_SESSION['bakery_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['customer']) || !isset($data['items']) || !isset($data['priority']) || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $orderId = 'ORD-' . rand(1000, 9999);
    $email = strtolower(str_replace(' ', '', $data['customer'])) . '@bakery-cust.com';
    $status = 'pending';
    $time_ago = 'Just now';
    
    // Calculate total
    $total = 0;
    foreach ($data['items'] as $item) {
        $total += $item['price'] * $item['qty'];
    }

    $stmt = $pdo->prepare("INSERT INTO orders (id, customer, email, priority, type, status, time_ago, total, items_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $orderId,
        $data['customer'],
        $email,
        $data['priority'],
        $data['type'],
        $status,
        $time_ago,
        $total,
        json_encode($data['items'])
    ]);

    echo json_encode([
        'success' => true,
        'order' => [
            'id' => $orderId,
            'customer' => $data['customer'],
            'email' => $email,
            'priority' => $data['priority'],
            'type' => $data['type'],
            'status' => $status,
            'time' => $time_ago,
            'total' => $total,
            'items' => $data['items']
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
