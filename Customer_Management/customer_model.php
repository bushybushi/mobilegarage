<?php
	require '../sanitize_inputs.php';
	require '../flatten.php';
	// Get the PDO instance from the included file
	$pdo = require '../db_connection.php';
	
	class customer {
		public id;
		public $fName; // first name
		public $lName; // last name
		public $company; // company name (if any)
		public array $address;
		public array $phone;
		public array $email;
		
		function __construct($id = null, $fName = null, $lName = null, $company = null, array $address = [], array $phone = [], array $email = []) {	
			editID($id);
			editFName($fName);
			editLName($lName);
			editCompany($company);
			editAddress($address);
			editPhone($phone);
			editEmail($email);
		}
		
		// get functions to return the object's data
		
		function getID() {
			return $this->id;
		}
		
		function getFName() {
			return $this->fName;
		}
		
		function getLName() {
			return $this->lName;
		}
		
		function getCompany() {
			return $this->company;
		}
		
		function getAddress(): array {
			return $this->address;
		}
		
		function getPhone(): array {
			return $this->phone;
		}
		
		function getEmail(): array{
			return $this->email;
		}
		
		// Edit Functions to be used to change the object's data
		function editID($id) {
			$this->id = $id;
		}
		
		function editFName($fName) {
			$this->fName = $fName;
		}
		
		function editID($lName) {
			$this->lName = $lName;
		}
		
		function editCompany($company) {
			$this->company = $company;
		}
		
		function editAddress(array $address) {
			$this->address = $address;
		}
		
		function editPhone(array $phone) {
			$this->phone = $phone;
		}
		
		function editEmail(array $email) {
			$this->email = $email;
		}
	}
	
	// Class for Customer Management
	class customerManagement {
		
		// sanitize the inputs from the post and then insert the data into the object customer
		$sInput = [];
		$sInput = sanitizeInputs($_POST);
		
		$customer = new customer($sInput['id'] ?? null ,$sInput['fName'],$sInput['lName'],$sInput['company'],$sInput['address'] ?? [],$sInput['phone'] ?? [],$sInput['email'] ?? []);
		
		
		// Function to add new Customer in database
		function Add() {
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If there are no errors, proceed with database operations
    if (empty($errors)) {
        try {
            // Ensure at least one phone number is provided
            if (empty($customer->getPhone())) {
                $errors['phoneNumber'] = "At least one phone number is required.";
            }

            if (empty($errors)) {
                // Start a transaction to ensure atomicity
                $pdo->beginTransaction();
                try {
                    // Insert into the `customers` table and get the generated CustomerID
                    $customerSql = "INSERT INTO customers (FirstName, LastName, Company)
                                    VALUES (?, ?, ?)";
                    $customerStmt = $pdo->prepare($customerSql);
                    $customerStmt->bindParam('sss', $customer->getFName(),$customer->getLName(),$customer->getCompany());
                    $customerStmt->execute();
                    
					// Get the last inserted CustomerID into customer object
                    $customer->editID($pdo->lastInsertId());
					
                    if (!$customer->getID()) {
                        throw new Exception("Failed to retrieve CustomerID after insertion.");
                    }

                    // Insert into the `addresses` table
                    foreach ($customer->getAddress() as $address) {
                        if (!empty($address)) {
                            $addressSql = "INSERT INTO addresses (CustomerID, Address)
                                           VALUES (?, ?)";
                            $addressStmt = $pdo->prepare($addressSql);
                            $addressStmt->bindParam('is',$customer->getID(), $address);
                            $addressStmt->execute();
                        }
                    }

                    // Insert into the `phonenumbers` table
                    foreach ($customer->getPhone() as $phoneNumber) {
                        if (!empty($phoneNumber)) {
                            $phoneSql = "INSERT INTO phonenumbers (CustomerID, Nr)
                                         VALUES (?, ?)";
                            $phoneStmt = $pdo->prepare($phoneSql);
                            $phoneStmt->bindParam('is',$customer->getID(), $phoneNumber);
                            $phoneStmt->execute();
                        }
                    }

                    // Insert into the `emails` table
                    foreach ($customer->getEmail() as $emailAddress) {
                        if (!empty($emailAddress)) {
                            $emailSql = "INSERT INTO emails (CustomerID, Emails)
                                         VALUES (?, ?)";
                            $emailStmt = $pdo->prepare($emailSql);
                            $emailStmt->bindParam('is',$customer->getID(), $emailAddress);
                            $emailStmt->execute();
                        }
                    }

                    // Commit the transaction
                    $pdo->commit();
                    // Display success message
                    echo "<h1>New Customer Added Successfully!</h1>";
                    echo "<p><a href='/'>Go Back</a></p>";
                    // Clear inputs after successful submission
                    $sanitizedInputs = [];
                } catch (Exception $e) {
                    // Rollback the transaction in case of an error
                    $pdo->rollBack();
                    echo "<h1>Error: Unable to Add User</h1>";
                    echo "<p>" . $e->getMessage() . "</p>";
                }
            }
        } catch (PDOException $e) {
            // Handle database errors
            echo "<h1>Error: Unable to Add User</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
			}
		}
	}

	}
	
	// Function to update Customer data
	function Update() {
		
	try {
		
		$old_customer = new customer($customer->getID());
		
		// Gathers all old date
		$customerSql = 'SELECT firstName, lastName, Company from customers where CustomerID = ?';
		$customerStmt = $pdo->prepare($customerSql);
		$customerStmt->execute([$old_customer->getID()]);
		
		$old_customer->editFName($customerStmt->fetchColumn());
		$old_customer->editLName($customerStmt->fetchColumn());
		$old_customer->editCompany($customerStmt->fetchColumn());


		$addressSql = 'select Address from Addresses where CustomerID = ?';
		$addressStmt = $pdo->prepare($addressSql);
		$addressStmt->execute([$old_customer->getID()]);
		
		$old_customer->editAddress($addressStmt->fetch_array());

		$phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
		$phoneStmt = $pdo->prepare($phoneSql);
		$phoneStmt->execute([$old_customer->getID()]);

		$old_customer->editPhone($phoneStmt->fetch_array());

		$emailSql = 'SELECT Emails from Emails where CustomerID = ?';
		$emailStmt = $pdo->prepare($emailSql);
		$emailStmt->execute([$old_customer->getID()]);

		$old_customer->editEmail($emailStmt->fetch_array());
		
        // Start a transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
			
			// Checks if old customer data matches new one
			if ($old_customer->getFName() != $customer->getFName() ||
				$old_customer->getLName() != $customer->getLName() ||
				$old_customer->getCompany() != $customer->getCompany()){
					
			//If data do not match, update in database
            $customerSql = "UPDATE customers 
							SET firstName = '?', LastName = '?', Company = '?'
                            where CustomerID = ?";
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->bindParam('sssi', $old_customer->getFName(), $old_customer->getLName(), $old_customer->getCompany(), $old_customer->getID());
            $customerStmt->execute();
			
			}
			
			// Flatten to only be a 1D array for array_diff
			$customer->editAddress(flattenArray($customer->getAddress()));
			$customer->editPhone(flattenArray($customer->getPhone()));
			$customer->editEmail(flattenArray($customer->getEmail()));
			
			
			$old_customer->editAddress(flattenArray($old_customer->getAddress()));
			$old_customer->editPhone(flattenArray($old_customer->getPhone()));
			$old_customer->editEmail(flattenArray($old_customer->getEmail()));
			
			
			// Compare and check addresses that need to be added and deleted
            $addressToAdd = array_diff($customer->getAddress(), $old_customer->getAddress()); // New addresses to insert
			$addressToDelete = array_diff($old_customer->getAddress(), $customer->getAddress()); // Outdated addresses to delete
			
			
			// Insert new Addresses
			if (!empty($addressToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO Addresses (customerID, Address) VALUES (?, ?)");
				foreach ($addressToAdd as $row) {
					$insertStmt->execute([$old_customer->getID(), $row]);
				}
			}
			
			// Delete outdated Addresses
			if (!empty($addressToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM Addresses WHERE customerID = ? AND Address IN (" . str_repeat('?,', count($addressToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$old_customer->getID()], $addressToDelete));
			}
			
            // Compare and check phones that need to be added and deleted
            $phoneToAdd = array_diff($customer->getPhone(), $old_customer->getPhone()); // New phones to insert
			$phoneToDelete = array_diff($old_customer->getPhone(), $customer->getPhone()); // Outdated phones to delete
	
			// Insert new phones
			if (!empty($phoneToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO phoneNumbers (customerID, Nr) VALUES (?, ?)");
				foreach ($phoneToAdd as $row) {
					$insertStmt->execute([$old_customer->getID(), $row]);
				}
			}

			// Delete outdated emails
			if (!empty($phoneToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM emailNumbers WHERE customerID = ? AND Nr IN (" . str_repeat('?,', count($emailToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$old_customer->getID()], $emailToDelete));
			}

            // Compare and check emails that need to be added and deleted
            $emailToAdd = array_diff($customer->getEmail(), $old_customer->getEmail()); // New emailes to insert
			$emailToDelete = array_diff($old_customer->getEmail(), $customer->getEmail()); // Outdated emailes to delete
	
			// Insert new emails
			if (!empty($emailToAdd)) {
				$insertStmt = $pdo->prepare("INSERT INTO Emails (customerID, Emails) VALUES (?, ?)");
				foreach ($emailToAdd as $row) {
					$insertStmt->execute([$old_customer->getID(), $row]);
				}
			}

			// Delete outdated emails
			if (!empty($emailToDelete)) {
				$deleteStmt = $pdo->prepare("DELETE FROM Emails WHERE customerID = ? AND Emails IN (" . str_repeat('?,', count($emailToDelete) - 1) . '?)');
				$deleteStmt->execute(array_merge([$old_customer->getID()], $emailToDelete));
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
		
	}
	
function Delete() {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($customer->getID())) {

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete customer-related records
        $stmt = $pdo->prepare("DELETE FROM emails WHERE CustomerID = ?");
        $stmt->bindParam('i', $customer->getID());
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM phonenumbers WHERE CustomerID = ?");
        $stmt->bindParam('i', $customer->getID());
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM addresses WHERE CustomerID = ?");
        $stmt->bindParam('i', $customer->getID());
        $stmt->execute();

        // Finally, delete from customers table
        $stmt = $pdo->prepare("DELETE FROM customers WHERE CustomerID = ?");
        $stmt->bindParam('i', $customer->getID());
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Return a success response
        echo json_encode(["success" => true, "message" => "Customer deleted successfully."]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error deleting customer: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
	
}
}
?>