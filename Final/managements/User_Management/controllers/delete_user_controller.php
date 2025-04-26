<?php
	session_start();
	require_once "../models/user_model.php";

	header('Content-Type: application/json');

	// Get the username from POST data
	$username = $_POST['username'] ?? '';
	
	// Validate username
	if (empty($username)) {
		echo json_encode([
			'success' => false,
			'message' => 'Username is required'
		]);
		exit;
	}

	try {
		// Create userManagement instance
		$userMang = new userManagement();
		
		// Set the input data
		$userMang->sInput = [
			'username' => $username,
			'action' => 'delete'
		];
		
		// Attempt to delete the user
		$result = $userMang->Delete();
		
		if ($result) {
			echo json_encode([
				'success' => true,
				'message' => 'User deleted successfully'
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => 'Failed to delete user'
			]);
		}
		
	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]);
	}
?>