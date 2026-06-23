<?php
$authenticated = false;

if (isset($_COOKIE['bakery_session_token']) && !empty($_COOKIE['bakery_session_token'])) {
    $authenticated = true;
    $GLOBALS['currentUser'] = ['username' => 'admin', 'name' => 'Grand Baker'];
}

if (!$authenticated) {
    setcookie('bakery_session_token', '', time() - 3600, '/');
    header("Location: login.php");
    exit;
}
?>
