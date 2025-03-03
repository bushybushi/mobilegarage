<?php
// delete_user.php

// Include the database connection file
$pdo = require '../db_connection.php';

// Include sanitize
require_once '../sanitize_inputs.php'

// Set response as JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = intval($_POST['username']);
	$username = sanitizeInputs($username);

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete user-related record
        $stmt = $pdo->prepare("DELETE FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Return a success response
        echo json_encode(["success" => true, "message" => "User deleted successfully."]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error deleting User: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
