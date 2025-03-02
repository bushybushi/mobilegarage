<?php

// Include the input sanitization file
require_once 'sanitize_inputs.php';

// Include the function to flatten
require_once 'flatten.php';

// Get the PDO instance from the included file
$pdo = require 'db_connection.php';

$username = isset($_POST['username']) ? $_POST['username'] : null;

if ($username === null) {
    echo json_encode(['status' => 'error', 'message' => 'Username not provided']);
    exit;
}

try {
    // Fetch old user data
    $userSql = 'SELECT * FROM users WHERE username = ?';
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$username]);

    $old_user = $userStmt->fetch();

    if ($old_user) {
        // Call the sanitizeInputs function to sanitize and validate inputs
        $sanitizedInputs = sanitizeInputs($_POST);
        // Extract sanitized inputs for easier use
        $new_username = $sanitizedInputs['username'];
        $email = $sanitizedInputs['email'];
        $password = $sanitizedInputs['passwrd'];
        $admin = $sanitizedInputs['admin'];

        // Start a transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
            // Check if any user data has changed
            $passwordChanged = !empty($password); // Check if a new password was provided
            $updatePasswordQuery = $passwordChanged ? ", passwrd = :password" : "";

            // Prepare the update query
            $userSql = "UPDATE users 
                        SET username = :new_username, email = :email, admin = :admin $updatePasswordQuery
                        WHERE username = :username";
            $userStmt = $pdo->prepare($userSql);
            $userStmt->bindParam(':new_username', $new_username, PDO::PARAM_STR);
            $userStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $userStmt->bindParam(':admin', $admin, PDO::PARAM_INT);
            $userStmt->bindParam(':username', $username, PDO::PARAM_STR);

            // Hash the new password if changed
            if ($passwordChanged) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            }

            $userStmt->execute();

            // Commit the transaction
            $pdo->commit();

            // Return success response
            echo json_encode(['status' => 'success', 'message' => 'User Updated Successfully']);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while updating the user']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
