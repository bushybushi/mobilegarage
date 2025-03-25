<?php
session_start();
require_once "../config/db_connection.php";
require_once "../includes/sanitize_inputs.php";

// Set JSON content type header
header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['partId']) || !is_numeric($_POST['partId'])) {
        throw new Exception('Invalid part ID');
    }

    $partId = (int)$_POST['partId'];
    $pdo = require '../config/db_connection.php';
    
    // Start transaction
    $pdo->beginTransaction();

    try {
        // First delete from JobCardParts if exists
        $stmt = $pdo->prepare("DELETE FROM JobCardParts WHERE PartID = ?");
        $stmt->execute([$partId]);

        // Then delete from PartsSupply
        $stmt = $pdo->prepare("DELETE FROM PartsSupply WHERE PartID = ?");
        $stmt->execute([$partId]);

        // Then delete from PartSupplier if exists
        $stmt = $pdo->prepare("DELETE FROM PartSupplier WHERE PartID = ?");
        $stmt->execute([$partId]);

        // Finally delete the part itself
        $stmt = $pdo->prepare("DELETE FROM Parts WHERE PartID = ?");
        $stmt->execute([$partId]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Part deleted successfully']);
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 