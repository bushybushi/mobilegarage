<?php
require_once dirname(dirname(__DIR__)) . '/config/db_connection.php';
require_once dirname(dirname(__DIR__)) . '/models/customer_model.php';

// Get selected customer IDs from URL
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

if (empty($selectedIds)) {
    die('No customers selected');
}

// Get selected customers using the model
$customerMang = new customerManagement();
$result = $customerMang->getSelectedCustomers($selectedIds);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Customers</title>
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
            <h1>Selected Customers</h1>
            <p>Total Selected: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['CustomerID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                    <td><?php echo !empty($row['Emails']) ? htmlspecialchars($row['Emails']) : 'N/A'; ?></td>
                    <td><?php echo !empty($row['nr']) ? htmlspecialchars($row['nr']) : 'N/A'; ?></td>
                    <td><?php echo !empty($row['Address']) ? htmlspecialchars($row['Address']) : 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 