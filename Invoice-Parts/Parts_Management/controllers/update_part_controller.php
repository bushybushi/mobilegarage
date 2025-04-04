<?php
// Start a session to maintain user state between pages
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
session_start();
require_once "../config/db_connection.php";
require_once "../includes/sanitize_inputs.php";
require_once "../models/parts_model.php";

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
    $dateCreated = sanitize_input($_POST['dateCreated']);
    $supplierID = isset($_POST['supplierID']) ? (int)$_POST['supplierID'] : null;
    $supplierName = isset($_POST['supplierName']) ? sanitize_input($_POST['supplierName']) : null;
    $supplierPhone = isset($_POST['supplierPhone']) ? sanitize_input($_POST['supplierPhone']) : null;
    $supplierEmail = isset($_POST['supplierEmail']) ? sanitize_input($_POST['supplierEmail']) : null;

    // Create instance of PartsManagement
    $partsMang = new PartsManagement();

    // Get the current part information to calculate new stock
    $currentPart = $partsMang->ViewSingle($partId);
    
    if (!$currentPart) {
        throw new Exception('Part not found');
    }

    // Calculate new stock based on the ratio of current stock to pieces purchased
    $ratio = $currentPart['Stock'] / $currentPart['PiecesPurch'];
    $newStock = round($ratio * $piecesPurch);

    // Make sure new stock doesn't exceed new pieces purchased amount
    $newStock = min($newStock, $piecesPurch);

    // Update the part
    $updateData = [
        'partId' => $partId,
        'partDesc' => $partDesc,
        'piecesPurch' => $piecesPurch,
        'pricePerPiece' => $pricePerPiece,
        'priceBulk' => $priceBulk,
        'sellingPrice' => $sellingPrice,
        'stock' => $newStock,
        'dateCreated' => $dateCreated,
        'supplierID' => $supplierID,
        'supplierName' => $supplierName,
        'supplierPhone' => $supplierPhone,
        'supplierEmail' => $supplierEmail
    ];

    // Update the part in the database
    $success = $partsMang->Update($partId, $updateData);

    if (!$success) {
        throw new Exception('Failed to update part');
    }

    // Send success response back to client
    echo json_encode([
        'success' => true,
        'message' => 'Part updated successfully'
    ]);

} catch (Exception $e) {
    // Send error response back to client
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 