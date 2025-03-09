<?php
// AddUser.php
require 'User.php'; // Ensure correct path to User.php
require '../includes/sanitize_inputs.php'; // Adjust path if needed

// Get PDO instance
try {
    $pdo = require '../includes/db_connection.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Initialize UserManagement with PDO
$userManagement = new UserManagement($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $sanitized = sanitizeInputs($_POST);

    // Validate required fields
    if (empty($sanitized['username']) || empty($sanitized['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Username and email are required']);
        exit;
    }

    $action = $sanitized['action'] ?? 'add';

    try {
        if ($action === 'add') {
            $result = $userManagement->addUser($sanitized);
        } elseif ($action === 'edit') {
            $currentUsername = $sanitized['current_username'] ?? null;
            if (!$currentUsername) {
                throw new Exception('Current username is required for edit');
            }
            $result = $userManagement->editUser($sanitized, $currentUsername);
        } else {
            $result = ['status' => 'error', 'message' => 'Invalid action'];
        }
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>