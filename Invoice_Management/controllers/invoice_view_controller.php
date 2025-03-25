<?php
// Start a session to maintain user state between pages
session_start();
require_once "../models/invoice_model.php";

// Enable detailed error reporting for development environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set a flag to indicate we're only viewing (not editing) the invoice
// This affects how the InvoiceManagement class behaves
$_POST['view_only'] = true;
$invoiceMang = new InvoiceManagement();

// Check if a specific invoice ID was requested in the URL
if (isset($_GET['id'])) {
    // Make sure the ID is a valid integer
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    // If ID is invalid, show error and redirect to main page
    if ($id === false) {
        $_SESSION['message'] = "Invalid invoice ID.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/invoice_main.php");
        exit;
    }

    // Try to get the invoice details from the database
    $invoice = $invoiceMang->ViewSingle($id);
    
    // If invoice not found, show error and redirect to main page
    if ($invoice === false) {
        $_SESSION['message'] = "Invoice not found.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/invoice_main.php");
        exit;
    }
    
    // Store invoice data in session for the view to use
    $_SESSION['invoice_data'] = $invoice;
    header("Location: ../views/invoice_view.php");
    exit;
} else {
    // If no invoice ID provided, go back to main page
    header("Location: ../views/invoice_main.php");
    exit;
}