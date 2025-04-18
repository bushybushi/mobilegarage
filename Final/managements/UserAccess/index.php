<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files and initialize model and view
require_once 'UserSystem.php';
$model = new UserModel();
$view = new UserView();

// Get requested page from URL parameters
$page = $_GET['page'] ?? '';

// Check user authentication status
$isLoggedIn = isset($_SESSION['user']);

// Route requests based on page parameter
switch ($page) {
    case 'forgot-password':
        // Redirect logged-in users to dashboard
        if ($isLoggedIn) {
            header('Location: ../dashboard.php');
            exit;
        }
        // Display password recovery form
        $view->showForgotPasswordForm();
        break;

    case 'reset-password':
        // Redirect logged-in users to dashboard
        if ($isLoggedIn) {
            header('Location: ../dashboard.php');
            exit;
        }
        // Get reset token from URL
        $token = $_GET['token'] ?? '';
        // Validate token presence
        if (empty($token)) {
            header('Location: index.php?page=forgot-password');
            exit;
        }
        // Display password reset form
        $view->showResetPasswordForm($token);
        break;

    case 'register':
        // Redirect logged-in users to dashboard
        if ($isLoggedIn) {
            header('Location: ../dashboard.php');
            exit;
        }
        // Display registration form
        $view->showRegistrationForm();
        break;

    default:
        // Redirect logged-in users to dashboard
        if ($isLoggedIn) {
            header('Location: ../dashboard.php');
            exit;
        }
        // Show login form for unauthenticated users
        if (!in_array($page, ['forgot-password', 'reset-password', 'register'])) {
            $view->showLoginForm();
        }
        break;
} 
