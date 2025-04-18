<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

// Start a new session to maintain user state across pages
session_start();
require_once "../includes/sanitize_inputs.php";

// Tell the browser we'll be sending back JSON data
header('Content-Type: application/json');

// Log the delete request details for debugging purposes
error_log("Delete part request: " . json_encode($_POST));

try {
    // Check if a part ID was provided in the request
    if (!isset($_POST['partId'])) {
        error_log("Part ID not set in request");
        throw new Exception('Part ID not provided');
    }
    
    // Get the part ID from the request
    $partId = $_POST['partId'];
    error_log("Original part ID: " . $partId);
    
    // Check if this is a temporary part (new parts that haven't been saved yet start with 'temp_')
    if (is_string($partId) && strpos($partId, 'temp_') === 0) {
        error_log("Attempted to delete a temporary part from database: " . $partId);
        throw new Exception('Cannot delete a temporary part from database');
    }
    
    // Validate that the part ID is a number
    if (!is_numeric($partId)) {
        error_log("Invalid part ID format: " . $partId);
        throw new Exception('Invalid part ID format: must be numeric');
    }
    
    // Convert the part ID to an integer for database operations
    $partId = (int)$partId;
    error_log("Processing delete for part ID: " . $partId);
    
    // Make sure the part ID is positive
    if ($partId <= 0) {
        error_log("Invalid part ID value: " . $partId);
        throw new Exception('Invalid part ID value');
    }
    
    // Get database connection
    $pdo = require '../config/db_connection.php';
    
    // Start a database transaction to ensure all related deletions succeed or none do
    $pdo->beginTransaction();

    try {
        // First remove any references to this part in job cards
        $stmt = $pdo->prepare("DELETE FROM JobCardParts WHERE PartID = ?");
        $stmt->execute([$partId]);
        error_log("Deleted from JobCardParts, rows affected: " . $stmt->rowCount());

        // Then remove any references in the parts supply table
        $stmt = $pdo->prepare("DELETE FROM PartsSupply WHERE PartID = ?");
        $stmt->execute([$partId]);
        error_log("Deleted from PartsSupply, rows affected: " . $stmt->rowCount());

        // Remove any supplier relationships for this part
        $stmt = $pdo->prepare("DELETE FROM PartSupplier WHERE PartID = ?");
        $stmt->execute([$partId]);
        error_log("Deleted from PartSupplier, rows affected: " . $stmt->rowCount());

        // Finally delete the part itself from the Parts table
        $stmt = $pdo->prepare("DELETE FROM Parts WHERE PartID = ?");
        $stmt->execute([$partId]);
        $rowsAffected = $stmt->rowCount();
        error_log("Deleted from Parts, rows affected: " . $rowsAffected);
        
        // Log a warning if no rows were actually deleted from the Parts table
        if ($rowsAffected === 0) {
            error_log("Warning: No rows were deleted from Parts table for ID: " . $partId);
        }

        // If we got here, everything worked, so commit the transaction
        $pdo->commit();
        error_log("Transaction committed successfully");

        // Send success response back to the client
        echo json_encode(['success' => true, 'message' => 'Part deleted successfully']);
    } catch (PDOException $e) {
        // If anything went wrong, roll back all changes
        $pdo->rollBack();
        error_log("PDO Error: " . $e->getMessage());
        throw new Exception('Database error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    // Log any errors and send error response back to the client
    error_log("Exception: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 