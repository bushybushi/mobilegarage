<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files and initialize model and view
require_once 'UserSystem.php';
$model = new UserModel();
$view = new UserView();

// Get action from POST or GET request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Process different user actions
switch ($action) {
    case 'login':
        // Get login credentials
        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input fields
        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: index.php');
            exit;
        }

        // Attempt to validate login credentials
        $user = $model->validateLogin($identifier, $password);

        if ($user) {
            // Set user session on successful login
            $_SESSION['user'] = $user;
            $_SESSION['message'] = 'Login successful!';
            
            // Handle post-login redirect
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']); // Clear stored URL
                header('Location: ' . $redirect_url);
            } else {
                header('Location: ../dashboard.php');
            }
            exit;
        } else {
            // Handle failed login
            $_SESSION['error'] = 'Invalid username/email or password';
            header('Location: index.php');
            exit;
        }
        break;

    case 'logout':
        // Clear session and redirect to login
        session_destroy();
        $_SESSION['message'] = 'You have been logged out successfully';
        header('Location: index.php');
        exit;
        break;

    case 'forgot-password':
        $identifier = $_POST['identifier'] ?? '';
        $questionId = $_POST['security_question_id'] ?? '';
        $answer = $_POST['security_answer'] ?? '';
        
        if (empty($identifier) || empty($questionId) || empty($answer)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: index.php?page=forgot-password');
            exit;
        }

        $user = $model->validateSecurityAnswer($identifier, $questionId, $answer);
        
        if ($user) {
            $token = $model->createResetToken($identifier);
            if ($token) {
                $_SESSION['message'] = 'Please set your new password';
                header('Location: index.php?page=reset-password&token=' . $token);
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create reset token. Please try again.';
                header('Location: index.php?page=forgot-password');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Invalid security question answer.';
            header('Location: index.php?page=forgot-password');
            exit;
        }
        break;

    case 'reset-password':
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: index.php?page=reset-password&token=' . $token);
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: index.php?page=reset-password&token=' . $token);
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: index.php?page=reset-password&token=' . $token);
            exit;
        }

        $tokenData = $model->validateResetToken($token);
        
        if ($tokenData) {
            if ($model->updatePassword($tokenData['user_id'], $password)) {
                $model->markTokenAsUsed($token);
                $_SESSION['message'] = 'Your password has been reset successfully.';
                header('Location: index.php');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update password. Please try again.';
                header('Location: index.php?page=reset-password&token=' . $token);
                exit;
            }
        } else {
            $_SESSION['error'] = 'Invalid or expired reset token.';
            header('Location: index.php?page=forgot-password');
            exit;
        }
        break;

    case 'register':
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $questionId = $_POST['security_question_id'] ?? '';
        $answer = $_POST['security_answer'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($questionId) || empty($answer)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: index.php?page=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: index.php?page=register');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: index.php?page=register');
            exit;
        }

        if ($model->registerUser($username, $email, $password, $questionId, $answer)) {
            $_SESSION['message'] = 'Registration successful! You can now login.';
            header('Location: index.php');
        } else {
            $_SESSION['error'] = 'Username or email already exists';
            header('Location: index.php?page=register');
        }
        exit;
        break;

    default:
        header('Location: index.php');
        exit;
        break;
} 
