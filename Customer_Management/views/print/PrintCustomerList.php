<?php

require_once '../../config/db_connection.php';

// SQL query to fetch all customers with their related information
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
        addresses.Address, phonenumbers.nr, emails.Emails 
        FROM customers 
        JOIN addresses ON customers.CustomerID = addresses.CustomerID 
        JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
        JOIN emails ON customers.CustomerID = emails.CustomerID";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer List</title>
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
            <h1>Customer List</h1>
            <p>Total Customers: <?php echo count($result); ?></p>
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
                    <td><?php echo htmlspecialchars($row['Emails']); ?></td>
                    <td><?php echo htmlspecialchars($row['nr']); ?></td>
                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 