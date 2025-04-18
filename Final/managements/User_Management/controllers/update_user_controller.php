<?php
// Start session for user authentication
session_start();

// Include user model for database operations
require_once "../models/user_model.php";

try {
	// Create new user management instance
	$userMang = new userManagement();
	
	// Set input data from POST request
	$userMang->sInput = $_POST;
	
	// Get username from POST data for redirection
	$username = $_POST['username'];
	
	// Attempt to update user
	$result = $userMang->Update();
	
	if ($result) {
		// Success response with dynamic redirect
		$response = [
			'success' => true,
			'message' => 'User updated successfully',
			'redirect' => 'user_view.php?id=' . urlencode($username)
		];
	} else {
		// Error response
		$response = [
			'success' => false,
			'message' => 'Failed to update user'
		];
	}
} catch (Exception $e) {
	// Error response with exception message
	$response = [
		'success' => false,
		'message' => 'Error updating user: ' . $e->getMessage()
	];
}

// Set response type to JSON
header('Content-Type: application/json');

// Send JSON response
echo json_encode($response);
?>