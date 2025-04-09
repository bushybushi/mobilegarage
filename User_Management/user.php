<?php
require_once 'db_connection.php';

class User {
    private $pdo;
    public function __construct() {
        $this->pdo = require 'db_connection.php';
    }

    public function addUser($username, $password, $email, $admin, $security_question_id, $security_answer) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, passwrd, email, admin, security_question_id, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$username, $hashedPassword, $email, $admin, $security_question_id, $security_answer]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function editUser($username, $email, $admin, $password = null) {
        try {
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("UPDATE users SET email = ?, admin = ?, passwrd = ? WHERE username = ?");
                return $stmt->execute([$email, $admin, $hashedPassword, $username]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET email = ?, admin = ? WHERE username = ?");
                return $stmt->execute([$email, $admin, $username]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteUser($username) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE username = ?");
            return $stmt->execute([$username]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUser($username) {
        try {
            $stmt = $this->pdo->prepare("SELECT username, email, admin, security_question_id, security_answer FROM users WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user = new User();
    
    if ($_POST['action'] === 'edit') {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $admin = ($_POST['admin'] === 'yes') ? 1 : 0;
        $password = !empty($_POST['passwrd']) ? $_POST['passwrd'] : null;
        
        $result = $user->editUser($username, $email, $admin, $password);
        echo json_encode(["status" => $result ? "success" : "error", "message" => $result ? "User updated successfully." : "Failed to update user."]);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $username = htmlspecialchars($_POST['username']);
        
        $result = $user->deleteUser($username);
        echo json_encode(["status" => $result ? "success" : "error", "message" => $result ? "User deleted successfully." : "Failed to delete user."]);
        exit;
    }
}
?>