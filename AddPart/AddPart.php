<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and input sanitization functions
require_once 'sanitize_inputs.php';
$pdo = require_once 'db_connection.php';

$errors = [];
$sanitizedInputs = [];

// ✅ HANDLE AJAX REQUEST: Supplier Search
if (isset($_GET['supplierSearch'])) {
    $name = '%' . $_GET['supplierSearch'] . '%';

    // Fetch suppliers matching the entered name
    $sql = "SELECT SupplierID, Name, PhoneNr, Email FROM Suppliers WHERE Name LIKE :name LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results for dropdown suggestions
    if ($suppliers) {
        foreach ($suppliers as $supplier) {
            echo "<div class='supplier-option' 
                        data-name='{$supplier['Name']}' 
                        data-phone='{$supplier['PhoneNr']}' 
                        data-email='{$supplier['Email']}'>
                    {$supplier['Name']}
                  </div>";
        }
    } else {
        echo "<div class='supplier-option'>No supplier found</div>";
    }
    exit;
}

// ✅ HANDLE AJAX REQUEST: Part Description Search
if (isset($_GET['partSearch'])) {
    $partDesc = '%' . $_GET['partSearch'] . '%';

    // Fetch parts matching the entered description
    $sql = "SELECT DISTINCT PartDesc FROM Parts WHERE PartDesc LIKE :partDesc LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':partDesc', $partDesc, PDO::PARAM_STR);
    $stmt->execute();
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results for dropdown suggestions
    if ($parts) {
        foreach ($parts as $part) {
            echo "<div class='part-option' data-part='{$part['PartDesc']}'>
                    {$part['PartDesc']}
                  </div>";
        }
    } else {
        echo "<div class='part-option'>No matching part found</div>";
    }
    exit;
}

// ✅ HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sanitizedInputs = sanitizeInputs($_POST, $errors);

    // Proceed only if there are no validation errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Extract sanitized input values
            $partDesc = $sanitizedInputs['partDesc'];
            $supplier = $sanitizedInputs['supplier'];
            $piecesPurchased = $sanitizedInputs['piecesPurchased'];
            $pricePerPiece = $sanitizedInputs['pricePerPiece'];
            $priceBulk = isset($sanitizedInputs['priceBulk']) ? $sanitizedInputs['priceBulk'] : null;
            $vat = $sanitizedInputs['vat'];
            $sellingPrice = $sanitizedInputs['sellingPrice'];

            // ✅ Check if Supplier Exists
            $supplierSql = "SELECT SupplierID, PhoneNr, Email FROM Suppliers WHERE Name = :supplier LIMIT 1";
            $supplierStmt = $pdo->prepare($supplierSql);
            $supplierStmt->bindParam(':supplier', $supplier, PDO::PARAM_STR);
            $supplierStmt->execute();
            $supplierRow = $supplierStmt->fetch(PDO::FETCH_ASSOC);

            if (!$supplierRow) {
                // Insert New Supplier
                $insertSupplierSql = "INSERT INTO Suppliers (Name, PhoneNr, Email) VALUES (:supplier, :phone, :email)";
                $insertSupplierStmt = $pdo->prepare($insertSupplierSql);
                $insertSupplierStmt->bindParam(':supplier', $supplier, PDO::PARAM_STR);
                $insertSupplierStmt->bindParam(':phone', $sanitizedInputs['supplierPhone'], PDO::PARAM_STR);
                $insertSupplierStmt->bindParam(':email', $sanitizedInputs['supplierEmail'], PDO::PARAM_STR);
                $insertSupplierStmt->execute();
                $supplierID = $pdo->lastInsertId();
            } else {
                // Use existing supplier & update phone/email if needed
                $supplierID = $supplierRow['SupplierID'];

                $updateSupplierSql = "UPDATE Suppliers SET 
                        PhoneNr = COALESCE(NULLIF(:phone, ''), PhoneNr), 
                        Email = COALESCE(NULLIF(:email, ''), Email) 
                        WHERE SupplierID = :supplierID";
                $updateSupplierStmt = $pdo->prepare($updateSupplierSql);
                $updateSupplierStmt->bindParam(':phone', $sanitizedInputs['supplierPhone'], PDO::PARAM_STR);
                $updateSupplierStmt->bindParam(':email', $sanitizedInputs['supplierEmail'], PDO::PARAM_STR);
                $updateSupplierStmt->bindParam(':supplierID', $supplierID, PDO::PARAM_INT);
                $updateSupplierStmt->execute();
            }

            // ✅ Insert New Invoice
            $invoiceSql = "INSERT INTO Invoices (DateCreated, Vat, Total) VALUES (NOW(), :vat, 0)";
            $invoiceStmt = $pdo->prepare($invoiceSql);
            $invoiceStmt->bindParam(':vat', $vat, PDO::PARAM_STR);
            $invoiceStmt->execute();
            $invoiceID = $pdo->lastInsertId();

            // ✅ Insert New Part
            $partSql = "INSERT INTO Parts (PartDesc, PriceBulk, SellPrice, Stock) 
                        VALUES (:partDesc, :priceBulk, :sellingPrice, :piecesPurchased)";
            $partStmt = $pdo->prepare($partSql);
            $partStmt->bindParam(':partDesc', $partDesc, PDO::PARAM_STR);
            $partStmt->bindParam(':priceBulk', $priceBulk, PDO::PARAM_STR);
            $partStmt->bindParam(':sellingPrice', $sellingPrice, PDO::PARAM_STR);
            $partStmt->bindParam(':piecesPurchased', $piecesPurchased, PDO::PARAM_INT);
            $partStmt->execute();
            $partID = $pdo->lastInsertId();

            // ✅ Insert into PartsSupply
            $partSupplySql = "INSERT INTO PartsSupply (InvoiceID, PartID, PiecesPurch, PricePerPiece) 
                              VALUES (:invoiceID, :partID, :piecesPurchased, :pricePerPiece)";
            $partSupplyStmt = $pdo->prepare($partSupplySql);
            $partSupplyStmt->bindParam(':invoiceID', $invoiceID, PDO::PARAM_INT);
            $partSupplyStmt->bindParam(':partID', $partID, PDO::PARAM_INT);
            $partSupplyStmt->bindParam(':piecesPurchased', $piecesPurchased, PDO::PARAM_INT);
            $partSupplyStmt->bindParam(':pricePerPiece', $pricePerPiece, PDO::PARAM_STR);
            $partSupplyStmt->execute();

            // ✅ Insert into InvoiceSupply
            $invoiceSupplySql = "INSERT INTO InvoiceSupply (InvoiceID, SupplierID) VALUES (:invoiceID, :supplierID)";
            $invoiceSupplyStmt = $pdo->prepare($invoiceSupplySql);
            $invoiceSupplyStmt->bindParam(':invoiceID', $invoiceID, PDO::PARAM_INT);
            $invoiceSupplyStmt->bindParam(':supplierID', $supplierID, PDO::PARAM_INT);
            $invoiceSupplyStmt->execute();

            // Commit transaction
            $pdo->commit();

            // Redirect to index with success message
            header("Location: index.php?success=1");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<h1>Error: Unable to Add Part</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }
}
?>





