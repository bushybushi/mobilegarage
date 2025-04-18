<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
    session_start();
    
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Log the incoming data
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("Received FILES data: " . print_r($_FILES, true));
    
    require_once "../models/parts_model.php";
    
    try {
        // Create PartsManagement instance
        try {
            $partsMang = new PartsManagement();
            // Start transaction at the beginning of database operations
            $pdo = require '../config/db_connection.php';
            $pdo->beginTransaction();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed. Please try again later.");
        }
        
        // Validate required fields
        $requiredFields = ['dateCreated'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Handle supplier creation/validation
        $supplierId = null;
        if (isset($_POST['supplier']) && !empty($_POST['supplier'])) {
            try {
                // Check if supplier exists
                $stmt = $pdo->prepare("SELECT SupplierID FROM suppliers WHERE Name = ?");
                $stmt->execute([$_POST['supplier']]);
                $supplierId = $stmt->fetchColumn();

                if (!$supplierId) {
                    // Validate required fields for new supplier
                    if (empty($_POST['supplierPhone']) && empty($_POST['supplierEmail'])) {
                        throw new Exception("Either phone or email is required for new suppliers");
                    }

                    // Create new supplier
                    $stmt = $pdo->prepare("INSERT INTO suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $_POST['supplier'],
                        $_POST['supplierPhone'] ?? null,
                        $_POST['supplierEmail'] ?? null
                    ]);
                    $supplierId = $pdo->lastInsertId();
                }
            } catch (PDOException $e) {
                throw new Exception("Error processing supplier information: " . $e->getMessage());
            }
        } else {
            throw new Exception("Supplier name is required");
        }

        // Process parts
        if (!isset($_POST['parts'])) {
            error_log("Parts data missing. POST data: " . print_r($_POST, true));
            throw new Exception("No parts data provided");
        }

        $parts = json_decode($_POST['parts'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            error_log("Raw parts data: " . $_POST['parts']);
            throw new Exception("Invalid parts data format");
        }

        if (!is_array($parts) || empty($parts)) {
            error_log("Parts array is empty or invalid");
            throw new Exception("No parts data provided");
        }

        error_log("Decoded parts data: " . print_r($parts, true));

        foreach ($parts as $part) {
            if (empty($part['partDesc']) || empty($part['piecesPurch']) || empty($part['pricePerPiece']) || !isset($part['vat'])) {
                error_log("Missing required part information. Part data: " . print_r($part, true));
                throw new Exception("Missing required part information");
            }

            // Insert part with stock equal to pieces purchased
            $stmt = $pdo->prepare("INSERT INTO parts (SupplierID, PartDesc, PriceBulk, SellPrice, PiecesPurch, PricePerPiece, Vat, DateCreated, Stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $supplierId,
                $part['partDesc'],
                $part['priceBulk'] ?? null,
                $part['sellingPrice'] ?? null,
                $part['piecesPurch'],
                $part['pricePerPiece'],
                $part['vat'],
                $_POST['dateCreated'],
                $part['piecesPurch']
            ]);
            $partId = $pdo->lastInsertId();

            // Link part to supplier in partsupplier table
            $stmt = $pdo->prepare("INSERT INTO partsupplier (PartID, SupplierID) VALUES (?, ?)");
            $stmt->execute([$partId, $supplierId]);
        }

        // Commit transaction
        $pdo->commit();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Part added successfully']);
        exit;

    } catch (PDOException $e) {
        // Only rollback if we have an active transaction
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error in add_parts_controller.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
        exit;
    } catch (Exception $e) {
        // Only rollback if we have an active transaction
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error in add_parts_controller.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
?>
