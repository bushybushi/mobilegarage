<?php

// Include the input sanitization file
require_once '../sanitize_inputs.php';

// Include the function to flatten
require_once '../flatten.php';

// Get the PDO instance from the included file
$pdo = require '../db_connection.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;

try {
		$old_address = [];
		$old_phone = [];
		$old_email = [];
		$old_customer = [];
		
		// Gathers all old date
		$customerSql = 'SELECT * from customers where CustomerID = ?';
		$customerStmt = $pdo->prepare($customerSql);
		$customerStmt->execute([$id]);
		
		$old_customer = $customerStmt->fetch();


		$addressSql = 'select Address from Addresses where CustomerID = ?';
		$addressStmt = $pdo->prepare($addressSql);
		$addressStmt->execute([$id]);
		
		$old_address = $addressStmt->fetchAll();

		$phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
		$phoneStmt = $pdo->prepare($phoneSql);
		$phoneStmt->execute([$id]);

		$old_phone = $phoneStmt->fetchAll();

		$emailSql = 'SELECT Emails from Emails where CustomerID = ?';
		$emailStmt = $pdo->prepare($emailSql);
		$emailStmt->execute([$id]);

		$old_email = $emailStmt->fetchAll();
		
        // Call the sanitizeInputs function to sanitize and validate inputs
        $sanitizedInputs = sanitizeInputs($_POST);
        // Extract sanitized inputs for easier use
        $firstName = $sanitizedInputs['firstName'];
		$surname = $sanitizedInputs['surname'];
		$companyName = $sanitizedInputs['companyName'];
		$address = $sanitizedInputs['address'];
		$phoneNumber = $sanitizedInputs['phoneNumber'];
		$emailAddress = $sanitizedInputs['emailAddress'];
		
        // Start a transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
			
			// Checks if old customer data matches new one
			if ($old_customer['FirstName'] != $firstName ||
				$old_customer['LastName'] != $surname ||
				$old_customer['Company'] != $companyName){
					
			//If data do not match, update in database
            $customerSql = "UPDATE customers 
							SET firstName = ':firstName', LastName = ':surname', Company = ':companyName'
                            where CustomerID = :id";
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
            $customerStmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            $customerStmt->bindParam(':companyName', $companyName, PDO::PARAM_STR);
			$customerStmt->bindParam(':id', $id, PDO::PARAM_STR);
            $customerStmt->execute();
			
			}
			
			// Flatten to only be a 1D array for array_diff
			$address = flattenArray($address);
			$old_address = flattenArray($old_address);
			$phone_number = flattenArray($phoneNumber);
			$old_phone = flattenArray($old_phone);
			$emailAddress = flattenArray($emailAddress);
			$old_email = flattenArray($old_email);
			
			// Compare and check addresses that need to be added and deleted
            $addressToAdd = array_diff($address, $old_address); // New addresses to insert
			$addressToDelete = array_diff($old_address, $address); // Outdated addresses to delete
			
			
			// Insert new Addresses
			if (!empty($addressToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO Addresses (customerID, Address) VALUES (?, ?)");
				foreach ($addressToAdd as $row) {
					$insertStmt->execute([$id, $row]);
				}
			}
			
			// Delete outdated Addresses
			if (!empty($addressToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM Addresses WHERE customerID = ? AND Address IN (" . str_repeat('?,', count($addressToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$id], $addressToDelete));
			}
			
            // Compare and check phones that need to be added and deleted
            $phoneToAdd = array_diff($phoneNumber, $old_phone); // New phones to insert
			$phoneToDelete = array_diff($old_phone, $phoneNumber); // Outdated phones to delete
	
			// Insert new phones
			if (!empty($phoneToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO phoneNumbers (customerID, Nr) VALUES (?, ?)");
				foreach ($phoneToAdd as $row) {
					$insertStmt->execute([$id, $row]);
				}
			}

			// Delete outdated emails
			if (!empty($phoneToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM emailNumbers WHERE customerID = ? AND Nr IN (" . str_repeat('?,', count($emailToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$id], $emailToDelete));
			}

            // Compare and check emails that need to be added and deleted
            $emailToAdd = array_diff($emailAddress, $old_email); // New emailes to insert
			$emailToDelete = array_diff($old_email, $emailAddress); // Outdated emailes to delete
	
			// Insert new emails
			if (!empty($emailToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO Emails (customerID, Emails) VALUES (?, ?)");
				foreach ($emailToAdd as $row) {
					$insertStmt->execute([$id, $row]);
				}
			}

			// Delete outdated emails
			if (!empty($emailToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM Emails WHERE customerID = ? AND Emails IN (" . str_repeat('?,', count($emailToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$id], $emailToDelete));
			}
			
			// Delete empty entries in Addresses
			$deleteEmptyStmt = $pdo->prepare("DELETE FROM Addresses WHERE Address IS NULL OR TRIM(Address) = ''");
			$deleteEmptyStmt->execute();
			$deleteEmptyStmt = $pdo->prepare("DELETE FROM phoneNumbers WHERE Nr IS NULL OR TRIM(Nr) = ''");
			$deleteEmptyStmt->execute();
			$deleteEmptyStmt = $pdo->prepare("DELETE FROM Emails WHERE Emails IS NULL OR TRIM(Emails) = ''");
			$deleteEmptyStmt->execute();
			
			
            // Commit the transaction
            $pdo->commit();

            // Display success message
            echo "<h1>Customer Updated Successfully!</h1>";
            echo "<p><a href='/'>Go Back</a></p>";
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $pdo->rollBack();
            throw $e;
        }
} catch (PDOException $e) {
    // Handle database errors
    echo "<h1>Error: Unable to Update Customer</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
