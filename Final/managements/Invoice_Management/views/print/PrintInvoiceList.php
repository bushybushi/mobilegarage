<?php

require_once '../../config/db_connection.php';

// SQL query to fetch all customers with their related information
$sql = "SELECT invoices.InvoiceID, invoices.InvoiceNr, invoices.DateCreated, invoices.Vat, invoices.Total, 
        suppliers.Name as SupplierName, suppliers.PhoneNr as SupplierPhone, suppliers.Email as SupplierEmail
        FROM invoices 
        LEFT JOIN partssupply ON invoices.InvoiceID = partssupply.InvoiceID
        LEFT JOIN parts ON partssupply.PartID = parts.PartID
        LEFT JOIN suppliers ON parts.SupplierID = suppliers.SupplierID";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice List</title>
    <style>
        @media print {
            body { 
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 2px solid #ddd;
            }
            .logo {
                width: 200px;
                height: auto;
            }
            .header-text {
                text-align: right;
            }
            table { 
                width: 100%; 
                border-collapse: collapse;
                page-break-inside: auto;
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left;
            }
            th { 
                background-color: #f2f2f2;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr { 
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Invoice List</h1>
            <p>Total Invoices: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
            <th>Invoice Nr</th>
                <th>Date Created</th>
                <th>Supplier</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Total</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['InvoiceNr']); ?></td>
                    <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierPhone']); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierEmail']); ?></td>
                    <td><?php echo htmlspecialchars($row['Total']); ?></td>
                    <td><?php echo htmlspecialchars($row['Vat']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 