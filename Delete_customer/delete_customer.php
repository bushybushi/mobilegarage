<?php
// delete_customer.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Set response as JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customerID'])) {
    $customerID = intval($_POST['customerID']);

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete customer-related records
        $stmt = $pdo->prepare("DELETE FROM emails WHERE CustomerID = :customerID");
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM phonenumbers WHERE CustomerID = :customerID");
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM addresses WHERE CustomerID = :customerID");
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();

        // Finally, delete from customers table
        $stmt = $pdo->prepare("DELETE FROM customers WHERE CustomerID = :customerID");
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Return a success response
        echo json_encode(["success" => true, "message" => "Customer deleted successfully."]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error deleting customer: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
