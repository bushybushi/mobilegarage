<?php
// Get database connection to fetch invoice data
require_once '../config/db_connection.php';

// Get the list of selected invoice IDs from the URL and convert to array
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// If no invoices were selected, stop processing
if (empty($selectedIds)) {
    die('No invoices selected');
}

// Create SQL placeholders for the selected IDs (?,?,? etc)
$placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';

// SQL query to fetch only the selected invoices with their supplier information
// Uses LEFT JOIN to include invoices even if they don't have parts or suppliers
$sql = "SELECT DISTINCT i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Total, i.Vat,
        s.Name as SupplierName
        FROM invoices i
        LEFT JOIN PartsSupply ps ON i.InvoiceID = ps.InvoiceID
        LEFT JOIN Parts p ON ps.PartID = p.PartID
        LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
        WHERE i.InvoiceID IN ($placeholders)
        ORDER BY i.DateCreated DESC";

// Execute query with the selected invoice IDs
$stmt = $pdo->prepare($sql);
$stmt->execute($selectedIds);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Invoices</title>
    <style>
        /* Print-specific styles to format the output nicely */
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
    <!-- Header section with logo and report details -->
    <div class="header">
        <img src="../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Selected Invoices</h1>
            <p>Total Selected: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <!-- Invoice data table -->
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['InvoiceNr']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['DateCreated']))); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                    <td>€<?php echo htmlspecialchars(number_format($row['Total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($row['Vat']); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 