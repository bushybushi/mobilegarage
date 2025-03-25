<?php
// Database connection function
function getConnection() {
    try {
        $host = 'localhost';
        $dbname = 'mobilegarage';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// User Model Class
class UserModel {
    private $db;

    public function __construct() {
        $this->db = getConnection();
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
            <title>Login - User Access System</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <h2>Login</h2>
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="identifier">Username or Email</label>
                            <input type="text" id="identifier" name="identifier" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn">Login</button>
                    </form>
                    <p style="margin-top: 1rem; text-align: center;">
                        <a href="index.php?page=forgot-password">Forgot Password?</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function showForgotPasswordForm() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Forgot Password - User Access System</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <h2>Forgot Password</h2>
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="forgot-password">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn">Reset Password</button>
                    </form>
                    <p style="margin-top: 1rem; text-align: center;">
                        <a href="index.php">Back to Login</a>
                    </p>
                </div>
            </div>
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
            <title>Reset Password - User Access System</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <h2>Reset Password</h2>
                    <?php $this->showMessages(); ?>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="reset-password">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn">Reset Password</button>
                    </form>
                    <p style="margin-top: 1rem; text-align: center;">
                        <a href="index.php">Back to Login</a>
                    </p>
                </div>
            </div>
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
            <title>Dashboard - User Access System</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <div class="navbar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="process.php?action=logout">Logout</a>
                </div>
            </div>

            <div class="container">
                <div class="welcome-message">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p>Here's your activity overview for today.</p>
                </div>

                <div class="dashboard-grid">
                    <div class="card">
                        <h2>Profile Information</h2>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Account Type:</strong> <?php echo $user['admin'] ? 'Administrator' : 'Standard User'; ?></p>
                    </div>

                    <div class="card">
                        <h2>Quick Stats</h2>
                        <div class="stats">
                            <div class="stat-card">
                                <h3>Total Logins</h3>
                                <p>42</p>
                            </div>
                            <div class="stat-card">
                                <h3>Last Login</h3>
                                <p>Today</p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h2>Recent Activity</h2>
                        <ul>
                            <li>Login from Windows Device</li>
                            <li>Profile updated</li>
                            <li>Password changed</li>
                        </ul>
                    </div>

                    <div class="card">
                        <h2>System Status</h2>
                        <div class="stats">
                            <div class="stat-card">
                                <h3>System Status</h3>
                                <p style="color: #28a745;">Online</p>
                            </div>
                            <div class="stat-card">
                                <h3>Uptime</h3>
                                <p>99.9%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function showMessage($message) {
        if (isset($_SESSION['message'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
    }

    public function showError($error) {
        if (isset($_SESSION['error'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
    }

    public function showMessages() {
        $this->showMessage($_SESSION['message'] ?? null);
        $this->showError($_SESSION['error'] ?? null);
    }
} 