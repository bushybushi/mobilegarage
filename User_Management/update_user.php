<?php
// update_user.php
require_once 'sanitize_inputs.php';
require_once 'flatten.php';
session_start();

// Get the PDO instance
$pdo = require 'db_connection.php';

$username = isset($_POST['username']) ? $_POST['username'] : null;

if ($username === null) {
    $_SESSION['message'] = 'Username not provided';
    $_SESSION['message_type'] = 'danger';
    header("Location: UserManagementMain.php");
    exit;
}

try {
    // Fetch old user data
    $userSql = 'SELECT * FROM users WHERE username = ?';
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$username]);

    $old_user = $userStmt->fetch();

    if ($old_user) {
        $sanitizedInputs = sanitizeInputs($_POST);
        $new_username = $sanitizedInputs['username'];
        $email = $sanitizedInputs['email'];
        $password = $sanitizedInputs['passwrd'];
        $admin = $sanitizedInputs['admin'];

        $pdo->beginTransaction();
        
        $passwordChanged = !empty($password);
        $updatePasswordQuery = $passwordChanged ? ", passwrd = :password" : "";

        $userSql = "UPDATE users 
                    SET username = :new_username, email = :email, admin = :admin $updatePasswordQuery
                    WHERE username = :username";
        $userStmt = $pdo->prepare($userSql);
        $userStmt->bindParam(':new_username', $new_username, PDO::PARAM_STR);
        $userStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $userStmt->bindParam(':admin', $admin, PDO::PARAM_INT);
        $userStmt->bindParam(':username', $username, PDO::PARAM_STR);

        if ($passwordChanged) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        }

        $userStmt->execute();
        $pdo->commit();

        $_SESSION['message'] = 'User updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'User not found';
        $_SESSION['message_type'] = 'danger';
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = 'An error occurred while updating the user';
    $_SESSION['message_type'] = 'danger';
}

// Redirect back to UserManagementMain.php
header("Location: UserManagementMain.php");
exit;
?>
