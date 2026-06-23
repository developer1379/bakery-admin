<?php
require_once dirname(__DIR__) . '/config.php';

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
    $oStmt = $pdo->query("SELECT id, customer, email, priority, type, status, time_ago as time, total, items_json FROM orders ORDER BY created_at DESC");
    $dbOrders = $oStmt->fetchAll();
    $orders = [];
    foreach ($dbOrders as $o) {
        $o['items'] = json_decode($o['items_json'], true);
        unset($o['items_json']);
        $orders[] = $o;
    }
    header('Content-Type: application/json');
    echo json_encode($orders);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
