<?php
// Include the input sanitization file
require 'sanitize_inputs.php';
// Get the PDO instance from the included file
$pdo = require 'db_connection.php';
// Initialize an array to store error messages
$errors = [];
// Initialize an array to store sanitized inputs
$sanitizedInputs = [];
// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Call the sanitizeInputs function to sanitize and validate inputs
    $sanitizedInputs = sanitizeInputs($_POST, $errors);
    // If there are no errors, proceed with database operations
    if (empty($errors)) {
        try {
            // Extract sanitized inputs for easier use
            $firstName = $sanitizedInputs['firstName'];
            $surname = $sanitizedInputs['surname'];
            $companyName = $sanitizedInputs['companyName'];
            $addresses = isset($sanitizedInputs['address']) ? $sanitizedInputs['address'] : [];
            $phoneNumbers = isset($sanitizedInputs['phoneNumber']) ? $sanitizedInputs['phoneNumber'] : [];
            $emailAddresses = isset($sanitizedInputs['emailAddress']) ? $sanitizedInputs['emailAddress'] : [];

            // Ensure at least one phone number is provided
            if (empty($phoneNumbers)) {
                $errors['phoneNumber'] = "At least one phone number is required.";
            }

            if (empty($errors)) {
                // Start a transaction to ensure atomicity
                $pdo->beginTransaction();
                try {
                    // Insert into the `customers` table and get the generated CustomerID
                    $customerSql = "INSERT INTO customers (FirstName, LastName, Company)
                                    VALUES (:firstName, :surname, :companyName)";
                    $customerStmt = $pdo->prepare($customerSql);
                    $customerStmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
                    $customerStmt->bindParam(':surname', $surname, PDO::PARAM_STR);
                    $customerStmt->bindParam(':companyName', $companyName, PDO::PARAM_STR);
                    $customerStmt->execute();
                    // Get the last inserted CustomerID
                    $customerID = $pdo->lastInsertId();
                    if (!$customerID) {
                        throw new Exception("Failed to retrieve CustomerID after insertion.");
                    }

                    // Insert into the `addresses` table
                    foreach ($addresses as $address) {
                        if (!empty($address)) {
                            $addressSql = "INSERT INTO addresses (CustomerID, Address)
                                           VALUES (:customerID, :address)";
                            $addressStmt = $pdo->prepare($addressSql);
                            $addressStmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
                            $addressStmt->bindParam(':address', $address, PDO::PARAM_STR);
                            $addressStmt->execute();
                        }
                    }

                    // Insert into the `phonenumbers` table
                    foreach ($phoneNumbers as $phoneNumber) {
                        if (!empty($phoneNumber)) {
                            $phoneSql = "INSERT INTO phonenumbers (CustomerID, Nr)
                                         VALUES (:customerID, :phoneNumber)";
                            $phoneStmt = $pdo->prepare($phoneSql);
                            $phoneStmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
                            $phoneStmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
                            $phoneStmt->execute();
                        }
                    }

                    // Insert into the `emails` table
                    foreach ($emailAddresses as $emailAddress) {
                        if (!empty($emailAddress)) {
                            $emailSql = "INSERT INTO emails (CustomerID, Emails)
                                         VALUES (:customerID, :emailAddress)";
                            $emailStmt = $pdo->prepare($emailSql);
                            $emailStmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
                            $emailStmt->bindParam(':emailAddress', $emailAddress, PDO::PARAM_STR);
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
// Include the HTML form
include 'index.php';
?>
