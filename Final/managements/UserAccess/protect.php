<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to protect pages that require authentication
function protectPage() {
    // Check if user is not logged in
    if (!isset($_SESSION['user'])) {
        // Store current URL for post-login redirect
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ../../index.php');
        exit;
    }
    return true;
}

// Function to protect admin-only pages
function protectAdminPage() {
    // Check if user is not logged in or not an admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
        // Redirect non-admin users to dashboard in root directory
        header('Location: ../../dashboard.php');
        exit;
    }
    return true;
}

// Note: Automatic protectPage() call removed to prevent redirect loops
// protectPage(); // This line is causing the redirect loop 