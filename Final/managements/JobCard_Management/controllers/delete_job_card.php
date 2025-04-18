<?php
require_once '../config/db_connection.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$jobId = $data['jobId'] ?? null;
$partsToReturn = $data['partsToReturn'] ?? [];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Return parts to stock if requested
    foreach ($partsToReturn as $part) {
        // Update stock quantity and decrease sold count
        $updateStockSql = "UPDATE parts SET Stock = Stock + ?, Sold = Sold - ? WHERE PartID = ?";
        $updateStockStmt = $pdo->prepare($updateStockSql);
        $updateStockStmt->execute([$part['quantity'], $part['quantity'], $part['partId']]);
    }

    // Get invoice IDs associated with this job
    $invoiceSql = "SELECT InvoiceID FROM invoicejob WHERE JobID = ?";
    $invoiceStmt = $pdo->prepare($invoiceSql);
    $invoiceStmt->execute([$jobId]);
    $invoiceIds = $invoiceStmt->fetchAll(PDO::FETCH_COLUMN);

    // Delete from InvoiceJob first
    $deleteInvoiceJobSql = "DELETE FROM invoicejob WHERE JobID = ?";
    $deleteInvoiceJobStmt = $pdo->prepare($deleteInvoiceJobSql);
    $deleteInvoiceJobStmt->execute([$jobId]);

    // Delete invoices that were linked to this job
    if (!empty($invoiceIds)) {
        $invoicePlaceholders = implode(',', array_fill(0, count($invoiceIds), '?'));
        
        // Delete from PartsSupply first if it exists
        $deletePartsSupplySql = "DELETE FROM partssupply WHERE InvoiceID IN ($invoicePlaceholders)";
        $deletePartsSupplyStmt = $pdo->prepare($deletePartsSupplySql);
        $deletePartsSupplyStmt->execute($invoiceIds);
        
        // Delete the invoices
        $deleteInvoicesSql = "DELETE FROM invoices WHERE InvoiceID IN ($invoicePlaceholders)";
        $deleteInvoicesStmt = $pdo->prepare($deleteInvoicesSql);
        $deleteInvoicesStmt->execute($invoiceIds);
    }

    // Delete job card parts
    $deletePartsSql = "DELETE FROM jobcardparts WHERE JobID = ?";
    $deletePartsStmt = $pdo->prepare($deletePartsSql);
    $deletePartsStmt->execute([$jobId]);

    // Delete job car association
    $deleteJobCarSql = "DELETE FROM jobcar WHERE JobID = ?";
    $deleteJobCarStmt = $pdo->prepare($deleteJobCarSql);
    $deleteJobCarStmt->execute([$jobId]);

    // Delete job card
    $deleteJobSql = "DELETE FROM jobcards WHERE JobID = ?";
    $deleteJobStmt = $pdo->prepare($deleteJobSql);
    $deleteJobStmt->execute([$jobId]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Job card deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting job card: ' . $e->getMessage()
    ]);
} 