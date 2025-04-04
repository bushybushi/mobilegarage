<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
// Start a session to maintain user state between pages
session_start();
require_once "../models/parts_model.php";

// Enable detailed error reporting for development environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set a flag to indicate we're only viewing (not editing) the parts
// This affects how the PartsManagement class behaves
$_POST['view_only'] = true;
$partsMang = new PartsManagement();

// Check if a specific parts ID was requested in the URL
if (isset($_GET['id'])) {
    // Make sure the ID is a valid integer
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    // If ID is invalid, show error and redirect to main page
    if ($id === false) {
        $_SESSION['message'] = "Invalid parts ID.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/parts_main.php");
        exit;
    }

    // Try to get the parts details from the database
    $parts = $partsMang->ViewSingle($id);
    
    // If parts not found, show error and redirect to main page
    if ($parts === false) {
        $_SESSION['message'] = "Parts not found.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/parts_main.php");
        exit;
    }
    
    // Store parts data in session for the view to use
    $_SESSION['parts_data'] = $parts;
    header("Location: ../views/parts_view.php");
    exit;
} else {
    // If no parts ID provided, go back to main page
    header("Location: ../views/parts_main.php");
    exit;
}