<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';

// Get date range from user input or default to current month
$FDayCMonth = new DateTime();
$FDayCMonth->modify('first day of this month');

$LDayCMonth = new DateTime();
$LDayCMonth->modify('last day of this month');

$startDate = date('Y-m-d', strtotime($_GET['startDate']));
$endDate = date('Y-m-d', strtotime($_GET['endDate']));

// Income during selected period
$sql = "SELECT COALESCE(SUM(i.Total), 0) AS TotalIncome
        FROM jobcards j
        LEFT JOIN invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateStart BETWEEN :startDate AND :endDate";
$stmt = $pdo->prepare($sql);
$stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$IncomeCMonth = $stmt->fetchColumn() ?? 0;

// Expenses during selected period
$sql = "SELECT COALESCE(SUM(PiecesPurch * PricePerPiece), 0) AS TotalExpenses
        FROM parts
        WHERE DateCreated BETWEEN :startDate AND :endDate";
$stmt = $pdo->prepare($sql);
$stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$ExpensesCMonth = $stmt->fetchColumn() ?? 0;

// Profit for the period
$ProfitCMonth = $IncomeCMonth - $ExpensesCMonth;

// Detailed job cards for the period
$sql = "SELECT j.JobID, j.DateStart, j.DateFinish, i.Total
        FROM jobcards j
        LEFT JOIN invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN invoices i ON ij.InvoiceID = i.InvoiceID
        WHERE j.DateFinish BETWEEN :startDate AND :endDate
        ORDER BY j.DateFinish DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$jobCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detailed parts for the period
$sql = "SELECT p.PartID, p.PartDesc, p.PiecesPurch, p.PricePerPiece, p.DateCreated, (p.PiecesPurch * p.PricePerPiece) as Total
        FROM parts p
        WHERE p.DateCreated BETWEEN :startDate AND :endDate
        ORDER BY p.DateCreated DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detailed extra expenses for the period
$sql = "SELECT e.ExpenseID, e.Description, e.DateCreated, e.Expense as Total
        FROM extraexpenses e
        WHERE e.DateCreated BETWEEN :startDate AND :endDate
        ORDER BY e.DateCreated DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Report generation date
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
       display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 2px solid #ddd;
    }

    .header-text {
                text-align: right;
            }
    .report-logo {
      width: 200px;
      height: auto;
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
    .table th, .table td {
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <div class="report-header">
    <img src="../assets/logo.png" alt="Logo" class="report-logo">
    <div class="header-text">
    <h1><b>Financial Report</b></h1>
    <p>Generated on <?= $reportDate ?></p>
    </div>
  </div>

  <div class="summary-section">
    <div class="summary-title">Select Date Summary (<?= $startDate ?> to <?= $endDate ?>)</div>
    <div class="summary-item">Income: <strong>€ <?= number_format($IncomeCMonth, 2) ?></strong></div>
    <div class="summary-item">Expenses: <strong>€ <?= number_format($ExpensesCMonth, 2) ?></strong></div>
    <div class="summary-item">Profit: <strong>€ <?= number_format($ProfitCMonth, 2) ?></strong></div>
  </div>

  <div class="summary-section">
    <div class="summary-title">Detailed Job Cards</div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Job ID</th>
          <th>Date Start</th>
          <th>Date Finish</th>
          <th>Total (€)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobCards as $job): ?>
          <tr>
            <td><?= htmlspecialchars($job['JobID']) ?></td>
            <td><?= htmlspecialchars((new DateTime($job['DateStart']))->format('d/m/Y')) ?></td>
            <td><?= htmlspecialchars((new DateTime($job['DateFinish']))->format('d/m/Y')) ?></td>
            <td><?= number_format($job['Total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="summary-section">
    <div class="summary-title">Detailed Parts Purchased</div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Part ID</th>
          <th>Description</th>
          <th>Quantity</th>
          <th>Price Per Piece (€)</th>
          <th>Total (€)</th>
          <th>Date Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($parts as $part): ?>
          <tr>
            <td><?= htmlspecialchars($part['PartID']) ?></td>
            <td><?= htmlspecialchars($part['PartDesc']) ?></td>
            <td><?= $part['PiecesPurch'] ?></td>
            <td><?= number_format($part['PricePerPiece'], 2) ?></td>
            <td><?= number_format($part['Total'], 2) ?></td>
            <td><?= htmlspecialchars((new DateTime($part['DateCreated']))->format('d/m/Y')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="summary-section">
    <div class="summary-title">Detailed Extras Purchased</div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Expense ID</th>
          <th>Description</th>
          <th>Total (€)</th>
          <th>Date Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($expenses as $expense): ?>
          <tr>
            <td><?= htmlspecialchars($expense['ExpenseID']) ?></td>
            <td><?= htmlspecialchars($expense['Description']) ?></td>
            <td><?= number_format($expense['Total'], 2) ?></td>
            <td><?= htmlspecialchars((new DateTime($expense['DateCreated']))->format('d/m/Y')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- You could add chart integration here if you're using JS libraries like Chart.js -->
</body>
</html>