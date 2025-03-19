<?php
require_once '../config/db_connection.php';

// Get customer ID from request
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

try {
    $pdo = require '../config/db_connection.php';
    
    // Query to get all cars associated with this customer
    $sql = "SELECT c.* 
            FROM Cars c
            JOIN CarAssoc ca ON c.LicenseNr = ca.LicenseNr
            WHERE ca.CustomerID = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'cars' => $cars]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 
