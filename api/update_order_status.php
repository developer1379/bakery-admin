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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $priority = null;
    if ($data['status'] === 'baking') {
        $priority = 'Oven A';
    } elseif ($data['status'] === 'delivered') {
        $priority = 'Paid';
    }

    if ($priority !== null) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, priority = ? WHERE id = ?");
        $stmt->execute([$data['status'], $priority, $data['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
