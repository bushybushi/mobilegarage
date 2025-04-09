<?php
// adduser.php
require_once 'user.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = new User();
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
    $password = isset($_POST['passwrd']) ? htmlspecialchars($_POST['passwrd']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $admin = isset($_POST['admin']) && $_POST['admin'] === 'yes' ? 1 : 0;
    $security_question_id = isset($_POST['security_question_id']) ? (int)$_POST['security_question_id'] : 0;
    $security_answer = isset($_POST['security_answer']) ? htmlspecialchars($_POST['security_answer']) : '';

    $result = $user->addUser($username, $password, $email, $admin, $security_question_id, $security_answer);

    if ($result) {
        $_SESSION['message'] = "User added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to add user.";
        $_SESSION['message_type'] = "danger";
    }

    // Redirect back to UserManagementMain.php
    header("Location: UserManagementMain.php");
    exit;
}
?>
