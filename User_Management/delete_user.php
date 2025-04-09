<?php
// delete_user.php

// Include the User class
require_once 'user.php';
require_once 'sanitize_inputs.php';

$user = new User();

// Check if username is provided via GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
    $username = htmlspecialchars($_GET['username']);
    $username = sanitizeInputs($username);

    // Call the deleteUser function from user.php
    $result = $user->deleteUser($username);
    
    // Store result message in session to display in UserManagementMain.php
    session_start();
    if ($result) {
        $_SESSION['message'] = 'User deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting user.';
        $_SESSION['message_type'] = 'danger';
    }
    
    // Redirect to UserManagementMain.php
    header("Location: UserManagementMain.php");
    exit;
} else {
    // Redirect if invalid request
    session_start();
    $_SESSION['message'] = 'Invalid request.';
    $_SESSION['message_type'] = 'warning';
    header("Location: UserManagementMain.php");
    exit;
}