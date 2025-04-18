<?php
/**
 * This file generates a printable HTML page with a list of selected parts.
 * It accepts part IDs via URL parameters and creates a print-friendly page
 * showing only the selected parts with proper formatting and styling.
 */
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

// Get database connection to fetch parts data
require_once '../config/db_connection.php';

// Get the selected part IDs from the URL
// The IDs are passed as a comma-separated string and need to be converted to an array
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// If no parts are selected, stop execution and display an error message
if (empty($selectedIds)) {
    die('No parts selected for printing');
}

// Create placeholders for the IN clause
// This creates a string like "?,?,?" with the correct number of placeholders
$placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';

// SQL query to get selected parts with their supplier information
// This query joins the Parts and Suppliers tables and filters by the selected part IDs
$sql = "SELECT DISTINCT 
            p.PartID,
            p.PartDesc,
            p.DateCreated,
            p.PricePerPiece,
            p.SellPrice,
            p.Stock,
            s.Name as SupplierName
        FROM parts p
        LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
        WHERE p.PartID IN ($placeholders)
        ORDER BY p.DateCreated DESC";

// Execute the query with the selected IDs as parameters
$stmt = $pdo->prepare($sql);
$stmt->execute($selectedIds);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Parts List</title>
    <style>
        /* Print-specific styles to format the output nicely */
        /* These styles ensure the printed document looks professional and is easy to read */
        @media print {
            body { 
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            /* Header styling for the report title and logo */
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
            /* Table styling for clear data presentation */
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
            /* Ensure rows don't break across pages */
            tr { 
                page-break-inside: avoid;
                page-break-after: auto;
            }
            /* Keep headers and footers on each printed page */
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
            <h1>Selected Parts List</h1>
            <p>Selected Parts: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <!-- Parts data table -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Price/Piece</th>
                <th>Sell Price</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <!-- Display part details with proper formatting and escaping for security -->
                    <td><?php echo htmlspecialchars($row['PartDesc']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['DateCreated']))); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?></td>
                    <td>€<?php echo htmlspecialchars(number_format($row['PricePerPiece'], 2)); ?></td>
                    <td>€<?php echo htmlspecialchars(number_format($row['SellPrice'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($row['Stock']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 