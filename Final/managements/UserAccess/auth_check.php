<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function checkAuth() {
    // If user is not logged in (no session user data)
    if (!isset($_SESSION['user'])) {
        // Store current page URL to redirect back after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: index.php');
        exit;
    }
    return true;
}

// Function to check if user has admin privileges
function checkAdmin() {
    // If user is not logged in or is not an admin (admin != 1)
    if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
        // Redirect to dashboard in root directory
        header('Location: /dashboard.php');
        exit;
    }
    return true;
}

// Function to check if user is trying to access user management
function checkUserManagementAccess() {
    // If user is logged in but not an admin
    if (isset($_SESSION['user']) && $_SESSION['user']['admin'] != 1) {
        // Redirect non-admin users to dashboard in root directory
        header('Location: /dashboard.php');
        exit;
    }
    return true;
} 