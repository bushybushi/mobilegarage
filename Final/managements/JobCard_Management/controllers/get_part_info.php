<?php
require_once '../config/db_connection.php';

// Get part ID from request
$partId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($partId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid part ID']);
    exit;
}

try {
    $pdo = require '../config/db_connection.php';
    
    // Query to get part information including SellPrice
    $sql = "SELECT PartID, PartDesc, SellPrice FROM parts WHERE PartID = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$partId]);
    
    $part = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($part) {
        echo json_encode(['success' => true, 'part' => $part]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Part not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 