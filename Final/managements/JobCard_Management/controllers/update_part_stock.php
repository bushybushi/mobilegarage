<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

// Get parameters
$partId = isset($_POST['partId']) ? (int)$_POST['partId'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($partId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid part ID or quantity']);
    exit;
}

try {
    if ($action === 'return') {
        // Start transaction
        $pdo->beginTransaction();

        // Get current stock
        $stmt = $pdo->prepare("SELECT Stock FROM parts WHERE PartID = ?");
        $stmt->execute([$partId]);
        $currentStock = $stmt->fetchColumn();

        if ($currentStock === false) {
            throw new Exception('Part not found');
        }

        // Calculate new stock
        $newStock = $currentStock + $quantity;

        // Update stock
        $stmt = $pdo->prepare("UPDATE parts SET Stock = ? WHERE PartID = ?");
        $stmt->execute([$newStock, $partId]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'newStock' => $newStock
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 