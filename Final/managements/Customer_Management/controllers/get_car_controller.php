<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/car_model.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['licenseNr'])) {
        throw new Exception('License number is required');
    }

    $licenseNr = sanitizeInputs($_GET['licenseNr']);
    
    // Get car details with customer association
    $carSql = "SELECT c.*, ca.CustomerID 
               FROM cars c 
               JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
               WHERE c.LicenseNr = :licenseNr";
    $carStmt = $pdo->prepare($carSql);
    $carStmt->execute(['licenseNr' => $licenseNr]);
    $car = $carStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        throw new Exception('Car not found');
    }
    
    echo json_encode([
        'status' => 'success',
        'car' => $car
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 