<?php
// delete_part.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Set response as JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partID'])) {
    $partID = intval($_POST['partID']);

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Step 1: Find the Invoice ID linked to the part
        $invoiceQuery = "SELECT InvoiceID FROM partssupply WHERE PartID = :partID";
        $invoiceStmt = $pdo->prepare($invoiceQuery);
        $invoiceStmt->bindParam(':partID', $partID, PDO::PARAM_INT);
        $invoiceStmt->execute();
        $invoiceIDs = $invoiceStmt->fetchAll(PDO::FETCH_COLUMN);

        // Step 2: Find the Supplier ID correctly
        $supplierQuery = "SELECT DISTINCT s.SupplierID FROM suppliers s
                          INNER JOIN invoicesupply i ON s.SupplierID = i.SupplierID
                          INNER JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
                          WHERE ps.PartID = :partID";
        $supplierStmt = $pdo->prepare($supplierQuery);
        $supplierStmt->bindParam(':partID', $partID, PDO::PARAM_INT);
        $supplierStmt->execute();
        $supplierIDs = $supplierStmt->fetchAll(PDO::FETCH_COLUMN);

        // Step 3: Delete from `partssupply`
        $stmt = $pdo->prepare("DELETE FROM partssupply WHERE PartID = :partID");
        $stmt->bindParam(':partID', $partID, PDO::PARAM_INT);
        $stmt->execute();

        // Step 4: Delete from `invoicesupply` and `invoices` if no more linked parts exist
        foreach ($invoiceIDs as $invoiceID) {
            $checkInvoice = "SELECT COUNT(*) FROM partssupply WHERE InvoiceID = :invoiceID";
            $checkStmt = $pdo->prepare($checkInvoice);
            $checkStmt->bindParam(':invoiceID', $invoiceID, PDO::PARAM_INT);
            $checkStmt->execute();
            $invoiceCount = $checkStmt->fetchColumn();

            if ($invoiceCount == 0) {
                $stmt = $pdo->prepare("DELETE FROM invoicesupply WHERE InvoiceID = :invoiceID");
                $stmt->bindParam(':invoiceID', $invoiceID, PDO::PARAM_INT);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM invoices WHERE InvoiceID = :invoiceID");
                $stmt->bindParam(':invoiceID', $invoiceID, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // Step 5: Delete from `parts`
        $stmt = $pdo->prepare("DELETE FROM parts WHERE PartID = :partID");
        $stmt->bindParam(':partID', $partID, PDO::PARAM_INT);
        $stmt->execute();

        // Step 6: Delete supplier if they have no more linked parts
        foreach ($supplierIDs as $supplierID) {
            $checkSupplier = "SELECT COUNT(*) FROM invoicesupply WHERE SupplierID = :supplierID";
            $checkStmt = $pdo->prepare($checkSupplier);
            $checkStmt->bindParam(':supplierID', $supplierID, PDO::PARAM_INT);
            $checkStmt->execute();
            $supplierCount = $checkStmt->fetchColumn();

            if ($supplierCount == 0) {
                // No more parts linked to this supplier, delete supplier
                $stmt = $pdo->prepare("DELETE FROM suppliers WHERE SupplierID = :supplierID");
                $stmt->bindParam(':supplierID', $supplierID, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // Commit the transaction
        $pdo->commit();

        // Return a success response
        echo json_encode(["success" => true, "message" => "Part and all related records deleted successfully."]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error deleting part: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
?>
