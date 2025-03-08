<?php
	// Include the input sanitization file
	require '../sanitize_inputs.php';
	require '../flatten.php';
	// Get the PDO instance from the included file
	$pdo = require '../db_connection.php';

	class user {
		public $username;
		protected $password;
		public $email;
		protected $admin;
		
		function __construct($username, $password, $email, $admin) {	
			editUsername($username);
			editPassword($password);
			editEmail($email);
			editAdmin($admin);
		}
		
		function getUsername() {
			return $this->username;
		}
		
		function getPassword() {
			return $this->username;
		}
		
		function getEmail() {
			return $this->username;
		}
		
		function getAdmin() {
			return $this->username;
		}
		
		function editUsername($username) {
			$this->username = $username;
		}
		
		function editPassword($password) {
			$this->password = $password;
		}
		
		function editEmail($email) {
			$this->email = $email;
		}
		
		function editAdmin($admin) {
			$this->admin = $admin;
		}
		
		
	}
	
	class userManagement {
		
		// Initialize an array to store sanitized inputs
		$sanitizedInputs = [];
		$sanitizedInputs = sanitizeInputs($_POST);
		$user = new user($sanitizedInputs['username'],$sanitizedInputs['passwrd'],$sanitizedInputs['email'], $sanitizedInputs['admin']);
		
		
		// Function to Add a new User
		function AddUser {

	// Initialize an array to store error messages
	$errors = [];


	// Check if the form was submitted via POST
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
    // If there are no errors, proceed with database operations
    if (empty($errors)) {
        try {
            // Start a transaction to ensure atomicity
            $pdo->beginTransaction();
            try {
                // Insert into the `users` table
                $userSql = "INSERT INTO users (username, passwrd, email, admin)
                            VALUES (?, ?, ?, ?)";
                $userStmt = $pdo->prepare($userSql);
                $userStmt->bindParam("ssss", $user->getUsername(),password_hash($user->getPassword(), PASSWORD_DEFAULT),$user->getEmail(),$user->getAdmin());
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
		}
		// Function to Update User
		function UpdateUser {
			$old_username = isset($_POST['old_username']) ? $_POST['old_username'] : null;

if ($username === null) {
    echo json_encode(['status' => 'error', 'message' => 'Username not provided']);
    exit;
}

try {
    // Fetch old user data
    $userSql = 'SELECT * FROM users WHERE username = ?';
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$old_username]);
	
	$userData = $userStmt->fetch();

    $old_user = new user(...$userData);

    if ($old_user) {
        // Start a transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
            // Check if any user data has changed
            $passwordChanged = !empty($password); // Check if a new password was provided
            $updatePasswordQuery = $passwordChanged ? ", passwrd = ?" : "";

            // Prepare the update query
            $userSql = "UPDATE users 
                        SET username = ?, email = ?, admin = ? $updatePasswordQuery
                        WHERE username = :username";
            $userStmt->bindParam("ssss", $user->getUsername(),$user->getEmail(),$user->getAdmin(),$user->getPassword(),$old_user->getUsername());
                $userStmt->execute();

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
		}
		
		// Function to delete User
		
		function DeleteUser {
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
			$username = intval($_POST['username']);
			$username = sanitizeInputs($username);

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete user-related record
        $stmt = $pdo->prepare("DELETE FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Return a success response
        echo json_encode(["success" => true, "message" => "User deleted successfully."]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error deleting User: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
		}
		
		
	}
?>