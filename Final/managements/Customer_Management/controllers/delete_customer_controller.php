<?php
	session_start();
	require_once "../models/customer_model.php";

	header('Content-Type: application/json');

	try {
		// Log the received parameters for debugging
		error_log("Delete customer request - ID: " . $_POST['id'] . ", deleteCars: " . (isset($_POST['deleteCars']) ? $_POST['deleteCars'] : 'not set') . ", deleteJobCards: " . (isset($_POST['deleteJobCards']) ? $_POST['deleteJobCards'] : 'not set'));
		
		$customerMang = new customerManagement(true); // true to require POST method
		
		// Get the deleteJobCards parameter
		$deleteJobCards = isset($_POST['deleteJobCards']) ? $_POST['deleteJobCards'] === 'true' : false;
		
		// Set the deleteJobCards flag in the customer management object
		$customerMang->setDeleteJobCards($deleteJobCards);
		
		$result = $customerMang->Delete();
		
		if ($result) {
			// Prepare success message
			$message = "Customer deleted successfully!";
			if ($deleteJobCards) {
				$message .= " Associated cars and job cards were also deleted.";
			} elseif (isset($_POST['deleteCars']) && $_POST['deleteCars'] === 'true') {
				$message .= " Associated cars were also deleted.";
			}
			
			// Return success response
			echo json_encode([
				'success' => true,
				'message' => $message,
				'redirect' => '../views/customer_main.php'
			]);
		} else {
			throw new Exception("Failed to delete customer");
		}
	} catch (Exception $e) {
		error_log("Error deleting customer: " . $e->getMessage());
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => "Error: " . $e->getMessage()
		]);
	}
?>