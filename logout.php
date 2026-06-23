<?php
require_once 'config.php';

if (isset($_COOKIE['bakery_session_token'])) {
    $token = $_COOKIE['bakery_session_token'];
    
    // Clear token in database
    $stmt = $pdo->prepare("UPDATE users SET session_token = NULL WHERE session_token = ?");
    $stmt->execute([$token]);
    
    // Expire the cookie
    setcookie('bakery_session_token', '', time() - 3600, '/');
}

header("Location: login.php");
exit;
?>
