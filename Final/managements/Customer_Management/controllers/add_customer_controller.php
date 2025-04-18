<?php
	session_start();
	require_once "../models/customer_model.php";

	header('Content-Type: application/json');

	try {
		$customerMang = new customerManagement();
		$customerId = $customerMang->Add();
		
		if ($customerId) {
			echo json_encode([
				'status' => 'success',
				'message' => 'Customer added successfully',
				'customerId' => $customerId,
				'redirect' => '../views/customer_main.php'
			]);
		} else {
			throw new Exception("Failed to add customer");
		}
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode([
			'status' => 'error',
			'message' => $e->getMessage()
		]);
	}
?>