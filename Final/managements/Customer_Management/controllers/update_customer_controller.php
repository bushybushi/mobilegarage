<?php
	session_start();
	require_once "../models/customer_model.php";

	header('Content-Type: application/json');

	try {
		$customerMang = new customerManagement(true); // Require POST method
		$customerMang->Update();
		
		// Return success response
		echo json_encode([
			'status' => 'success',
			'message' => 'Customer updated successfully!',
			'customerId' => $customerMang->customer->getID()
		]);
	} catch (Exception $e) {
		// If there was an error, return error response
		http_response_code(400);
		echo json_encode([
			'status' => 'error',
			'message' => $e->getMessage()
		]);
	}
?>