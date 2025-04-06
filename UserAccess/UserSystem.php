<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';

// User Model Class
class UserModel {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    public function validateLogin($identifier, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['passwrd'])) {
            return $user;
        }
        return false;
    }

    public function updatePassword($identifier, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET passwrd = ? WHERE email = ? OR username = ?");
        return $stmt->execute([$hashedPassword, $identifier, $identifier]);
    }

    public function getUserByIdentifier($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isAdmin($identifier) {
        $user = $this->getUserByIdentifier($identifier);
        return $user && $user['admin'] == 1;
    }

    public function createResetToken($identifier) {
        $user = $this->getUserByIdentifier($identifier);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
        if ($stmt->execute([$user['username'], $token, $expiry])) {
            return $token;
        }
        return false;
    }

    public function validateResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM password_reset_tokens 
            WHERE token = ? AND used = 0 AND expiry > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markTokenAsUsed($token) {
        $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public function getSecurityQuestions() {
        $stmt = $this->db->prepare("SELECT * FROM security_questions");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function updateSecurityQuestion($identifier, $questionId, $answer) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET security_question_id = ?, security_answer = ? 
            WHERE email = ? OR username = ?
        ");
        return $stmt->execute([$questionId, $answer, $identifier, $identifier]);
    }

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

// User View Class
class UserView {
    public function showLoginForm() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login</title>
        </head>
        <body>
            <h2>Login</h2>
            <?php $this->showMessages(); ?>
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div>
                    <label for="identifier">Username or Email</label>
                    <input type="text" id="identifier" name="identifier" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <p>
                <a href="index.php?page=forgot-password">Forgot Password?</a> |
                <a href="index.php?page=register">Register</a>
            </p>
        </body>
        </html>
        <?php
    }

    public function showForgotPasswordForm() {
        $model = new UserModel();
        $questions = $model->getSecurityQuestions();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Forgot Password</title>
        </head>
        <body>
            <h2>Forgot Password</h2>
            <?php $this->showMessages(); ?>
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="forgot-password">
                <div>
                    <label for="identifier">Username or Email</label>
                    <input type="text" id="identifier" name="identifier" required>
                </div>
                <div>
                    <label for="security_question">Security Question</label>
                    <select id="security_question" name="security_question_id" required>
                        <option value="">Select a security question</option>
                        <?php foreach ($questions as $question): ?>
                            <option value="<?php echo $question['id']; ?>">
                                <?php echo htmlspecialchars($question['question']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="security_answer">Answer</label>
                    <input type="text" id="security_answer" name="security_answer" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
            <p>
                <a href="index.php">Back to Login</a>
            </p>
        </body>
        </html>
        <?php
    }

    public function showResetPasswordForm($token) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
        </head>
        <body>
            <h2>Reset Password</h2>
            <?php $this->showMessages(); ?>
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="reset-password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div>
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
            <p>
                <a href="index.php">Back to Login</a>
            </p>
        </body>
        </html>
        <?php
    }

    public function showRegistrationForm() {
        $model = new UserModel();
        $questions = $model->getSecurityQuestions();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Register</title>
        </head>
        <body>
            <h2>Register</h2>
            <?php $this->showMessages(); ?>
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div>
                    <label for="security_question">Security Question</label>
                    <select id="security_question" name="security_question_id" required>
                        <option value="">Select a security question</option>
                        <?php foreach ($questions as $question): ?>
                            <option value="<?php echo $question['id']; ?>">
                                <?php echo htmlspecialchars($question['question']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="security_answer">Answer</label>
                    <input type="text" id="security_answer" name="security_answer" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>
                <a href="index.php">Back to Login</a>
            </p>
        </body>
        </html>
        <?php
    }

    public function showDashboard($user) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard</title>
        </head>
        <body>
            <h1>Dashboard</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="process.php?action=logout">Logout</a>
            </div>

            <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p>Here's your activity overview for today.</p>

            <div>
                <h2>Profile Information</h2>
                <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p>Account Type: <?php echo $user['admin'] ? 'Administrator' : 'Standard User'; ?></p>
            </div>
        </body>
        </html>
        <?php
    }

    public function showMessage($message) {
        if (isset($_SESSION['message'])) {
            echo '<div>' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
    }

    public function showError($error) {
        if (isset($_SESSION['error'])) {
            echo '<div>' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
    }

    public function showMessages() {
        $this->showMessage($_SESSION['message'] ?? null);
        $this->showError($_SESSION['error'] ?? null);
    }
} 
