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
    $sanitizedInputs = sanitizeInputs($_POST);

    // If there are no errors, proceed with database operations
    if (empty($errors)) {
        try {
            // Extract sanitized inputs for easier use
            $username = $sanitizedInputs['username'];
            $passwrd = password_hash($sanitizedInputs['passwrd'], PASSWORD_DEFAULT); // Hash the password
            $email = $sanitizedInputs['email'];
            $admin = $sanitizedInputs['admin'] === 'yes' ? 1 : 0; // Convert 'yes' to 1 and 'no' to 0;

            // Start a transaction to ensure atomicity
            $pdo->beginTransaction();
            try {
                // Insert into the `users` table
                $userSql = "INSERT INTO users (username, passwrd, email, admin)
                            VALUES (:username, :passwrd, :email, :admin)";
                $userStmt = $pdo->prepare($userSql);
                $userStmt->bindParam(':username', $username, PDO::PARAM_STR);
                $userStmt->bindParam(':passwrd', $passwrd, PDO::PARAM_STR);
                $userStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $userStmt->bindParam(':admin', $admin, PDO::PARAM_STR);
                $userStmt->execute();

                // Commit the transaction
                $pdo->commit();

                // Return a success response
                echo json_encode(['status' => 'success', 'message' => 'New User Added Successfully!']);
            } catch (Exception $e) {
                // Rollback the transaction in case of an error
                $pdo->rollBack();
                // Return an error response
                echo json_encode(['status' => 'error', 'message' => 'An error occurred while adding the user: ' . $e->getMessage()]);
            }
        } catch (PDOException $e) {
            // Handle database errors
            echo json_encode(['status' => 'error', 'message' => 'Error: Unable to Add User: ' . $e->getMessage()]);
        }
    } else {
        // Return validation errors
        echo json_encode(['status' => 'error', 'message' => 'Validation errors occurred', 'errors' => $errors]);
    }
    exit;
}

// Include the HTML form
include 'AddNewUserForm.php';
?>