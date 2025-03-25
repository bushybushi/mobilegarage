<?php
session_start();
require_once '../models/invoice_model.php';

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON content type header right at the start
header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['invoiceId']) || !isset($input['deleteParts'])) {
        throw new Exception('Missing required parameters');
    }

    $invoiceId = (int)$input['invoiceId'];
    if ($invoiceId <= 0) {
        throw new Exception('Invalid invoice ID');
    }

    // Create instance of InvoiceManagement
    $invoiceMang = new InvoiceManagement();
    
    // Delete the invoice and optionally its parts
    $result = $invoiceMang->Delete($invoiceId, $input['deleteParts'] === true);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => "Invoice deleted successfully."
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Failed to delete invoice. Please try again."
        ]);
    }

} catch (Exception $e) {
    error_log("Error deleting invoice: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
exit;