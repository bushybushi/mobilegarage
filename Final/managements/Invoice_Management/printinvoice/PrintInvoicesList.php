<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
/**
 * Invoice List Print Page
 * 
 * This file generates a printable list of all invoices in the system.
 * It displays invoice details including supplier information and totals.
 * The page is designed to be both viewed on screen and printed.
 */

// Get database connection to fetch invoice data
require_once '../config/db_connection.php';
require_once '../models/invoice_model.php';

// SQL query to get all invoices with their supplier information
// This query joins multiple tables to get complete invoice data including supplier details
$sql = "SELECT DISTINCT 
            i.InvoiceID,
            i.InvoiceNr,
            i.DateCreated,
            i.Total,
            i.Vat,
            s.Name as SupplierName,
            s.PhoneNr as SupplierPhone,
            s.Email as SupplierEmail
        FROM invoices i
        LEFT JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
        LEFT JOIN parts p ON ps.PartID = p.PartID
        LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
        ORDER BY i.DateCreated DESC";

// Execute the query and fetch all results
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices List</title>
    <style>
        /* Default styles that apply both to screen and print */
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

        /* Print-specific styles to ensure proper formatting when printed */
        @media print {
            @page {
                margin: 0.5cm;
            }
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header section with logo and report details -->
    <div class="header">
        <img src="../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Invoices List</h1>
            <p>Total Invoices: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <!-- Invoices data table -->
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
                    <!-- Display invoice details with proper formatting and fallbacks for missing data -->
                    <td><?php echo htmlspecialchars($row['InvoiceNr'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['DateCreated']))); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierPhone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierEmail'] ?? 'N/A'); ?></td>
                    <td>€<?php echo htmlspecialchars(number_format($row['Total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($row['Vat']); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 