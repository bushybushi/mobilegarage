<?php
session_start();
require_once "../models/job_card_model.php";

// Set response type to JSON
header('Content-Type: application/json');

try {
    // Make sure we have a valid job card ID to update
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid Job Card ID');
    }
    $jobId = (int)$_POST['id'];

    // Handle photo uploads
    $photos = [];
    
    // Process existing photos
    if (isset($_POST['existing_photos'])) {
        if (is_array($_POST['existing_photos'])) {
            $photos = $_POST['existing_photos'];
        } else if (is_string($_POST['existing_photos'])) {
            $photos = json_decode($_POST['existing_photos'], true) ?: [];
        }
    }

    // Process new photo uploads
    if (!empty($_FILES['photos']['name'][0])) {
        $uploadDir = '../uploads/job_photos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['photos']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $photos[] = $fileName;
                }
            }
        }
    }

    // Process removed photos
    if (isset($_POST['removed_photos'])) {
        $removedPhotos = json_decode($_POST['removed_photos'], true);
        if (is_array($removedPhotos)) {
            foreach ($removedPhotos as $photo) {
                $photoPath = '../uploads/job_photos/' . $photo;
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
                // Remove from photos array if it exists
                $key = array_search($photo, $photos);
                if ($key !== false) {
                    unset($photos[$key]);
                }
            }
            // Reindex array after removing elements
            $photos = array_values($photos);
        }
    }

    // Update the photos in the POST data
    $_POST['photos'] = !empty($photos) ? json_encode($photos) : null;

    // Update job card
    if (JobCard::update($jobId, $_POST)) {
        // Success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Job card updated successfully',
            'jobId' => $jobId
        ]);
    } else {
        throw new Exception('Failed to update job card');
    }
} catch (Exception $e) {
    // Error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;
?> 