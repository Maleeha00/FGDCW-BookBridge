<?php

$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];
$cleanPath = strtok($path, '?');
if ($cleanPath === '/' || $cleanPath === '') {
    require 'homepage.php';
    return;
}
if ($cleanPath === '/index.php' || $cleanPath === '/login') {
    require 'index.php';
    return;
}
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

if ($cleanPath === '/verify_email.php') {
    require 'verify_email.php';
    return;
}

$filePath = __DIR__ . $cleanPath;
if (is_file($filePath)) {
    return false; 
}
http_response_code(404);
echo "Page not found";
?>
