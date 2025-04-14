<!DOCTYPE html>
<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

//Finds first and last date of this month
$FDayCMonth = new DateTime();
$FDayCMonth->modify('first day of this month');

$LDayCMonth = new DateTime();
$LDayCMonth->modify('last day of this month');

//Finds income based on this month
$startDate = $FDayCMonth->format("Y-m-d");
$endDate = $LDayCMonth->format("Y-m-d");
$sql = "SELECT SUM(i.Total) as Income
        FROM JobCards j
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
        
        WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeCMonth = $stmt->fetchColumn() ?? 0;

//Finds expenses based on this month
$sql = "SELECT (SELECT SUM(p.PiecesPurch * p.PricePerPiece)) as Expenses
        FROM Parts p
        WHERE DateCreated BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$ExpensesCMonth = $stmt->fetchColumn() ?? 0;

//Calculates profit of this month
$ProfitCMonth = $IncomeCMonth - $ExpensesCMonth;

// Calculate percentage changes with checks for division by zero
$IncomePer = $IncomeCMonth != 0 ? number_format((($IncomeCMonth - $ExpensesCMonth) / $IncomeCMonth) * 100, 2) : 0;
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounting Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f7f9;
    }
    .sidebar {
      height: 100vh;
      background-color: #fff;
      padding: 1rem;
      border-right: 1px solid #dee2e6;
    }
    .sidebar a {
      display: block;
      padding: 0.75rem;
      color: #333;
      text-decoration: none;
      border-radius: 0.5rem;
    }
    .sidebar a.active, .sidebar a:hover {
      background-color: #3366ff;
      color: white;
    }
    .card-custom {
      background-color: white;
      border-radius: 1rem;
      padding: 1.5rem;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .profit-card {
      font-size: 1.5rem;
      font-weight: bold;
    }
    .btn-custom {
      border-radius: 2rem;
      padding: 0.5rem 1.5rem;
    }
  </style>
</head>
<body>
<div class="container-fluid">
    <!-- Main Content -->
    <div class="row g-4 mb-4">
      <!-- Current Month's Income -->
      <div class="col-md-4">
        <div class="card-custom">
          <h6>Current Month's Income</h6>
          <h3>€<?php echo $IncomeCMonth; ?></h3>
          <small class="text-muted"><?php echo $IncomePer; ?>% from last month</small>
        </div>
      </div>

      <!-- Current Month's Expenses -->
      <div class="col-md-4">
        <div class="card-custom">
          <h6>Current Month's Expenses</h6>
          <h3>€<?php echo $ExpensesCMonth; ?></h3>
        </div>
      </div>

      <!-- Current Month's Profit -->
      <div class="col-md-4">
        <div class="card-custom">
          <h6>Current Month's Profit</h6>
          <h3 class="<?php echo $ProfitCMonth < 0 ? 'text-danger' : 'text-success'; ?>">€<?php echo number_format($ProfitCMonth, 2); ?></h3>
        </div>
      </div>
    </div>

    <!-- Bar Chart -->
    <div class="card-custom">
      <canvas id="incomeChart"></canvas>
      <div class="d-flex justify-content-between mt-4">
        <button class="btn btn-outline-primary btn-custom" onclick="window.location.href='view_job_cards.php'">Job Cards - Details</button>
        <button class="btn btn-outline-primary btn-custom" onclick="window.location.href='view_parts.php'">Parts - Details</button>
        <button class="btn btn-outline-primary btn-custom" onclick="window.location.href='extra_expenses_main.php'">Extra Expenses</button>
        
        <!-- Dropdowns for selecting month and year -->
        <div class="d-flex align-items-center">
          <select id="printMonth" class="form-select me-2">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
            <?php endfor; ?>
          </select>
          <select id="printYear" class="form-select me-2">
            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
              <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
            <?php endfor; ?>
          </select>
          <button class="btn btn-outline-primary btn-custom" onclick="printFinances()">Print Finances</button>
        </div>
      </div>
    </div>
</div>
<?php 
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

$months = [];
$incomes = [];
$expenses = [];

foreach ($result as $row) {
    $months[] = $row['Month'];
    $incomes[] = (float) $row['Income'];
	  $expenses[] = $row['Expenses'];
}

?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('incomeChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($months); ?>,
      datasets: [
        {
          label: 'Income',
          data: <?php echo json_encode($incomes); ?>,
          backgroundColor: '#3366ff'
        },
        {
          label: 'Expenses',
          data: <?php echo json_encode($expenses); ?>,
          backgroundColor: '#ff9966'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        }
      }
    }
  });

  // Function to open print_finances.php with selected month and year
  function printFinances() {
    const month = document.getElementById('printMonth').value;
    const year = document.getElementById('printYear').value;

    // Create a hidden iframe
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `print_finances.php?month=${month}&year=${year}`;
    document.body.appendChild(iframe);

    // Wait for iframe to load before triggering print
    iframe.onload = function() {
      iframe.contentWindow.print();

      // Remove iframe after printing (or if user cancels)
      setTimeout(function() {
        document.body.removeChild(iframe);
      }, 1000);
    };
  }
</script>
</body>
</html>