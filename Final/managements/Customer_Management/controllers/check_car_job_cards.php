<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection
require_once "../config/db_connection.php";

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the license number from the POST data
    $licenseNr = isset($_POST['licenseNr']) ? $_POST['licenseNr'] : null;
    
    if (!$licenseNr) {
        echo json_encode(['error' => 'License number is required']);
        exit;
    }
    
    try {
        // Check if the car has associated job cards
        $sql = "SELECT COUNT(*) as count FROM jobcar WHERE LicenseNr = :licenseNr";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['licenseNr' => $licenseNr]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the result
        echo json_encode([
            'success' => true,
            'hasJobCards' => $result['count'] > 0,
            'jobCardsCount' => $result['count']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?> 