<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database connection configuration
require_once '../../config/db_connection.php';

/**
 * User Model Class
 * Handles all database operations related to users
 */
class UserModel {
    private $db;

    // Initialize database connection
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * Validate user login credentials
     * @param string $identifier Username or email
     * @param string $password User's password
     * @return array|false User data if valid, false otherwise
     */
    public function validateLogin($identifier, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['passwrd'])) {
            return $user;
        }
        return false;
    }

    /**
     * Update user's password
     * @param string $identifier Username or email
     * @param string $newPassword New password to set
     * @return bool True if update successful
     */
    public function updatePassword($identifier, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET passwrd = ? WHERE email = ? OR username = ?");
        return $stmt->execute([$hashedPassword, $identifier, $identifier]);
    }

    /**
     * Get user by username or email
     * @param string $identifier Username or email
     * @return array|false User data if found, false otherwise
     */
    public function getUserByIdentifier($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user has admin privileges
     * @param string $identifier Username or email
     * @return bool True if user is admin
     */
    public function isAdmin($identifier) {
        $user = $this->getUserByIdentifier($identifier);
        return $user && $user['admin'] == 1;
    }

    /**
     * Create password reset token
     * @param string $identifier Username or email
     * @return string|false Reset token if created, false otherwise
     */
    public function createResetToken($identifier) {
        $user = $this->getUserByIdentifier($identifier);
        if (!$user) {
            return false;
        }

        // Generate random token and set expiry time
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+6 hours'));
        
        $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
        if ($stmt->execute([$user['username'], $token, $expiry])) {
            return $token;
        }
        return false;
    }

    /**
     * Validate password reset token
     * @param string $token Reset token to validate
     * @return array|false Token data if valid, false otherwise
     */
    public function validateResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM password_reset_tokens 
            WHERE token = ? AND used = 0 AND expiry > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark reset token as used
     * @param string $token Token to mark as used
     * @return bool True if update successful
     */
    public function markTokenAsUsed($token) {
        $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        return $stmt->execute([$token]);
    }

    /**
     * Get all security questions
     * @return array List of security questions
     */
    public function getSecurityQuestions() {
        $stmt = $this->db->prepare("SELECT * FROM security_questions");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate security question answer
     * @param string $identifier Username or email
     * @param int $questionId Security question ID
     * @param string $answer User's answer
     * @return array|false User data if answer correct, false otherwise
     */
    public function validateSecurityAnswer($identifier, $questionId, $answer) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE (email = ? OR username = ?) 
            AND security_question_id = ? 
            AND security_answer = ?
        ");
        $stmt->execute([$identifier, $identifier, $questionId, $answer]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update user's security question
     * @param string $identifier Username or email
     * @param int $questionId New security question ID
     * @param string $answer New security answer
     * @return bool True if update successful
     */
    public function updateSecurityQuestion($identifier, $questionId, $answer) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET security_question_id = ?, security_answer = ? 
            WHERE email = ? OR username = ?
        ");
        return $stmt->execute([$questionId, $answer, $identifier, $identifier]);
    }

    /**
     * Register new user
     * @param string $username New username
     * @param string $email User's email
     * @param string $password User's password
     * @param int $questionId Security question ID
     * @param string $answer Security question answer
     * @return bool True if registration successful
     */
    public function registerUser($username, $email, $password, $questionId, $answer) {
        // Check if username or email already exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, passwrd, security_question_id, security_answer) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$username, $email, $hashedPassword, $questionId, $answer]);
    }
}

/**
 * User View Class
 * Handles all user interface rendering
 */
class UserView {
    /**
     * Display login form
     */
    public function showLoginForm() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Mobile Garage Larnaka</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="shortcut icon" type="image/png" href="../../assets/images/icon.png"/>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <img src="logo.png" alt="Logo" class="logo">
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="identifier" name="identifier" placeholder="Username or Email" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn">Login</button>
                    </form>
                    <div class="forgot-password">
                        <a href="index.php?page=forgot-password">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display password recovery form
     */
    public function showForgotPasswordForm() {
        $model = new UserModel();
        $questions = $model->getSecurityQuestions();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Forgot Password - Mobile Garage Larnaka</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="shortcut icon" type="image/png" href="../../assets/images/icon.png"/>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <img src="logo.png" alt="Logo" class="logo">
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="forgot-password">
                        <div class="form-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="identifier" name="identifier" placeholder="Username or Email" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-question-circle input-icon"></i>
                            <select id="security_question" name="security_question_id" required>
                                <option value="">Select a security question</option>
                                <?php foreach ($questions as $question): ?>
                                    <option value="<?php echo $question['id']; ?>">
                                        <?php echo htmlspecialchars($question['question']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-key input-icon"></i>
                            <input type="text" id="security_answer" name="security_answer" placeholder="Your Answer" required>
                        </div>
                        <button type="submit" class="btn">Reset Password</button>
                    </form>
                    <div class="forgot-password">
                        <a href="index.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display password reset form
     * @param string $token Password reset token
     */
    public function showResetPasswordForm($token) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password - Mobile Garage Larnaka</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="shortcut icon" type="image/png" href="../../assets/images/icon.png"/>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <img src="logo.png" alt="Logo" class="logo">
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="reset-password">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="form-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="New Password" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn">Reset Password</button>
                    </form>
                    <div class="forgot-password">
                        <a href="index.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display registration form
     */
    public function showRegistrationForm() {
        $model = new UserModel();
        $questions = $model->getSecurityQuestions();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Register - Mobile Garage Larnaka</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="shortcut icon" type="image/png" href="../../assets/images/icon.png"/>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <img src="logo.png" alt="Logo" class="logo">
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="form-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-question-circle input-icon"></i>
                            <select id="security_question" name="security_question_id" required>
                                <option value="">Select a security question</option>
                                <?php foreach ($questions as $question): ?>
                                    <option value="<?php echo $question['id']; ?>">
                                        <?php echo htmlspecialchars($question['question']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-key input-icon"></i>
                            <input type="text" id="security_answer" name="security_answer" placeholder="Your Answer" required>
                        </div>
                        <button type="submit" class="btn">Register</button>
                    </form>
                    <div class="forgot-password">
                        <a href="index.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display user dashboard
     * @param array $user User data to display
     */
    public function showDashboard($user) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard - Mobile Garage Larnaka</title>
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="shortcut icon" type="image/png" href="../../assets/images/icon.png"/>
        </head>
        <body>
            <div class="container">
                <div class="dashboard-container">
                    <img src="logo.png" alt="Logo" class="logo">
                    <?php $this->showMessages(); ?>
                    <div class="welcome-message">
                        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                        <p>You are logged in as <?php echo $user['admin'] ? 'Administrator' : 'User'; ?></p>
                    </div>
                    <div class="dashboard-actions">
                        <a href="process.php?action=logout" class="btn">Logout</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display success message
     * @param string $message Message to display
     */
    public function showMessage($message) {
        $_SESSION['message'] = $message;
    }

    /**
     * Display error message
     * @param string $error Error message to display
     */
    public function showError($error) {
        $_SESSION['error'] = $error;
    }

    /**
     * Display all stored messages and errors
     */
    public function showMessages() {
        if (isset($_SESSION['message'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
    }
} 
