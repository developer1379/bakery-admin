<?php
if (isset($_COOKIE['bakery_session_token'])) {
    // Expire the cookie
    setcookie('bakery_session_token', '', time() - 3600, '/');
}

header("Location: login.php");
exit;
?>
