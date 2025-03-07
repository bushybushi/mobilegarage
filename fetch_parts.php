<?php
require 'db_connection.php';

$query = "
    SELECT p.PartDesc, SUM(p.Stock) AS TotalStock,
           GROUP_CONCAT(DISTINCT s.Name ORDER BY s.Name ASC SEPARATOR ', ') AS Suppliers,
           GROUP_CONCAT(DISTINCT COALESCE(s.PhoneNr, s.Email) ORDER BY s.Name ASC SEPARATOR ', ') AS Contacts
    FROM parts p
    LEFT JOIN partssupply ps ON p.PartID = ps.PartID
    LEFT JOIN invoicesupply i ON ps.InvoiceID = i.InvoiceID
    LEFT JOIN suppliers s ON i.SupplierID = s.SupplierID
    GROUP BY p.PartDesc
    ORDER BY p.PartDesc
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$parts = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $parts[] = [
        'PartDesc' => $row['PartDesc'],
        'TotalStock' => (int) $row['TotalStock'], // Convert stock to integer
        'Suppliers' => $row['Suppliers'] ?: 'No Supplier',
        'Contacts' => $row['Contacts'] ?: 'No Contact'
    ];
}

// Debug Output
header('Content-Type: application/json');
echo json_encode($parts, JSON_PRETTY_PRINT);
?>
