<?php
session_start();
require_once "../config/db_connection.php";
require_once "../includes/sanitize_inputs.php";

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['partId']) || !is_numeric($_POST['partId'])) {
        throw new Exception('Invalid part ID');
    }

    $partId = (int)$_POST['partId'];
    $partDesc = sanitize_input($_POST['partDesc']);
    $piecesPurch = (int)$_POST['piecesPurch'];
    $pricePerPiece = (float)$_POST['pricePerPiece'];
    $priceBulk = isset($_POST['priceBulk']) && $_POST['priceBulk'] !== '' ? (float)$_POST['priceBulk'] : null;
    $sellingPrice = (float)$_POST['sellingPrice'];

    $pdo = require '../config/db_connection.php';
    
    // Start transaction
    $pdo->beginTransaction();

    try {
        // First get the current part information
        $stmt = $pdo->prepare("SELECT PiecesPurch, Stock FROM Parts WHERE PartID = ?");
        $stmt->execute([$partId]);
        $currentPart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentPart) {
            throw new Exception('Part not found');
        }

        // Calculate new stock based on the ratio
        // If current is 9 pieces with 5 stock (ratio 5/9), and new pieces is 10,
        // then new stock should be (5/9) * 10 = 5.56, rounded to 6
        $ratio = $currentPart['Stock'] / $currentPart['PiecesPurch'];
        $newStock = round($ratio * $piecesPurch);

        // Ensure stock never exceeds pieces purchased
        $newStock = min($newStock, $piecesPurch);

        // Update the Parts table with all the new information
        $stmt = $pdo->prepare("
            UPDATE Parts 
            SET PartDesc = ?, 
                PiecesPurch = ?,
                PricePerPiece = ?, 
                PriceBulk = ?, 
                SellPrice = ?,
                Stock = ?
            WHERE PartID = ?
        ");
        $stmt->execute([
            $partDesc,
            $piecesPurch,
            $pricePerPiece,
            $priceBulk,
            $sellingPrice,
            $newStock,
            $partId
        ]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Part updated successfully'
        ]);
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 