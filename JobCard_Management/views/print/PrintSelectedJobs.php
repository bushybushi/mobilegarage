<?php
require_once '../../config/db_connection.php';

// Get selected job IDs from URL
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

if (empty($selectedIds)) {
    die('No job cards selected');
}

// Create placeholders for the IN clause
$placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';

// SQL query to fetch selected job cards
$sql = "SELECT j.JobID, j.Location, j.DateCall, j.JobDesc, j.DateStart, j.DateFinish,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, 
        car.LicenseNr, car.Brand, car.Model, 
        pn.Nr as PhoneNumber,
        a.Address
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN CarAssoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN PhoneNumbers pn ON c.CustomerID = pn.CustomerID
        LEFT JOIN Addresses a ON c.CustomerID = a.CustomerID
        WHERE j.JobID IN ($placeholders)
        ORDER BY j.DateCall DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($selectedIds);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Job Cards</title>
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
            .status-open {
                color: #28a745;
                font-weight: bold;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-closed {
                color: #dc3545;
                font-weight: bold;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .cost {
                text-align: right;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Selected Job Cards</h1>
            <p>Total Selected: <?php echo count($result); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Car Info</th>
                <th>Phone</th>
                <th>Job Start/End date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['CustomerName'] ?: 'N/A'); ?></td>
                    <td>
                        <?php 
                        $carInfo = '';
                        if (!empty($row['Brand']) || !empty($row['Model'])) {
                            $carInfo = htmlspecialchars(trim($row['Brand'] . ' ' . $row['Model']));
                        }
                        if (!empty($row['LicenseNr'])) {
                            $carInfo .= (!empty($carInfo) ? ', ' : '') . htmlspecialchars($row['LicenseNr']);
                        }
                        echo !empty($carInfo) ? $carInfo : 'N/A';
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['PhoneNumber'] ?: 'N/A'); ?></td>
                    <td>
                        <?php 
                        $startDate = !empty($row['DateStart']) ? date('d/m/Y', strtotime($row['DateStart'])) : 'N/A';
                        $endDate = !empty($row['DateFinish']) ? date('d/m/Y', strtotime($row['DateFinish'])) : 'N/A';
                        echo $startDate . ' - ' . $endDate;
                        ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($row['DateFinish'])) {
                            echo '<span class="status-closed">CLOSED</span>';
                        } else {
                            echo '<span class="status-open">OPEN</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 
