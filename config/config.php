<?php
session_start();

// Define Base URL
define('BASE_URL', 'http://localhost/carhire/');

// App settings
define('APP_NAME', 'Smart Drive Car Rental');
define('CURRENCY', 'KES');

// Path constants
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/carhire/');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');

// Role definitions
define('ROLE_ADMIN', 'admin');
define('ROLE_CUSTOMER', 'customer');
define('ROLE_DRIVER', 'driver');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Helper function for checking if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Role check
function hasRole($role) {
    return (isset($_SESSION['role']) && $_SESSION['role'] === $role);
}

// Sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
