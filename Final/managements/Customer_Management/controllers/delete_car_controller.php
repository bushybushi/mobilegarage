<?php
	session_start();
	require_once "../models/car_model.php";

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$licenseNr = isset($_POST['licenseNr']) ? $_POST['licenseNr'] : null;
		$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : null;
		$deleteJobCards = isset($_POST['deleteJobCards']) ? $_POST['deleteJobCards'] === '1' : false;

		// Set content type to JSON
		header('Content-Type: application/json');

		if (!$licenseNr) {
			echo json_encode([
				'success' => false,
				'message' => "License number is required"
			]);
			exit;
		}

		try {
			// Create car object
			$car = new car($licenseNr);
			
			// Delete the car with or without job cards based on the parameter
			if ($car->Delete($deleteJobCards)) {
				$message = "Car deleted successfully!" . ($deleteJobCards ? " Associated job cards were also deleted." : "");
				echo json_encode([
					'success' => true,
					'message' => $message
				]);
			} else {
				echo json_encode([
					'success' => false,
					'message' => "Failed to delete car"
				]);
			}
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => $e->getMessage()
			]);
		}
		exit;
	} else {
		// Set content type to JSON
		header('Content-Type: application/json');
		echo json_encode([
			'success' => false,
			'message' => "Invalid request method"
		]);
		exit;
	}
?>