<?php
require_once '../../config/db_connection.php';

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$jobId) {
    die('No job ID provided');
}

// Get the next invoice number
$sql = "SELECT InvoiceNr FROM Invoices ORDER BY InvoiceID DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetch();

// Determine next invoice number
if ($result && !empty($result['InvoiceNr'])) {
    // Extract only the numeric part and increment
    $currentNumber = intval($result['InvoiceNr']);
    $nextNumber = $currentNumber + 1;
    
    // Reset to 1 if over 9999
    if ($nextNumber > 9999) {
        $nextNumber = 1;
    }
} else {
    // Start from 1 if no invoices exist
    $nextNumber = 1;
}

// Format to 4 digits with leading zeros
$invoiceNr = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

// Get job card details with customer and car info
$sql = "SELECT j.JobID, j.DateStart, j.DateFinish, j.DriveCosts, 
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        c.Company,
        a.Address,
        car.Brand, car.Model, car.LicenseNr,
        GROUP_CONCAT(DISTINCT pn.Nr) as PhoneNumbers,
        GROUP_CONCAT(DISTINCT e.Emails) as EmailAddresses
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN CarAssoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN PhoneNumbers pn ON c.CustomerID = pn.CustomerID
        LEFT JOIN Emails e ON c.CustomerID = e.CustomerID
        LEFT JOIN Addresses a ON c.CustomerID = a.CustomerID
        WHERE j.JobID = ?
        GROUP BY j.JobID";

$stmt = $pdo->prepare($sql);
$stmt->execute([$jobId]);
$jobCard = $stmt->fetch(PDO::FETCH_ASSOC);

// Get parts used in this job
$partsSql = "SELECT p.PartDesc, jp.PiecesSold, jp.PricePerPiece, p.Vat
             FROM JobCardParts jp
             JOIN Parts p ON jp.PartID = p.PartID
             WHERE jp.JobID = ?";

$partsStmt = $pdo->prepare($partsSql);
$partsStmt->execute([$jobId]);
$parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$totalVat = 0;

foreach ($parts as $part) {
    $lineTotal = $part['PiecesSold'] * $part['PricePerPiece'];
    $subtotal += $lineTotal;
    $totalVat += $lineTotal * ($part['Vat'] / 100);
}

// Add drive costs to subtotal
$subtotal += $jobCard['DriveCosts'];
$total = $subtotal + $totalVat;

// Insert new invoice record
try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert new invoice record
    $insertSql = "INSERT INTO Invoices (InvoiceNr, DateCreated, Vat, Total) 
                  VALUES (?, CURDATE(), ?, ?)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([$invoiceNr, $totalVat, $total]);
    $invoiceId = $pdo->lastInsertId();

    // Link invoice to job
    $linkSql = "INSERT INTO InvoiceJob (JobID, InvoiceID) VALUES (?, ?)";
    $linkStmt = $pdo->prepare($linkSql);
    $linkStmt->execute([$jobId, $invoiceId]);

    // Commit transaction
    $pdo->commit();
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    die('Error creating invoice: ' . $e->getMessage());
}

// Calculate total cost
$totalCost = $total + $jobCard['DriveCosts'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $invoiceNr; ?></title>
    <style>
        @media print {
            body { 
                font-family: Arial, sans-serif;
                margin: 40px;
                color: #333;
            }
            .invoice-header {
                border-bottom: 2px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .invoice-title {
                font-size: 32px;
                color: #333;
                margin-bottom: 30px;
            }
            .company-info {
                float: left;
                width: 40%;
            }
            .invoice-info {
                float: right;
                width: 40%;
                text-align: right;
            }
            .clear {
                clear: both;
            }
            .client-info {
                margin: 10px 0;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                padding: 12px;
                text-align: left;
            }
            td {
                padding: 12px;
                border-bottom: 1px solid #dee2e6;
            }
            .text-right {
                text-align: right;
            }
            .totals {
                width: 40%;
                float: right;
                margin-top: 20px;
            }
            .totals table {
                width: 100%;
            }
            .totals table td {
                padding: 5px;
            }
            .total-row {
                font-weight: bold;
                font-size: 1.2em;
            }
            .footer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                text-align: center;
                font-size: 0.9em;
                color: #666;
            }
        }
        @media screen {
            body { 
                font-family: Arial, sans-serif;
                margin: 40px;
                color: #333;
            }
            .invoice-header {
                border-bottom: 2px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .invoice-title {
                font-size: 32px;
                color: #333;
                margin-bottom: 30px;
            }
            .company-info {
                float: left;
                width: 40%;
            }
            .invoice-info {
                float: right;
                width: 40%;
                text-align: right;
            }
            .clear {
                clear: both;
            }
            .client-info {
                margin: 10px 0;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                padding: 12px;
                text-align: left;
            }
            td {
                padding: 12px;
                border-bottom: 1px solid #dee2e6;
            }
            .text-right {
                text-align: right;
            }
            .totals {
                width: 40%;
                float: right;
                margin-top: 20px;
            }
            .totals table {
                width: 100%;
            }
            .totals table td {
                padding: 5px;
            }
            .total-row {
                font-weight: bold;
                font-size: 1.2em;
            }
            .footer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                text-align: center;
                font-size: 0.9em;
                color: #666;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
            // Close the window after print dialog closes
            window.onafterprint = function() {
                window.close();
            };
            // Also close if user cancels print dialog
            setTimeout(function() {
                window.close();
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="invoice-header">
        <div class="company-info">
            <img src="../../assets/logo.png" alt="Company Logo" style="max-width: 200px;">
            <p>Mobile Garage Larnaca<br>
               Phone: +35799851876<br>
               Email: mobilegaragelarnaca@outlook.com</p>
        </div>
        <div class="invoice-info">
            <h1 class="invoice-title">INVOICE</h1>
            <p>
                Invoice #: <?php echo $invoiceNr; ?><br>
                Date of Call: <?php echo date('d/m/Y'); ?><br>
                Job Start Date: <?php echo !empty($jobCard['DateStart']) ? date('d/m/Y', strtotime($jobCard['DateStart'])) : 'N/A'; ?><br>
                Job End Date: <?php echo !empty($jobCard['DateFinish']) ? date('d/m/Y', strtotime($jobCard['DateFinish'])) : 'N/A'; ?>
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="client-info">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
            <div style="width: 28%;">
                <strong>Bill To:</strong><br>
                <?php echo htmlspecialchars($jobCard['CustomerName']); ?><br>
                <?php if (!empty($jobCard['Company'])): ?>
                    <?php echo htmlspecialchars($jobCard['Company']); ?><br>
                <?php endif; ?>
                <?php echo htmlspecialchars($jobCard['Address']); ?>
            </div>
            <div style="width: 28%;">
                <strong>Contact:</strong><br>
                Phone: <?php echo htmlspecialchars($jobCard['PhoneNumbers']); ?><br>
                <?php if (!empty($jobCard['EmailAddresses'])): ?>
                    Email: <?php echo htmlspecialchars($jobCard['EmailAddresses']); ?>
                <?php endif; ?>
            </div>
            <div style="width: 28%;">
                <strong>Vehicle Information:</strong><br>
                <?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model']); ?><br>
                License Plate: <?php echo htmlspecialchars($jobCard['LicenseNr']); ?>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>VAT %</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parts as $part): ?>
                <tr>
                    <td><?php echo htmlspecialchars($part['PartDesc']); ?></td>
                    <td><?php echo $part['PiecesSold']; ?></td>
                    <td>€<?php echo number_format($part['PricePerPiece'], 2); ?></td>
                    <td><?php echo $part['Vat']; ?>%</td>
                    <td class="text-right">€<?php echo number_format($part['PiecesSold'] * $part['PricePerPiece'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($jobCard['DriveCosts'] > 0): ?>
                <tr>
                    <td>Drive Costs</td>
                    <td>1</td>
                    <td>€<?php echo number_format($jobCard['DriveCosts'], 2); ?></td>
                    <td>0%</td>
                    <td class="text-right">€<?php echo number_format($jobCard['DriveCosts'], 2); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">€<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td>VAT:</td>
                <td class="text-right">€<?php echo number_format($totalVat, 2); ?></td>
            </tr>
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">€<?php echo number_format($total, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>

</body>
</html> 