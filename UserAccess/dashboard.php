<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once 'UserSystem.php';
$view = new UserView();
$view->showDashboard($_SESSION['user']); 