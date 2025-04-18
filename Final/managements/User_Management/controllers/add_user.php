<?php

$username = $_POST['username'];
$password = $_POST['password'];
$role_id = $_POST['role_id'];
$security_question_id = $_POST['security_question_id'];
$security_answer = $_POST['security_answer'];
$email = $_POST['email'];

// Create new user object
$user = new User($username, $password, $role_id, $security_question_id, $security_answer, $email);

// Add user to database
if ($user->add()) {
    header('Location: ../views/user_management.php?success=1');
} else {
    header('Location: ../views/user_management.php?error=1');
} 