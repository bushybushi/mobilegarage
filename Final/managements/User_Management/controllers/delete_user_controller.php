<?php
	session_start();
	require_once "../models/user_model.php";

	header('Content-Type: application/json');

	try {
		// Get the username from POST data
		$username = $_POST['username'] ?? '';
		
		if (empty($username)) {
			throw new Exception('Username is required');
		}
		
		// Create userManagement instance with the username
		$userMang = new userManagement();
		$userMang->sInput = [
			'username' => $username,
			'action' => 'delete'
		];
		
		// Delete the user
		$result = $userMang->Delete();
		
		if ($result) {
			echo json_encode([
				'success' => true,
				'message' => 'User deleted successfully',
				'redirect' => 'user_main.php'
			]);
		} else {
			throw new Exception('Failed to delete user');
		}
	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]);
	}
?>