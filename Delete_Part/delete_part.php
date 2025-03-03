<?php
// delete_part.php

// Include database connection
$pdo = require 'db_connection.php';

// Check if partID is sent via POST
if (!isset($_POST['partID'])) {
    echo json_encode(["success" => false, "message" => "No part specified."]);
    exit;
}

$partID = intval($_POST['partID']);

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Remove the association from partssupply
    $sql = "DELETE FROM partssupply WHERE PartID = :partID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':partID', $partID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the part from parts table
    $sql = "DELETE FROM parts WHERE PartID = :partID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':partID', $partID, PDO::PARAM_INT);
    $stmt->execute();

    // Commit the transaction
    $pdo->commit();

    // Success response
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    // Rollback in case of an error
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Error deleting part: " . $e->getMessage()]);
}
?>
