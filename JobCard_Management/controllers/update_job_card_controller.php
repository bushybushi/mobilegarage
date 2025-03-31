<?php
session_start();
require_once "../models/job_card_model.php";

try {
    // Handle photo uploads
    $uploadDir = '../uploads/job_photos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $existingPhotos = isset($_POST['existing_photos']) ? $_POST['existing_photos'] : [];
    $removedPhotos = isset($_POST['removed_photos']) ? json_decode($_POST['removed_photos'], true) : [];
    $newPhotos = [];

    // Handle new photo uploads
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['photos']['name'][$key];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Generate unique filename
                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $newPhotos[] = $newFileName;
                }
            }
        }
    }

    // Remove deleted photos
    foreach ($removedPhotos as $photo) {
        $photoPath = $uploadDir . $photo;
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    // Combine existing and new photos
    $allPhotos = array_merge($existingPhotos, $newPhotos);
    $photosJson = !empty($allPhotos) ? json_encode($allPhotos) : null;

    // Process parts data
    $partIds = [];
    $partPrices = [];
    $partQuantities = [];
    if (!empty($_POST['parts'])) {
        $pdo = require '../config/db_connection.php';
        foreach ($_POST['parts'] as $key => $partId) {
            if (!empty($partId)) {
                // Get part details from database
                $partStmt = $pdo->prepare("SELECT PartDesc, SellPrice FROM parts WHERE PartID = ?");
                $partStmt->execute([$partId]);
                $part = $partStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($part) {
                    $quantity = isset($_POST['partQuantities'][$key]) ? (int)$_POST['partQuantities'][$key] : 1;
                    if ($quantity < 1) $quantity = 1;
                    
                    $partIds[] = $partId;
                    $partPrices[] = $_POST['partPrices'][$key] ?? $part['SellPrice'];
                    $partQuantities[] = $quantity;
                }
            }
        }
    }

    // Update job card data
    $_POST['photos'] = $photosJson;
    $_POST['parts'] = $partIds;
    $_POST['partPrices'] = $partPrices;
    $_POST['partQuantities'] = $partQuantities;
    
    // Make sure additionalCost is included in the update
    if (!isset($_POST['additionalCost'])) {
        $_POST['additionalCost'] = $_POST['additionalCosts'] ?? 0;
    }
    
    if (JobCard::update($_POST['id'], $_POST)) {
        $_SESSION['message'] = "Job card updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        throw new Exception("Failed to update job card");
    }

    header("Location: ../views/job_cards_main.php");
    exit;
} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../views/edit_job_card.php?id=" . $_POST['id']);
    exit;
}
?> 
