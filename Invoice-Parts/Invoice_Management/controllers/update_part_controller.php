<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

// Start a session to maintain user state between pages
session_start();
require_once "../config/db_connection.php";
require_once "../includes/sanitize_inputs.php";

// Tell the browser we'll be sending back JSON data
header('Content-Type: application/json');

try {
    // Make sure we have a valid part ID to update
    if (!isset($_POST['partId']) || !is_numeric($_POST['partId'])) {
        throw new Exception('Invalid part ID');
    }

    // Get and validate all the part data from the form
    $partId = (int)$_POST['partId'];
    $partDesc = sanitize_input($_POST['partDesc']);
    $piecesPurch = (int)$_POST['piecesPurch'];
    $pricePerPiece = (float)$_POST['pricePerPiece'];
    $priceBulk = isset($_POST['priceBulk']) && $_POST['priceBulk'] !== '' ? (float)$_POST['priceBulk'] : null;
    $sellingPrice = (float)$_POST['sellingPrice'];

    // Get database connection
    $pdo = require '../config/db_connection.php';
    
    // Start a transaction to ensure data consistency
    $pdo->beginTransaction();

    try {
        // Get the current part information to calculate new stock
        $stmt = $pdo->prepare("SELECT PiecesPurch, Stock FROM Parts WHERE PartID = ?");
        $stmt->execute([$partId]);
        $currentPart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentPart) {
            throw new Exception('Part not found');
        }

        // Calculate new stock based on the ratio of current stock to pieces purchased
        // Example: If current is 9 pieces with 5 stock (ratio 5/9), and new pieces is 10,
        // then new stock should be (5/9) * 10 = 5.56, rounded to 6
        $ratio = $currentPart['Stock'] / $currentPart['PiecesPurch'];
        $newStock = round($ratio * $piecesPurch);

        // Make sure new stock doesn't exceed new pieces purchased amount
        $newStock = min($newStock, $piecesPurch);

        // Update all the part information in the database
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

        // If everything worked, commit the changes
        $pdo->commit();

        // Send success response back to client
        echo json_encode([
            'success' => true,
            'message' => 'Part updated successfully'
        ]);
    } catch (PDOException $e) {
        // If anything went wrong, roll back the changes
        $pdo->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    // Send error response back to client
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 