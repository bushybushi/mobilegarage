<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'UserSystem.php';

$model = new UserModel();
$view = new UserView();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: index.php');
            exit;
        }

        $user = $model->validateLogin($identifier, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['message'] = 'Login successful!';
            header('Location: dashboard.php');                  //CHANGE THE FILE NAME TO HOMEPAGE
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username/email or password';
            header('Location: index.php');
            exit;
        }
        break;

    case 'logout':
        session_destroy();
        $_SESSION['message'] = 'You have been logged out successfully';
        header('Location: index.php');
        exit;
        break;

    /*case 'forgot-password':
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $_SESSION['error'] = 'Please enter your email address';
            header('Location: index.php?page=forgot-password');
            exit;
        }

        $token = $model->createResetToken($email);
        
        if ($token) {
            
            $_SESSION['message'] = 'Password reset link: ' . 
                'http://' . $_SERVER['HTTP_HOST'] . 
                dirname($_SERVER['PHP_SELF']) . 
                '/index.php?page=reset-password&token=' . $token;
        } else {
            $_SESSION['message'] = 'If an account exists with this email, you will receive password reset instructions.';
        }
        
        header('Location: index.php?page=forgot-password');
        exit;
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
        break;*/

    default:
        header('Location: index.php');
        exit;
        break;
} 