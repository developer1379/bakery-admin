<?php
$host = '193.203.184.228';
$user = 'u793412290_bakery';
$pass = 'Bakery@1020';
$db   = 'u793412290_bakery';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "SUCCESS: Connected to database successfully!\n";
} catch (PDOException $e) {
    echo "ERROR: Could not connect to database: " . $e->getMessage() . "\n";
}
