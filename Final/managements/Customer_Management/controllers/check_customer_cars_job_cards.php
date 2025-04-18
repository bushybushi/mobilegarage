<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection
require_once "../config/db_connection.php";

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the customer ID from the POST data
    $customerId = isset($_POST['customerId']) ? $_POST['customerId'] : null;
    
    if (!$customerId) {
        echo json_encode(['error' => 'Customer ID is required']);
        exit;
    }
    
    try {
        // Check if the customer has any cars with associated job cards
        $sql = "SELECT COUNT(*) as count 
                FROM cars c 
                JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
                JOIN JobCar jc ON c.LicenseNr = jc.LicenseNr 
                WHERE ca.CustomerID = :customerId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['customerId' => $customerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the result
        echo json_encode([
            'hasJobCards' => $result['count'] > 0,
            'jobCardsCount' => $result['count']
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?> 