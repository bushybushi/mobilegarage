<?php
require_once '../config/db_connection.php';

if (isset($_GET['id'])) {
    $customerId = (int)$_GET['id'];
    
    try {
        $sql = "SELECT Nr FROM phonenumbers WHERE CustomerID = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customerId]);
        
        $phone = $stmt->fetchColumn();
        
        header('Content-Type: application/json');
        echo json_encode(['phone' => $phone]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Customer ID not provided']);
}
?> 