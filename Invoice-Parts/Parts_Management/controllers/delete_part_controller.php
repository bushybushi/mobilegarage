<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
// Start a new session to maintain user state across pages
session_start();
require_once "../includes/sanitize_inputs.php";
require_once "../models/parts_model.php";

// Tell the browser we'll be sending back JSON data
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the incoming request
error_log("Delete part request received: " . json_encode($_POST));

try {
    // Validate part ID
    if (!isset($_POST['partId'])) {
        throw new Exception('Part ID is required');
    }
    
    if (!is_numeric($_POST['partId'])) {
        throw new Exception('Invalid part ID format');
    }
    
    $partId = (int)$_POST['partId'];
    
    // Create instance of PartsManagement
    $partsMang = new PartsManagement();
    
    // Attempt to delete the part
    $result = $partsMang->Delete($partId);
    
    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        echo json_encode([
            'success' => true,
            'message' => $result['message']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in delete_part_controller: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Set appropriate HTTP status code based on error type
    if (strpos($e->getMessage(), 'Cannot delete part: It is associated with Invoice') !== false ||
        strpos($e->getMessage(), 'Cannot delete part: It is associated with Supplier') !== false) {
        http_response_code(409); // Conflict
    } else {
        http_response_code(400); // Bad Request
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 