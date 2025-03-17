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
            $fileName = uniqid() . '_' . $_FILES['photos']['name'][$key];
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tmp_name, $targetPath)) {
                $photos[] = $fileName;
            }
        }
    }

    // Get existing photos
    $existingJobCard = JobCard::getById($_POST['id']);
    if ($existingJobCard && !empty($existingJobCard['Photo'])) {
        $existingPhotos = json_decode($existingJobCard['Photo'], true);
        if (is_array($existingPhotos)) {
            $photos = array_merge($existingPhotos, $photos);
        }
    }

    // Update job card data
    $_POST['photos'] = !empty($photos) ? json_encode($photos) : null;
    
    if (JobCard::update($_POST['id'], $_POST)) {
        $_SESSION['message'] = "Job card updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        throw new Exception("Failed to update job card");
    }

    header("Location: ../views/job_card_view.php?id=" . $_POST['id']);
    exit;
} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../views/edit_job_card.php?id=" . $_POST['id']);
    exit;
}
?> 