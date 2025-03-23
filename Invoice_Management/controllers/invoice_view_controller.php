<?php
session_start();
require_once "../models/invoice_model.php";

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set view_only flag to bypass validation in constructor
$_POST['view_only'] = true;
$invoiceMang = new InvoiceManagement();

// Check if specific invoice ID is requested
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        $_SESSION['message'] = "Invalid invoice ID.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/invoice_main.php");
        exit;
    }

    // Get the invoice
    $invoice = $invoiceMang->ViewSingle($id);
    
    if ($invoice === false) {
        $_SESSION['message'] = "Invoice not found.";
        $_SESSION['message_type'] = "error";
        header("Location: ../views/invoice_main.php");
        exit;
    }
    
    // Store invoice data in session for view
    $_SESSION['invoice_data'] = $invoice;
    header("Location: ../views/invoice_view.php");
    exit;
} else {
    // If no ID is provided, redirect to main page
    header("Location: ../views/invoice_main.php");
    exit;
}