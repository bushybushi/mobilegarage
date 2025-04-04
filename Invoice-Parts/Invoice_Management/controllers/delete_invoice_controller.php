<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

// Start a session to maintain user state between pages
session_start();
require_once '../models/invoice_model.php';

// Enable detailed error reporting for development environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tell the browser we'll be sending back JSON data
header('Content-Type: application/json');

try {
    // Get and parse the JSON data sent in the request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Make sure we have both required parameters: invoiceId and deleteParts flag
    if (!isset($input['invoiceId']) || !isset($input['deleteParts'])) {
        throw new Exception('Missing required parameters');
    }

    // Convert invoice ID to integer and validate it's positive
    $invoiceId = (int)$input['invoiceId'];
    if ($invoiceId <= 0) {
        throw new Exception('Invalid invoice ID');
    }

    // Create an instance of our invoice management class
    $invoiceMang = new InvoiceManagement();
    
    // Try to delete the invoice and its parts (if requested)
    $result = $invoiceMang->Delete($invoiceId, $input['deleteParts'] === true);
    
    // Send appropriate response based on deletion result
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
    // Log any errors that occur during deletion
    error_log("Error deleting invoice: " . $e->getMessage());
    
    // Send error response to client with HTTP 400 status
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
exit;