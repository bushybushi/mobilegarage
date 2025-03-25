<?php
    session_start();
    
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Log the incoming data
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("Received FILES data: " . print_r($_FILES, true));
    
    require_once "../models/invoice_model.php";
    
    try {
        // Create InvoiceManagement instance
        try {
            $invoiceMang = new InvoiceManagement();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed. Please try again later.");
        }
        
        // Validate required fields
        $requiredFields = ['invoiceNr', 'dateCreated', 'vat', 'total'];
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
                $pdo = require '../config/db_connection.php';
                $stmt = $pdo->prepare("SELECT SupplierID FROM Suppliers WHERE Name = ?");
                $stmt->execute([$_POST['supplier']]);
                $existingSupplier = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingSupplier) {
                    // Validate that either phone or email is provided for new supplier
                    if (empty($_POST['supplierPhone']) && empty($_POST['supplierEmail'])) {
                        throw new Exception("Either phone or email is required for new suppliers");
                    }

                    // Validate email if provided
                    if (!empty($_POST['supplierEmail'])) {
                        if (!InvoiceManagement::validateEmail($_POST['supplierEmail'])) {
                            throw new Exception("Invalid email format. Email must contain @ and a valid domain (e.g., example@domain.com)");
                        }
                    }

                    // Create new supplier
                    $stmt = $pdo->prepare("INSERT INTO Suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $_POST['supplier'],
                        $_POST['supplierPhone'] ?? null,
                        $_POST['supplierEmail'] ?? null
                    ]);
                    $supplierId = $pdo->lastInsertId();
                } else {
                    $supplierId = $existingSupplier['SupplierID'];
                }
            } catch (PDOException $e) {
                throw new Exception("Error processing supplier information: " . $e->getMessage());
            }
        } else {
            throw new Exception("Supplier name is required");
        }

        // Handle file upload if present
        $pdf = null;
        if (isset($_FILES['invoicePhoto']) && $_FILES['invoicePhoto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['invoicePhoto'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
            }
            
            // Validate file size (5MB limit)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size must be less than 5MB");
            }
            
            try {
                // Read file content
                $pdf = file_get_contents($file['tmp_name']);
                if ($pdf === false) {
                    throw new Exception("Failed to read uploaded file");
                }
            } catch (Exception $e) {
                throw new Exception("Error processing uploaded file: " . $e->getMessage());
            }
        }

        // Prepare invoice data
        $invoiceData = [
            'invoiceNr' => $_POST['invoiceNr'],
            'dateCreated' => $_POST['dateCreated'],
            'supplierID' => $supplierId,
            'vat' => $_POST['vat'],
            'total' => $_POST['total'],
            'pdf' => $pdf,
            'parts' => []
        ];

        // Process parts data if present
        if (isset($_POST['parts']) && is_array($_POST['parts'])) {
            foreach ($_POST['parts'] as $part) {
                if (!empty($part['partDesc'])) {
                    $invoiceData['parts'][] = [
                        'partDesc' => $part['partDesc'],
                        'piecesPurch' => $part['piecesPurch'],
                        'pricePerPiece' => $part['pricePerPiece'],
                        'priceBulk' => $part['priceBulk'] ?? null,
                        'sellingPrice' => $part['sellingPrice']
                    ];
                }
            }
        }

        // Start transaction
        $pdo->beginTransaction();

        // Save invoice
        $stmt = $pdo->prepare("INSERT INTO Invoices (InvoiceNr, DateCreated, Vat, Total, PDF) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['invoiceNr'], $_POST['dateCreated'], $_POST['vat'], $_POST['total'], $pdf]);
        $invoiceId = $pdo->lastInsertId();

        // Get supplier ID
        $supplierId = $_POST['supplierID'];

        // Process parts
        $parts = json_decode($_POST['parts'], true);
        foreach ($parts as $part) {
            // Insert part with stock equal to pieces purchased
            $stmt = $pdo->prepare("INSERT INTO Parts (SupplierID, PartDesc, PriceBulk, SellPrice, PiecesPurch, PricePerPiece, DateCreated, Stock) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)");
            $stmt->execute([
                $supplierId,
                $part['partDesc'],
                $part['priceBulk'],
                $part['sellingPrice'],
                $part['piecesPurch'],
                $part['pricePerPiece'],
                $part['piecesPurch'] // Set Stock equal to PiecesPurch
            ]);
            $partId = $pdo->lastInsertId();

            // Link part to supplier in PartSupplier table
            $stmt = $pdo->prepare("INSERT INTO PartSupplier (PartID, SupplierID) VALUES (?, ?)");
            $stmt->execute([$partId, $supplierId]);

            // Link part to invoice in PartsSupply table
            $stmt = $pdo->prepare("INSERT INTO PartsSupply (InvoiceID, PartID) VALUES (?, ?)");
            $stmt->execute([$invoiceId, $partId]);
        }

        // Commit transaction
        $pdo->commit();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Invoice added successfully']);
        exit;

    } catch (PDOException $e) {
        // Rollback transaction on error
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error in add_invoice_controller.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error in add_invoice_controller.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
?>
