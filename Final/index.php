<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Only redirect if we're not already on the login page
    if (!strpos($_SERVER['REQUEST_URI'], 'UserAccess/index.php')) {
        header('Location: managements/UserAccess/index.php');
        exit;
    }
}

// If logged in and no specific page is requested, redirect to dashboard
if (!isset($_GET['page'])) {
    header('Location: managements/dashboard.php');
    exit;
}

// If we get here, we're logged in and have a specific page requested
// Let the UserAccess/index.php handle the routing
require_once 'managements/UserAccess/index.php'; 