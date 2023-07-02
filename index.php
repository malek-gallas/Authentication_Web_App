<?php
require_once 'app/controllers/Users.php';

// Get the current URL from the browser
$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Parse the URL and get the path
$uri = parse_url($url, PHP_URL_PATH);

// Route the URI to the appropriate PHP file
switch ($uri) {
    case '/':
        require_once 'app/views/home.php';
        break;
    case '/register':
        require_once 'app/views/register.php';
        break;
    case '/validate':
        require_once 'app/views/validate.php';
        break;
    case '/login':
        require_once 'app/views/login.php';
        break;
    case '/resetPassword':
        require_once 'app/views/resetPassword.php';
        break;  
    case '/newPassword':
        require_once 'app/views/newPassword.php';
        break;
    default:
        require_once 'app/views/error.php';
        break;
}