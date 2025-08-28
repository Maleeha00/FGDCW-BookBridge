<?php
// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// Remove query string for routing decisions
$cleanPath = strtok($path, '?');

// Route to homepage.php for root access
if ($cleanPath === '/' || $cleanPath === '') {
    require 'homepage.php';
    return;
}

// Route to index.php for login page
if ($cleanPath === '/index.php' || $cleanPath === '/login') {
    require 'index.php';
    return;
}

// Route to other specific pages
if ($cleanPath === '/register.php') {
    require 'register.php';
    return;
}

if ($cleanPath === '/about.php') {
    require 'about.php';
    return;
}

if ($cleanPath === '/gallery.php') {
    require 'gallery.php';
    return;
}

if ($cleanPath === '/forgot_password.php') {
    require 'forgot_password.php';
    return;
}

if ($cleanPath === '/reset_password.php') {
    require 'reset_password.php';
    return;
}

if ($cleanPath === '/recover_account.php') {
    require 'recover_account.php';
    return;
}

if ($cleanPath === '/logout.php') {
    require 'logout.php';
    return;
}

if ($cleanPath === '/generate_id.php') {
    require 'generate_id.php';
    return;
}

// Check if the requested file exists in the file system
$filePath = __DIR__ . $cleanPath;
if (is_file($filePath)) {
    return false; // Let the built-in server handle static files
}

// For any other route, show 404 or redirect to intro
http_response_code(404);
echo "Page not found";
?>
