<?php
// list_parts.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Query to fetch parts details
$sql = "SELECT p.PartID, p.PartDesc, p.SellPrice, p.Sold, p.Stock, 
               ps.PiecesPurch, ps.PricePerPiece, s.Name AS Supplier
        FROM parts p
        LEFT JOIN partssupply ps ON p.PartID = ps.PartID
        LEFT JOIN invoicesupply i ON ps.InvoiceID = i.InvoiceID  -- Corrected supplier link
        LEFT JOIN suppliers s ON i.SupplierID = s.SupplierID";   
$stmt = $pdo->prepare($sql);
$stmt->execute();
$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parts List</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <h1 class="title">Parts List</h1>
    <?php if(count($parts) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Part Description</th>
                    <th>Supplier</th>
                    <th>Sell Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($parts as $part): ?>
                    <tr>
                        <td>
                            <a href="edit_part.php?partID=<?php echo $part['PartID']; ?>">
                                <?php echo htmlspecialchars($part['PartDesc']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($part['Supplier']); ?></td>
                        <td><?php echo htmlspecialchars($part['SellPrice']); ?></td>
                        <td><?php echo htmlspecialchars($part['Stock']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-parts">No parts found.</p>
    <?php endif; ?>
</body>
</html>
