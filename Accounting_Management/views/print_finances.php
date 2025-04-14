<!DOCTYPE html>
<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

// Get month and year from query parameters
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Adjust start and end dates based on selected month and year
$startDate = "$year-$month-01";
$endDate = date("Y-m-t", strtotime($startDate));

// Fetch income and expenses for the selected month
$sql = "SELECT COALESCE(SUM(i.Total), 0) AS TotalIncome
        FROM JobCards j
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateFinish BETWEEN :startDate AND :endDate";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeCMonth = $stmt->fetchColumn() ?? 0;

$sql = "SELECT COALESCE(SUM(PiecesPurch * PricePerPiece), 0) AS TotalExpenses
        FROM Parts
        WHERE DateCreated BETWEEN :startDate AND :endDate";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$ExpensesCMonth = $stmt->fetchColumn() ?? 0;

// Calculate profit
$ProfitCMonth = $IncomeCMonth - $ExpensesCMonth;

// Get current week's income
$sql = "SELECT COALESCE(SUM(i.Total), 0) AS TotalIncome
        FROM JobCards j
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateStart >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
        AND j.DateStart < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$IncomeCWeek = $stmt->fetchColumn() ?? 0;

// Get current week's expenses
$sql = "SELECT COALESCE(SUM(PiecesPurch * PricePerPiece), 0) AS TotalExpenses
        FROM Parts
        WHERE DateCreated >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
        AND DateCreated < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ExpensesCWeek = $stmt->fetchColumn() ?? 0;

// Get last week's income
$sql = "SELECT COALESCE(SUM(i.Total), 0) AS TotalIncome
        FROM JobCards j
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateStart >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 1 WEEK)
        AND j.DateStart < DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$IncomeLWeek = $stmt->fetchColumn() ?? 0;

// Get last week's expenses
$sql = "SELECT COALESCE(SUM(PiecesPurch * PricePerPiece), 0) AS TotalExpenses
        FROM Parts
        WHERE DateCreated >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 1 WEEK)
        AND DateCreated < DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ExpensesLWeek = $stmt->fetchColumn() ?? 0;

// Get monthly data for the chart
$sql = "SELECT 
    DATE_FORMAT(data.MonthDate, '%b') AS Month,
    COALESCE(i.TotalIncome, 0) AS Income,
    COALESCE(e.TotalExpenses, 0) AS Expenses
FROM (
    SELECT DISTINCT DATE_FORMAT(j.DateFinish, '%Y-%m-01') AS MonthDate
    FROM JobCards j
    WHERE j.DateFinish >= DATE_FORMAT(NOW(), '%Y-01-01')
    
    UNION
    
    SELECT DISTINCT DATE_FORMAT(p.DateCreated, '%Y-%m-01') AS MonthDate
    FROM Parts p
    WHERE p.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')
) AS data

LEFT JOIN (
    SELECT 
        DATE_FORMAT(j.DateFinish, '%Y-%m-01') AS MonthDate,
        SUM(i.Total) AS TotalIncome
    FROM JobCards j
    LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
    LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
    WHERE j.DateFinish >= DATE_FORMAT(NOW(), '%Y-01-01')
    GROUP BY MonthDate
) AS i ON i.MonthDate = data.MonthDate

LEFT JOIN (
    SELECT 
        DATE_FORMAT(p.DateCreated, '%Y-%m-01') AS MonthDate,
        SUM(p.PiecesPurch * p.PricePerPiece) AS TotalExpenses
    FROM Parts p
    WHERE p.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')
    GROUP BY MonthDate
) AS e ON e.MonthDate = data.MonthDate

ORDER BY data.MonthDate ASC;";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get detailed job cards for current month
$sql = "SELECT j.JobID, j.DateStart, j.DateFinish, i.Total
        FROM JobCards j
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateFinish BETWEEN :startDate AND :endDate
        ORDER BY j.DateFinish DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$jobCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get detailed parts for current month
$sql = "SELECT p.PartID, p.PartDesc, p.PiecesPurch, p.PricePerPiece, p.DateCreated, (p.PiecesPurch * p.PricePerPiece) as Total
        FROM Parts p
        WHERE p.DateCreated BETWEEN :startDate AND :endDate
        ORDER BY p.DateCreated DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current date for report
$currentDate = new DateTime();
$reportDate = $currentDate->format("d/m/Y");
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Financial Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    .report-header {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }
    .report-logo {
      position: absolute;
      left: 0;
      top: 0;
      width: 100px;
      height: auto;
    }
    .report-title {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .report-date {
      font-size: 14px;
      color: #666;
    }
    .summary-section {
      margin-bottom: 30px;
    }
    .summary-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }
    .summary-item {
      margin-bottom: 10px;
    }
    .summary-label {
      font-weight: bold;
    }
    .profit-positive {
      color: green;
    }
    .profit-negative {
      color: red;
    }
    .table-section {
      margin-bottom: 30px;
    }
    .table-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
    .footer {
      margin-top: 50px;
      text-align: center;
      font-size: 12px;
      color: #666;
    }
    @media print {
      .no-print {
        display: none;
      }
      body {
        margin: 0;
      }
      .container {
        width: 100%;
        max-width: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="no-print text-end mb-3">
      <button class="btn btn-primary" onclick="window.print()">Print Report</button>
    </div>
    
    <div class="report-header">
      <img src="../assets/logo.png" alt="Company Logo" class="report-logo">
      <div class="report-title">Financial Report</div>
      <div class="report-date">Generated on: <?php echo $reportDate; ?></div>
    </div>
    
    <div class="summary-section">
      <div class="summary-title">Financial Summary</div>
      
      <div class="row">
        <div class="col-md-4">
          <div class="summary-item">
            <div class="summary-label">Current Month's Income:</div>
            <div>€<?php echo number_format($IncomeCMonth, 2); ?></div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="summary-item">
            <div class="summary-label">Current Month's Expenses:</div>
            <div>€<?php echo number_format($ExpensesCMonth, 2); ?></div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="summary-item">
            <div class="summary-label">Current Month's Profit:</div>
            <div class="<?php echo $ProfitCMonth < 0 ? 'profit-negative' : 'profit-positive'; ?>">
              €<?php echo number_format($ProfitCMonth, 2); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="table-section">
      <div class="table-title">Monthly Income vs Expenses</div>
      <table>
        <thead>
          <tr>
            <th>Month</th>
            <th>Income</th>
            <th>Expenses</th>
            <th>Profit</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result as $row): ?>
            <?php 
              $monthlyIncome = (float) $row['Income'];
              $monthlyExpenses = (float) $row['Expenses'];
              $monthlyProfit = $monthlyIncome - $monthlyExpenses;
            ?>
            <tr>
              <td><?php echo $row['Month']; ?></td>
              <td>€<?php echo number_format($monthlyIncome, 2); ?></td>
              <td>€<?php echo number_format($monthlyExpenses, 2); ?></td>
              <td class="<?php echo $monthlyProfit < 0 ? 'profit-negative' : 'profit-positive'; ?>">
                €<?php echo number_format($monthlyProfit, 2); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="table-section">
      <div class="table-title">Job Cards (Current Month)</div>
      <table>
        <thead>
          <tr>
            <th>Job ID</th>
            <th>Start Date</th>
            <th>Finish Date</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jobCards as $job): ?>
            <tr>
              <td><?php echo $job['JobID']; ?></td>
              <td><?php echo date('d/m/Y', strtotime($job['DateStart'])); ?></td>
              <td><?php echo date('d/m/Y', strtotime($job['DateFinish'])); ?></td>
              <td>€<?php echo number_format($job['Total'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="table-section">
      <div class="table-title">Parts Purchased (Current Month)</div>
      <table>
        <thead>
          <tr>
            <th>Part ID</th>
            <th>Part Name</th>
            <th>Quantity</th>
            <th>Price Per Piece</th>
            <th>Date</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($parts as $part): ?>
            <tr>
              <td><?php echo $part['PartID']; ?></td>
              <td><?php echo $part['PartDesc']; ?></td>
              <td><?php echo $part['PiecesPurch']; ?></td>
              <td>€<?php echo number_format($part['PricePerPiece'], 2); ?></td>
              <td><?php echo date('d/m/Y', strtotime($part['DateCreated'])); ?></td>
              <td>€<?php echo number_format($part['Total'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="footer">
      <p>This report was generated automatically by the Mobile Garage Management System.</p>
    </div>
  </div>
</body>
</html>