<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
  
session_start();
require_once "../models/invoice_model.php";
require_once "../config/db_connection.php";
require_once __DIR__ . "/../includes/sanitize_inputs.php";

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a custom error log for this controller
$logFile = '../logs/update_invoice_errors.log';
if (!file_exists('../logs/')) {
    mkdir('../logs/', 0777, true);
}

// Define a custom error logging function 
function logDebug($message) {
    global $logFile;
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, $timestamp . ' ' . $message . "\n", FILE_APPEND);
}

// Log the start of processing
logDebug("=== START PROCESSING UPDATE INVOICE ===");
logDebug("POST data: " . json_encode($_POST));
logDebug("FILES data: " . json_encode($_FILES));

// Log POST data for debugging
error_log("UPDATE INVOICE POST DATA: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

// Set view_only flag to bypass validation in constructor
$_POST['view_only'] = true;
$invoiceMang = new InvoiceManagement();

// Check if invoice ID is provided
$id = null;
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} elseif (isset($_POST['invoice_id'])) {
    $id = filter_var($_POST['invoice_id'], FILTER_VALIDATE_INT);
}

if ($id === false || $id === null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No invoice ID provided or invalid ID.'
    ]);
    exit;
}

// Get the invoice data
$invoice = $invoiceMang->ViewSingle($id);

if ($invoice === false) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invoice not found.'
    ]);
    exit;
}

// Store invoice data in session for edit form
$_SESSION['invoice_data'] = $invoice;

// Check if this is a form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Get database connection
        $pdo = require '../config/db_connection.php';
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get sanitized input data
        $invoiceId = filter_var($_POST['invoice_id'], FILTER_VALIDATE_INT);
        $invoiceNr = sanitize_input($_POST['invoiceNr']);
        $dateCreated = sanitize_input($_POST['dateCreated']);
        $supplierName = sanitize_input($_POST['supplier']);
        $vat = filter_var($_POST['vat'], FILTER_VALIDATE_FLOAT);
        $total = filter_var($_POST['total'], FILTER_VALIDATE_FLOAT);
        $supplierPhone = sanitize_input($_POST['supplierPhone']);
        $supplierEmail = sanitize_input($_POST['supplierEmail']);
        $supplierId = !empty($_POST['supplierID']) ? filter_var($_POST['supplierID'], FILTER_VALIDATE_INT) : null;
        
        // Validate required fields
        $requiredFields = [
            'invoiceNr' => 'Invoice number',
            'dateCreated' => 'Date created',
            'supplier' => 'Supplier name',
            'vat' => 'VAT',
            'total' => 'Total price'
        ];
        
        $errors = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "$label is required";
            }
        }
        
        // Validate that either phone or email is provided
        if (empty($supplierPhone) && empty($supplierEmail)) {
            $errors[] = "Either supplier phone or email is required";
        }
        
        // If there are validation errors, return them
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }
        
        // Handle supplier
        if (!$supplierId) {
            // Create new supplier
            $stmt = $pdo->prepare("INSERT INTO suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)");
            $stmt->execute([$supplierName, $supplierPhone, $supplierEmail]);
            $supplierId = $pdo->lastInsertId();
        } else {
            // Always update existing supplier data as name could have changed
            $stmt = $pdo->prepare("UPDATE suppliers SET Name = ?, PhoneNr = ?, Email = ? WHERE SupplierID = ?");
            $stmt->execute([$supplierName, $supplierPhone, $supplierEmail, $supplierId]);
        }
        
        // Update invoice
        $sql = "UPDATE invoices SET 
                InvoiceNr = ?, 
                DateCreated = ?, 
                Vat = ?, 
                Total = ?";
        
        $params = [$invoiceNr, $dateCreated, $vat, $total];
        
        $sql .= " WHERE InvoiceID = ?";
        $params[] = $invoiceId;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Handle parts
        $parts = json_decode($_POST['parts'], true);
        if (!empty($parts)) {
            try {
                // First, delete existing parts supply entries
                $stmt = $pdo->prepare("DELETE FROM partssupply WHERE InvoiceID = ?");
                $stmt->execute([$invoiceId]);
                
                // Process parts
                foreach ($parts as $part) {
                    // Validate part data
                    if (empty($part['partDesc']) || empty($part['piecesPurch']) || empty($part['pricePerPiece']) || empty($part['sellingPrice'])) {
                        throw new Exception("Invalid part data: Missing required fields");
                    }
                    
                    // Check if this is an existing part or a new one
                    if (!empty($part['partId']) && (substr($part['partId'], 0, 5) !== 'temp_')) {
                        $partId = filter_var($part['partId'], FILTER_VALIDATE_INT);
                        
                        // Update existing part
                        $stmt = $pdo->prepare("
                            UPDATE parts 
                            SET PartDesc = ?, 
                                PiecesPurch = ?,
                                PricePerPiece = ?, 
                                PriceBulk = ?, 
                                SellPrice = ?,
                                Stock = ?,
                                SupplierID = ?
                            WHERE PartID = ?
                        ");
                        $stmt->execute([
                            $part['partDesc'],
                            $part['piecesPurch'],
                            $part['pricePerPiece'],
                            $part['priceBulk'] ?? null,
                            $part['sellingPrice'],
                            $part['piecesPurch'], // Set stock equal to pieces purchased
                            $supplierId, // Update the supplier ID
                            $partId
                        ]);
                        
                        // Update PartSupplier relationship
                        // First delete any existing relationships for this part
                        $deleteStmt = $pdo->prepare("DELETE FROM partsupplier WHERE PartID = ?");
                        $deleteStmt->execute([$partId]);
                        
                        // Then insert the new relationship
                        $insertStmt = $pdo->prepare("INSERT INTO partsupplier (PartID, SupplierID) VALUES (?, ?)");
                        $insertStmt->execute([$partId, $supplierId]);
                    } else {
                        // Insert new part
                        $stmt = $pdo->prepare("
                            INSERT INTO parts (PartDesc, SupplierID, PiecesPurch, PricePerPiece, PriceBulk, SellPrice, DateCreated, Stock) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $part['partDesc'],
                            $supplierId,
                            $part['piecesPurch'],
                            $part['pricePerPiece'],
                            $part['priceBulk'] ?? null,
                            $part['sellingPrice'],
                            $dateCreated,
                            $part['piecesPurch'] // Set stock equal to pieces purchased
                        ]);
                        $partId = $pdo->lastInsertId();
                        
                        // Add entry to PartSupplier table for new parts
                        $stmt = $pdo->prepare("INSERT INTO partsupplier (PartID, SupplierID) VALUES (?, ?)");
                        $stmt->execute([$partId, $supplierId]);
                    }
                    
                    // Link part to invoice
                    $stmt = $pdo->prepare("INSERT INTO partssupply (InvoiceID, PartID) VALUES (?, ?)");
                    $stmt->execute([$invoiceId, $partId]);
                }
            } catch (Exception $e) {
                throw new Exception("Error processing parts: " . $e->getMessage());
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Invoice updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        
        error_log("Error updating invoice: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error updating invoice: ' . $e->getMessage()
        ]);
    }
    exit;
}

// If not a POST request, redirect to main page
header("Location: ../views/invoice_main.php");
exit;
