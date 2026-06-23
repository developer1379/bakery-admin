<?php
require_once '../config.php';

$authenticated = false;
if (isset($_COOKIE['bakery_session_token']) && !empty($_COOKIE['bakery_session_token'])) {
    $token = $_COOKIE['bakery_session_token'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE session_token = ?");
    $stmt->execute([$token]);
    if ($stmt->fetch()) {
        $authenticated = true;
    }
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $pStmt = $pdo->query("SELECT id, name, category, price, description as `desc`, status, stock, limit_val as `limit`, image_url as img FROM products ORDER BY id DESC");
    $products = $pStmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
