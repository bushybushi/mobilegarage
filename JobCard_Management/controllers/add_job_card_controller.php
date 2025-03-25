<?php
session_start();
require_once "../models/job_card_model.php";

try {
    // First, verify if the license number exists in the cars table
    $licenseNr = $_POST['registration'] ?? '';
    if (empty($licenseNr)) {
        throw new Exception("Registration plate is required");
    }

    // Check if the license number exists in the cars table
    $pdo = require '../config/db_connection.php';
    $stmt = $pdo->prepare("SELECT LicenseNr FROM cars WHERE LicenseNr = ?");
    $stmt->execute([$licenseNr]);
    
    if (!$stmt->fetch()) {
        // If the license number doesn't exist, we need to create it first
        // Generate a temporary unique VIN if not provided
        $vin = $_POST['vin'] ?? 'TEMP_' . uniqid();
        $insertCarStmt = $pdo->prepare("INSERT INTO cars (LicenseNr, VIN) VALUES (?, ?)");
        $insertCarStmt->execute([$licenseNr, $vin]);
    }

    // Handle file uploads
    $photos = [];
    if (!empty($_FILES['photos']['name'][0])) {
        $uploadDir = '../uploads/job_photos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name) && !empty($_FILES['photos']['name'][$key])) {
                $fileName = uniqid() . '_' . $_FILES['photos']['name'][$key];
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $photos[] = $fileName;
                }
            }
        }
    }

    // Process parts data
    $partData = [];
    $partIds = [];
    $partPrices = [];
    $partQuantities = [];
    if (!empty($_POST['parts'])) {
        foreach ($_POST['parts'] as $key => $partId) {
            if (!empty($partId)) {
                // Get part details from database
                $partStmt = $pdo->prepare("SELECT PartDesc, SellPrice FROM parts WHERE PartID = ?");
                $partStmt->execute([$partId]);
                $part = $partStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($part) {
                    $quantity = isset($_POST['partQuantities'][$key]) ? (int)$_POST['partQuantities'][$key] : 1;
                    if ($quantity < 1) $quantity = 1;
                    
                    $partData[] = [
                        'id' => $partId,
                        'name' => $part['PartDesc'],
                        'price' => $_POST['partPrices'][$key] ?? $part['SellPrice'],
                        'quantity' => $quantity
                    ];
                    
                    // Store part IDs, prices and quantities separately for the model
                    $partIds[] = $partId;
                    $partPrices[] = $_POST['partPrices'][$key] ?? $part['SellPrice'];
                    $partQuantities[] = $quantity;
                }
            }
        }
    }

    // Prepare job card data with the correct field names
    $jobCardData = [
        'customer' => $_POST['customer'] ?? '',
        'location' => $_POST['location'] ?? '',
        'dateCall' => $_POST['dateCall'] ?? date('Y-m-d'),
        'jobDescription' => $_POST['jobDescription'] ?? '',
        'jobReport' => $_POST['jobReport'] ?? '',
        'jobStartDate' => !empty($_POST['jobStartDate']) ? $_POST['jobStartDate'] : null,
        'jobEndDate' => !empty($_POST['jobEndDate']) ? $_POST['jobEndDate'] : null,
        'rides' => $_POST['rides'] ?? 0,
        'driveCosts' => $_POST['driveCosts'] ?? 0,
        'registration' => $licenseNr, // Use the verified license number
        'parts' => $partIds,
        'partPrices' => $partPrices,
        'partQuantities' => $partQuantities,
        'totalCosts' => $_POST['totalCosts'] ?? 0,
        'photos' => !empty($photos) ? json_encode($photos) : null
    ];

    // Create job card
    $jobCard = new JobCard($jobCardData);
    
    if ($jobCard->save()) {
        $_SESSION['message'] = "Job card saved successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        throw new Exception("Failed to create job card");
    }

    // Redirect back to main page
    header("Location: ../views/job_cards_main.php");
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../views/job_cards_main.php");
    exit();
}
?> 
