<?php
require_once 'config.php';

$authenticated = false;

if (isset($_COOKIE['bakery_session_token']) && !empty($_COOKIE['bakery_session_token'])) {
    $token = $_COOKIE['bakery_session_token'];
    
    // Query database for the user with this session token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE session_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $authenticated = true;
        $GLOBALS['currentUser'] = $user;
    }
}

if (!$authenticated) {
    // If not authenticated, clear the cookie and redirect to login
    setcookie('bakery_session_token', '', time() - 3600, '/');
    header("Location: login.php");
    exit;
}
?>
