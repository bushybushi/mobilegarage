<?php
require_once '../config/db_connection.php';

// Get POST data
$licenseNr = $_POST['licenseNr'] ?? '';
$customerId = $_POST['customerId'] ?? '';

if (empty($licenseNr) || empty($customerId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    // Get detailed car information
    $carSql = "SELECT * FROM cars WHERE LicenseNr = :licenseNr";
    $carStmt = $pdo->prepare($carSql);
    $carStmt->execute(['licenseNr' => $licenseNr]);
    $car = $carStmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        http_response_code(404);
        echo json_encode(['error' => 'Car not found']);
        exit;
    }

    // Get associated job cards
    $jobCardSql = "SELECT j.* 
                   FROM jobcards j 
                   JOIN jobcar jc ON j.JobID = jc.JobID 
                   WHERE jc.LicenseNr = :licenseNr 
                   ORDER BY j.DateStart DESC";
    $jobCardStmt = $pdo->prepare($jobCardSql);
    $jobCardStmt->execute(['licenseNr' => $licenseNr]);
    $jobCards = $jobCardStmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    echo json_encode([
        'car' => $car,
        'jobCards' => $jobCards
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 