<?php
// Main Router for Vercel Serverless PHP Environment
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// Fallback to index
if ($path === '') {
    $path = 'index';
}

// Remove .php or .html extensions
$path = preg_replace('/(\.php|\.html)$/', '', $path);

// Check if it's an API endpoint inside the api/ folder (excluding index)
if (strpos($path, 'api/') === 0 && $path !== 'api/index') {
    $apiFile = dirname(__DIR__) . '/' . $path . '.php';
    if (file_exists($apiFile)) {
        chdir(dirname(__DIR__));
        include $apiFile;
        exit;
    }
}

// Map of standard page views in the root directory
$allowed_files = [
    'index' => '../index.php',
    'login' => '../login.php',
    'logout' => '../logout.php',
    'orders' => '../orders.php',
    'products' => '../products.php',
    'analytics' => '../analytics.php',
    'ovens' => '../ovens.php',
    'inventory' => '../inventory.php',
    'settings' => '../settings.php',
];

if (isset($allowed_files[$path])) {
    chdir(dirname(__DIR__));
    include $allowed_files[$path];
} else {
    // If route doesn't match any allowed files, fallback to index
    chdir(dirname(__DIR__));
    include '../index.php';
}
