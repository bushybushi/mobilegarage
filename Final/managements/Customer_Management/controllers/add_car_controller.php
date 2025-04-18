<?php
	session_start();
	require_once "../models/car_model.php";

	try {
		// Set content type to JSON
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST');
		header('Access-Control-Allow-Headers: Content-Type');

		// Validate customer ID exists
		if (!isset($_POST['customerId']) || empty($_POST['customerId'])) {
			throw new Exception('Customer ID is required');
		}

		// Check if customer exists
		global $pdo;
		$checkCustomerSql = "SELECT CustomerID FROM customers WHERE CustomerID = ?";
		$checkCustomerStmt = $pdo->prepare($checkCustomerSql);
		$checkCustomerStmt->execute([$_POST['customerId']]);
		
		if (!$checkCustomerStmt->fetch()) {
			throw new Exception('Invalid customer ID. Customer does not exist.');
		}

		// Create car management instance
		$carMang = new carManagement();
		
		// Add the car
		$result = $carMang->Add();
		
		// If car was added successfully, create the association
		if ($result) {
			// Create car-customer association
			$assocSql = "INSERT INTO carassoc (CustomerID, LicenseNr) VALUES (?, ?)";
			$assocStmt = $pdo->prepare($assocSql);
			$assocStmt->execute([$_POST['customerId'], $carMang->car->getLicenseNr()]);
		}
		
		// Always return a JSON response
		$response = [
			'success' => true,
			'message' => 'Car added successfully',
			'licenseNr' => $carMang->car->getLicenseNr()
		];
		
		// Log the response for debugging
		error_log("Car addition response: " . json_encode($response));
		
		echo json_encode($response);
		exit;
		
	} catch (PDOException $e) {
		// Log the error
		error_log("Car addition database error: " . $e->getMessage());
		
		http_response_code(500);
		$errorResponse = [
			'success' => false,
			'message' => 'Database error occurred. Please try again.'
		];
		
		echo json_encode($errorResponse);
		exit;
	} catch (Exception $e) {
		// Log the error
		error_log("Car addition error: " . $e->getMessage());
		
		http_response_code(500);
		$errorResponse = [
			'success' => false,
			'message' => $e->getMessage()
		];
		
		echo json_encode($errorResponse);
		exit;
	}
?>