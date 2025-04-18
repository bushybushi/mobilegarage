<?php
session_start();
require_once "../models/job_card_model.php";

try {
    if (!isset($_POST['id'])) {
        throw new Exception("Job card ID not provided");
    }

    if (JobCard::delete($_POST['id'])) {
        $_SESSION['message'] = "Job card deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        throw new Exception("Failed to delete job card");
    }

    header("Location: ../views/job_cards_main.php");
    exit;
} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../views/job_cards_main.php");
    exit;
}
?>