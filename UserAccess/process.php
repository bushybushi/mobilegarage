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
            header('Location: dashboard.php');
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
                $_SESSION['message'] = 'Password reset link: ' . 
                    'http://' . $_SERVER['HTTP_HOST'] . 
                    dirname($_SERVER['PHP_SELF']) . 
                    '/index.php?page=reset-password&token=' . $token;
            } else {
                $_SESSION['error'] = 'Failed to create reset token. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Invalid security question answer.';
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
