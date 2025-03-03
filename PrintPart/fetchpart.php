<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

// Check if PartID is provided in the URL
if (!isset($_GET['PartID'])) {
    die(json_encode(["error" => "PartID is not provided"]));
}

$part_id = intval($_GET['PartID']); // Get part ID from URL and convert to integer

if ($part_id <= 0) {
    die(json_encode(["error" => "Invalid PartID"]));
}

// Fetch Part Details including Supplier and PartsSupply
$sql = "SELECT 
        p.PartID AS id, 
        p.PartDesc AS description, 
        s.Name AS supplier, 
        ps.PiecesPurch AS pieces_purchased, 
        ps.PricePerPiece AS price_per_piece, 
        p.Stock AS stock_quantity, 
        i.Vat AS vat, 
        p.SellPrice AS selling_price
        FROM Parts p
        LEFT JOIN PartsSupply ps ON p.PartID = ps.PartID
        LEFT JOIN Invoices i ON ps.InvoiceID = i.InvoiceID
        LEFT JOIN InvoiceSupply isupply ON i.InvoiceID = isupply.InvoiceID
        LEFT JOIN Suppliers s ON isupply.SupplierID = s.SupplierID
        WHERE p.PartID = ?";
$stmt = $pdo->prepare($sql);

if (!$stmt) {
    die(json_encode(["error" => "Failed to prepare statement: " . $pdo->errorInfo()[2]]));
}

$stmt->execute([$part_id]);
$part_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$part_data) { // Check if part exists
    die(json_encode(["error" => "No part found for PartID = $part_id"]));
}

header('Content-Type: application/json');
echo json_encode($part_data, JSON_PRETTY_PRINT);
?>