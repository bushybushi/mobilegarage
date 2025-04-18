<?php
// Include required files for database connection and input handling
require_once __DIR__ . '/../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../includes/flatten.php';

// Get database connection
$pdo = require '../config/db_connection.php';

/**
 * User class - Represents a user in the system
 * Handles user properties and basic operations
 */
class user {
    // User properties
    public $username;        // User's login name
    public $passwrd;         // User's password (hashed)
    public $admin;           // Admin status (0 = no, 1 = yes)
    public $security_question_id;  // ID of security question
    public $security_answer;       // Answer to security question

    /**
     * Constructor - Initialize user with provided data
     */
    function __construct($username = null, $passwrd = null, $admin = null, $security_question_id = null, $security_answer = null) {
        $this->editUsername($username);
        $this->editPassword($passwrd);
        $this->editAdmin($admin);
        $this->editSecurityQuestionId($security_question_id);
        $this->editSecurityAnswer($security_answer);
    }

    // Getter methods - Return user properties
    function getUsername() { return $this->username; }
    function getPassword() { return $this->passwrd; }
    function getAdmin() { return $this->admin; }
    function getSecurityQuestionId() { return $this->security_question_id; }
    function getSecurityAnswer() { return $this->security_answer; }

    // Setter methods - Update user properties
    function editUsername($username) { $this->username = $username; }
    function editPassword($password) { $this->passwrd = $password; }
    function editAdmin($admin) { $this->admin = $admin; }
    function editSecurityQuestionId($security_question_id) { $this->security_question_id = $security_question_id; }
    function editSecurityAnswer($security_answer) { $this->security_answer = $security_answer; }
}

/**
 * UserManagement class - Handles all user-related database operations
 * Manages user creation, updates, deletion, and retrieval
 */
class userManagement {
    private $pdo;           // Database connection
    public $sInput = [];    // Store form input data
    public $user;           // Current user object

    /**
     * Constructor - Initialize database connection and handle form data
     */
    public function __construct() {
        try {
            // Connect to database
            $this->pdo = require_once __DIR__ . '/../config/db_connection.php';
            
            // Get form data (POST or GET)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->sInput = $_POST;
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->sInput = $_GET;
            }

            // Create user object based on action
            if (isset($this->sInput['action']) && $this->sInput['action'] === 'delete') {
                // For delete action, only username is needed
                if (isset($this->sInput['username'])) {
                    $this->user = new user($this->sInput['username']);
                } else {
                    throw new Exception('Username is required for delete action');
                }
            } else if (isset($this->sInput['username'])) {
                // For other actions, create user with all provided data
                $this->user = new user(
                    $this->sInput['username'],
                    $this->sInput['passwrd'] ?? null,
                    $this->sInput['admin'] ?? null,
                    $this->sInput['security_question_id'] ?? null,
                    $this->sInput['security_answer'] ?? null
                );
            }
        } catch(PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Add new user to database
     * @return bool True if successful
     */
    function Add() {
        global $pdo;

        // Check if user object exists
        if (!isset($this->user)) {
            throw new Exception("User object is not properly initialized.");
        }

        try {
            // Password is required for new users
            if (empty($this->user->getPassword())) {
                throw new Exception("Password is required.");
            }

            // Start database transaction
            $pdo->beginTransaction();

            // Hash password for security
            $hashedPassword = password_hash($this->user->getPassword(), PASSWORD_DEFAULT);

            // Insert new user into database
            $userSql = "INSERT INTO users (username, email, passwrd, admin, security_question_id, security_answer) VALUES (?, ?, ?, ?, ?, ?)";
            $userStmt = $pdo->prepare($userSql);
            $result = $userStmt->execute([
                $this->user->getUsername(),
                $this->sInput['email'],
                $hashedPassword,
                $this->user->getAdmin(),
                $this->user->getSecurityQuestionId(),
                $this->user->getSecurityAnswer()
            ]);

            // Save changes to database
            $pdo->commit();
            return $result;
        } catch (Exception $e) {
            // Undo changes if error occurs
            $pdo->rollBack();
            throw new Exception("Error adding user: " . $e->getMessage());
        }
    }

    /**
     * Update existing user information
     * @return bool True if successful
     */
    function Update() {
        global $pdo;
        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Convert admin status to 1/0
            $adminStatus = ($this->user->getAdmin() === 'yes') ? 1 : 0;

            // Update user's email and admin status
            $userSql = "UPDATE users SET email = ?, admin = ? WHERE username = ?";
            $userStmt = $pdo->prepare($userSql);
            $userStmt->execute([
                $this->sInput['email'],
                $adminStatus,
                $this->user->getUsername()
            ]);

            // Update password if new one is provided
            if (!empty($this->user->getPassword())) {
                $hashedPassword = password_hash($this->user->getPassword(), PASSWORD_DEFAULT);
                $passwordSql = "UPDATE users SET passwrd = ? WHERE username = ?";
                $passwordStmt = $pdo->prepare($passwordSql);
                $passwordStmt->execute([$hashedPassword, $this->user->getUsername()]);
            }

            // Save changes to database
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Undo changes if error occurs
            $pdo->rollBack();
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    /**
     * Delete user from database
     * @return bool True if successful
     */
    function Delete() {
        global $pdo;
        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Delete user record
            $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
            $result = $stmt->execute([$this->user->getUsername()]);

            // Save changes to database
            $pdo->commit();

            return $result;
        } catch (PDOException $e) {
            // Undo changes if error occurs
            $pdo->rollBack();
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }

    /**
     * Get user information from database
     * @param string $username Username to look up
     * @return array|null User data or null if not found
     */
    public function getUser($username) {
        global $pdo;
        try {
            // Get user details including security question
            $stmt = $pdo->prepare("
                SELECT u.username, u.email, u.admin, 
                       s.question as security_question, 
                       u.security_answer 
                FROM users u 
                LEFT JOIN security_questions s ON u.security_question_id = s.id 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
