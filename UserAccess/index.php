<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'UserSystem.php';

$model = new UserModel();
$view = new UserView();

// Get the current page from URL parameter
$page = $_GET['page'] ?? '';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);

// Handle page routing
switch ($page) {
    case 'dashboard':
        if (!$isLoggedIn) {
            header('Location: index.php');
            exit;
        }
        $view->showDashboard($_SESSION['user']);
        break;

    case 'forgot-password':
        if ($isLoggedIn) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $view->showForgotPasswordForm();
        break;

    case 'reset-password':
        if ($isLoggedIn) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: index.php?page=forgot-password');
            exit;
        }
        $view->showResetPasswordForm($token);
        break;

    default:
        if ($isLoggedIn) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $view->showLoginForm();
        break;
} 