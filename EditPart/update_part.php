<?php

require_once '../sanitize_inputs.php';
require_once '../flatten.php';

$pdo = require '../db_connection.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;

try {
    // Fetch old part data
    $partSql = 'SELECT PartDesc, NAME, Email, PhoneNr, PiecesPurch, PricePerPiece, PriceBulk, Vat, SellPrice, Sold, Stock
                FROM parts JOIN partssupply USING (PartID)
                JOIN invoices USING (InvoiceID)
                JOIN invoicesupply USING (InvoiceID)
                JOIN suppliers USING (SupplierID) 
                WHERE PartID = :id';
    $partStmt = $pdo->prepare($partSql);
    $partStmt->execute([':id' => $id]);
    $old_part = $partStmt->fetch(PDO::FETCH_ASSOC);

    if (!$old_part) {
        echo "<h1>Error: Part Not Found</h1>";
        exit;
    }

    // Sanitize inputs
    $sanitizedInputs = sanitizeInputs($_POST);
    extract($sanitizedInputs);

    // Start transaction
    $pdo->beginTransaction();

    // Update parts table
    $partSql = "UPDATE parts 
                SET PartDesc = :PartDesc, PriceBulk = :PriceBulk, SellPrice = :SellPrice, Sold = :Sold, Stock = :Stock
                WHERE PartID = :id";
    $pdo->prepare($partSql)->execute([
        ':PartDesc' => $partdesc,
        ':PriceBulk' => $pricebulk,
        ':SellPrice' => $sellprice,
        ':Sold' => $sold,
        ':Stock' => $stock,
        ':id' => $id
    ]);

    // Update partssupply table
    $supplySql = "UPDATE partssupply 
                  SET PiecesPurch = :PiecesPurch, PricePerPiece = :PricePerPiece
                  WHERE PartID = :id";
    $pdo->prepare($supplySql)->execute([
        ':PiecesPurch' => $piecespurch,
        ':PricePerPiece' => $priceperpiece,
        ':id' => $id
    ]);

    // Update invoices table
    $invoiceSql = "UPDATE invoices 
                   SET Vat = :Vat
                   WHERE InvoiceID = (SELECT InvoiceID FROM partssupply WHERE PartID = :id)";
    $pdo->prepare($invoiceSql)->execute([
        ':Vat' => $vat,
        ':id' => $id
    ]);

    // Update suppliers table
    $supplierSql = "UPDATE suppliers
                    SET NAME = :Supplier, Email = :Email, PhoneNr = :PhoneNr
                    WHERE SupplierID = (SELECT SupplierID FROM invoicesupply NATURAL JOIN partssupply WHERE PartID = :id)";
    $pdo->prepare($supplierSql)->execute([
        ':Supplier' => $supplier,
        ':Email' => $suppemail,
        ':PhoneNr' => $suppphone,
        ':id' => $id
    ]);

    $pdo->commit();
    echo "<h1>Part Updated Successfully!</h1>";
    echo "<p><a href='/'>Go Back</a></p>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h1>Error: Unable to Update Part</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
