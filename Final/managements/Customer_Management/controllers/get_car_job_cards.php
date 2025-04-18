<?php
// Include database connection file
require_once '../config/db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if license number is provided
if (!isset($_POST['licenseNr']) || empty($_POST['licenseNr'])) {
    echo json_encode([
        'success' => false,
        'message' => 'License number is required'
    ]);
    exit;
}

// Get license number from POST data
$licenseNr = $_POST['licenseNr'];

try {
    // Prepare SQL query to get job cards for the car
    $sql = "SELECT j.JobID, j.DateStart as JobDate, j.JobDesc as Description, j.JobReport as Status 
            FROM jobcards j 
            JOIN jobcar jc ON j.JobID = jc.JobID
            WHERE jc.LicenseNr = :licenseNr 
            ORDER BY j.DateStart DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':licenseNr', $licenseNr, PDO::PARAM_STR);
    $stmt->execute();
    
    // Fetch all job cards
    $jobCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return job cards as JSON
    echo json_encode([
        'success' => true,
        'jobCards' => $jobCards
    ]);
} catch (PDOException $e) {
    // Log the error and return error message
    error_log("Error fetching job cards: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching job cards: ' . $e->getMessage()
    ]);
} 