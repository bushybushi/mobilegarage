<?php
require_once '../../config/db_connection.php';

// Get customer ID from URL parameter
$customerId = $_GET['id'];

// SQL query to fetch customer details with related information
$sql = "SELECT c.CustomerID, c.FirstName, c.LastName, c.Company,
        GROUP_CONCAT(DISTINCT a.Address SEPARATOR '||') AS Addresses,
        GROUP_CONCAT(DISTINCT p.nr SEPARATOR ',') AS PhoneNumbers,
        GROUP_CONCAT(DISTINCT e.Emails SEPARATOR ',') AS EmailAddresses
        FROM customers c
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID
        LEFT JOIN phonenumbers p ON c.CustomerID = p.CustomerID
        LEFT JOIN emails e ON c.CustomerID = e.CustomerID
        WHERE c.CustomerID = :customerId
        GROUP BY c.CustomerID, c.FirstName, c.LastName, c.Company";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the customer data
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle NULL values and split concatenated fields
if ($customer) {
    $customer['Addresses'] = $customer['Addresses'] ?? '';
    $customer['PhoneNumbers'] = $customer['PhoneNumbers'] ?? '';
    $customer['EmailAddresses'] = $customer['EmailAddresses'] ?? '';
    
    $addresses = !empty($customer['Addresses']) ? explode('||', $customer['Addresses']) : [];
    $phoneNumbers = !empty($customer['PhoneNumbers']) ? explode(',', $customer['PhoneNumbers']) : [];
    $emailAddresses = !empty($customer['EmailAddresses']) ? explode(',', $customer['EmailAddresses']) : [];
}

// Fetch cars for this customer
$carSql = "SELECT c.* 
           FROM cars c 
           JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
           WHERE ca.CustomerID = :customerId";
$carStmt = $pdo->prepare($carSql);
$carStmt->execute(['customerId' => $customerId]);
$cars = $carStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details - <?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?></title>
    <style>
        @media print {
            body { 
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
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
            .section {
                margin-bottom: 30px;
            }
            .section-title {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 1px solid #ddd;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-bottom: 20px;
            }
            .info-item {
                margin-bottom: 10px;
            }
            .info-label {
                font-weight: bold;
                margin-bottom: 5px;
            }
            .info-value {
                color: #666;
            }
            .cars-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                page-break-inside: auto;
            }
            .cars-table th, .cars-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .cars-table th {
                background-color: #f2f2f2;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .cars-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            .cars-table thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Customer Details</h1>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Customer Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value"><?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Company</div>
                <div class="info-value"><?php echo htmlspecialchars($customer['Company'] ?: 'N/A'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Phone Numbers</div>
                <?php foreach ($phoneNumbers as $phone): ?>
                    <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
                <?php endforeach; ?>
            </div>
            <div class="info-item">
                <div class="info-label">Email Addresses</div>
                <?php if (!empty($emailAddresses) && $emailAddresses[0] !== ''): ?>
                    <?php foreach ($emailAddresses as $email): ?>
                        <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="info-value">N/A</div>
                <?php endif; ?>
            </div>
            <div class="info-item">
            <div class="info-label">Addresses</div>
            <?php foreach ($addresses as $address): ?>
                <div class="info-value"><?php echo htmlspecialchars($address); ?></div>
            <?php endforeach; ?>
        </div>

        </div> 
    </div>

    <div class="section">
        <div class="section-title">Cars Information</div>
        <?php if (!empty($cars)): ?>
            <table class="cars-table">
                <thead>
                    <tr>
                        <th>Brand & Model</th>
                        <th>License Number</th>
                        <th>VIN</th>
                        <th>Fuel Type</th>
                        <th>Engine</th>
                        <th>Manufacture Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($car['Brand'] . ' ' . $car['Model']); ?></td>
                            <td><?php echo htmlspecialchars($car['LicenseNr']); ?></td>
                            <td><?php echo htmlspecialchars($car['VIN']); ?></td>
                            <td><?php echo htmlspecialchars($car['Fuel']); ?></td>
                            <td><?php echo htmlspecialchars($car['Engine']); ?></td>
                            <td><?php echo htmlspecialchars($car['ManuDate']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No cars registered for this customer.</p>
        <?php endif; ?>
    </div>
</body>
</html> 