<?php
require_once '../../config/db_connection.php';

// Get part ID from URL parameter
$partId = $_GET['id'];

// SQL query to fetch part details with related information
$sql = "SELECT p.*, s.Name as SupplierName
        FROM parts p
        LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
        WHERE p.PartID = :partId";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partId', $partId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the part data
$part = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total expenses
$totalExpenses = $part['PiecesPurch'] * $part['PricePerPiece'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Part Details - <?php echo htmlspecialchars($part['PartDesc']); ?></title>
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
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                page-break-inside: auto;
            }
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .table th {
                background-color: #f2f2f2;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            .table thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../assets/logo.png" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Part Details</h1>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Part Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Part Description</div>
                <div class="info-value"><?php echo htmlspecialchars($part['PartDesc']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Supplier</div>
                <div class="info-value"><?php echo htmlspecialchars($part['SupplierName'] ?: 'N/A'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Date Created</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($part['DateCreated'])); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Stock</div>
                <div class="info-value"><?php echo htmlspecialchars($part['Stock']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Pieces Purchased</div>
                <div class="info-value"><?php echo htmlspecialchars($part['PiecesPurch']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Pieces Sold</div>
                <div class="info-value"><?php echo htmlspecialchars($part['Sold']); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Financial Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Price Per Piece</div>
                <div class="info-value"><?php echo number_format($part['PricePerPiece'], 2); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Sell Price</div>
                <div class="info-value"><?php echo number_format($part['SellPrice'], 2); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">VAT</div>
                <div class="info-value"><?php echo htmlspecialchars($part['Vat']); ?>%</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Expenses</div>
                <div class="info-value"><?php echo number_format($totalExpenses, 2); ?></div>
            </div>
        </div>
    </div>
</body>
</html> 