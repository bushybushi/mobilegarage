<?php
require_once dirname(dirname(dirname(__DIR__))) . '/config/db_connection.php';
require_once dirname(dirname(dirname(__DIR__))) . '/models/invoice_model.php';

// Get selected invoice IDs from URL
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

if (empty($selectedIds)) {
    die('No invoices selected');
}

try {
    // Get invoices using the model
    $invoiceMang = new InvoiceManagement();
    $invoices = $invoiceMang->getSelectedInvoices($selectedIds);

    if ($invoices === false) {
        throw new Exception("Failed to get selected invoices");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Invoices</title>
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
            <h1>Selected Invoices</h1>
            <p>Total Selected: <?php echo count($invoices); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Total</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['InvoiceNr']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['DateCreated']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['SupplierName']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['SupplierPhone']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['SupplierEmail']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['Total']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['Vat']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error in PrintSelectedInvoices.php: " . $e->getMessage());
    echo "Error loading invoices. Please try again.";
}
?> 