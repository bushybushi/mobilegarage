<?php
// User.php
require '../includes/sanitize_inputs.php';
require '../includes/flatten.php';
$pdo = require '../includes/db_connection.php';

class User {
    public $username;
    protected $password;
    public $email;
    protected $admin;

    function __construct($username, $password, $email, $admin) {
        $this->editUsername($username);
        $this->editPassword($password);
        $this->editEmail($email);
        $this->editAdmin($admin);
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getEmail() {
        return $this->email;
    }

    function getAdmin() {
        return $this->admin;
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

class UserManagement {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addUser(array $data): array {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO users (username, passwrd, email, admin) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['username'],
                password_hash($data['passwrd'], PASSWORD_DEFAULT),
                $data['email'],
                $data['admin']
            ]);
            $this->pdo->commit();
            return ['status' => 'success', 'message' => 'User added'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function editUser(array $data, string $currentUsername): array {
        try {
            $this->pdo->beginTransaction();
            $update = [
                'username' => $data['username'],
                'email' => $data['email'],
                'admin' => $data['admin']
            ];

            if (!empty($data['passwrd'])) {
                $update['passwrd'] = password_hash($data['passwrd'], PASSWORD_DEFAULT);
            }

            $setClause = '';
            $params = [];
            foreach ($update as $field => $value) {
                $setClause .= "$field = :$field, ";
                $params[":$field"] = $value;
            }
            $setClause = rtrim($setClause, ', ');
            $sql = "UPDATE users SET $setClause WHERE username = :current_username";
            $stmt = $this->pdo->prepare($sql);
            $params[':current_username'] = $currentUsername;

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                $this->pdo->commit();
                return ['status' => 'success', 'message' => 'User updated'];
            } else {
                $this->pdo->rollBack();
                return ['status' => 'error', 'message' => 'Update failed'];
            }
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>