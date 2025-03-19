<?php
session_start();
require_once "../models/job_card_model.php";

try {
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

    // Get existing photos
    $existingPhotos = [];
    if (!empty($_POST['existing_photos']) && is_array($_POST['existing_photos'])) {
        $existingPhotos = $_POST['existing_photos'];
    } else {
        $existingJobCard = JobCard::getById($_POST['id']);
        if ($existingJobCard && !empty($existingJobCard['Photo'])) {
            $existingPhotos = json_decode($existingJobCard['Photo'], true) ?: [];
        }
    }
    
    // Handle removed photos
    if (!empty($_POST['removed_photos'])) {
        $removedPhotos = json_decode($_POST['removed_photos'], true) ?: [];
        
        // Remove from existing photos
        $existingPhotos = array_filter($existingPhotos, function($photo) use ($removedPhotos) {
            return !in_array($photo, $removedPhotos);
        });
        
        // Delete files from server
        $uploadDir = '../uploads/job_photos/';
        foreach ($removedPhotos as $photo) {
            $filePath = $uploadDir . $photo;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    // Combine existing and new photos
    $photos = array_merge($existingPhotos, $photos);

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
    $_POST['photos'] = !empty($photos) ? json_encode($photos) : null;
    $_POST['parts'] = $partIds;
    $_POST['partPrices'] = $partPrices;
    $_POST['partQuantities'] = $partQuantities;
    
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
