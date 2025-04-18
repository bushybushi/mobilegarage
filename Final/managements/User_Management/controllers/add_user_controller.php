<?php
	session_start();
	require_once "../models/user_model.php";

	header('Content-Type: application/json');

	try {
		// Create userManagement instance
		$userMang = new userManagement();
		
		// Add the user
		$result = $userMang->Add();
		
		if ($result) {
			echo json_encode([
				'success' => true,
				'message' => 'User added successfully',
				'redirect' => 'user_main.php'
			]);
		} else {
			throw new Exception('Failed to add user');
		}
	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]);
	}
?>