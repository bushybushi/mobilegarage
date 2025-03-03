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
            $admin = $sanitizedInputs['admin'];

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

                // Display success message
                echo "<h1>New User Added Successfully!</h1>";
                echo "<p><a href='/'>Go Back</a></p>";

                // Clear inputs after successful submission
                $sanitizedInputs = [];
            } catch (Exception $e) {
                // Rollback the transaction in case of an error
                $pdo->rollBack();
                throw $e;
            }
        } catch (PDOException $e) {
            // Handle database errors
            echo "<h1>Error: Unable to Add User</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }
}

// Include the HTML form
include 'AddNewUserForm.php';
?>