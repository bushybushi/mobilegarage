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





//Finds monday and sunday of current and last weeks
$FDayCWeek = new DateTime();
$FDayCWeek->modify('monday this week');

$LDayCWeek = new DateTime();
$LDayCWeek->modify('sunday this week');

$FDayLWeek = new DateTime();
$FDayLWeek->modify('monday last week');

$LDayLWeek = new DateTime();
$LDayLWeek->modify('sunday last week');

//Associate startDate and endDate as current week for SQL query
$startDate = $FDayCWeek->format("Y-m-d");
$endDate = $LDayCWeek->format("Y-m-d");

//Finds current week's income
$sql = "SELECT SUM(i.Total) as Income
		FROM JobCards j
		LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
		LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
		
		WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeCWeek = $stmt->fetchColumn() ?? 0;


//Finds current week's expenses
$sql = "SELECT (SELECT SUM(p.PiecesPurch * p.PricePerPiece)) as Expenses
        FROM Parts p
		WHERE DateCreated BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$ExpensesCWeek = $stmt->fetchColumn() ?? 0;


//Associate startDate and endDate as last week for SQL query
$startDate = $FDayLWeek->format("Y-m-d");
$endDate = $LDayLWeek->format("Y-m-d");

//Finds last week's income
$sql = "SELECT SUM(i.Total) as Income
		FROM JobCards j
		LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
		LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
		
		WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeLWeek = $stmt->fetchColumn() ?? 0;


//Finds current week's expenses
$sql = "SELECT (SELECT SUM(p.PiecesPurch * p.PricePerPiece)) as Expenses
        FROM Parts p
		WHERE DateCreated BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$ExpensesLWeek = $stmt->fetchColumn() ?? 0;

$IncomePer = number_format((($IncomeCWeek - $IncomeLWeek)/$IncomeLWeek) * 100,2);
$ExpensesPer = number_format((($ExpensesCWeek - $ExpensesLWeek)/$ExpensesLWeek) * 100,2);
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
      <small class="text-muted"><?php echo $IncomePer; ?>% from last week</small>
    </div>
  </div>

  <!-- Current Month's Expenses -->
  <div class="col-md-4">
    <div class="card-custom">
      <h6>Current Month's Expenses</h6>
      <h3>€<?php echo $ExpensesCMonth; ?></h3>
      <small class="text-muted"><?php echo $ExpensesPer; ?>% from last week</small>
    </div>
  </div>

  <!-- Current Month's Profit -->
  <div class="col-md-4">
    <div class="card-custom">
      <h6>Current Month's Profit</h6>
		<?php
		$profitClass = $ProfitCMonth < 0 ? 'text-danger' : 'text-success';
		?>
      <h3 class="<?php echo $profitClass?>">€<?php echo number_format($ProfitCMonth, 2); ?></h3>
      <small class="text-muted">Calculated from income - expenses</small>
    </div>
  </div>
</div>

      <!-- Bar Chart -->
      <div class="card-custom">
        <canvas id="incomeChart"></canvas>
        <div class="d-flex justify-content-between mt-4">
          <button class="btn btn-outline-primary btn-custom">Job Cards - Details</button>
          <button class="btn btn-outline-primary btn-custom">Parts - Details</button>
          <button class="btn btn-outline-primary btn-custom">Extra Expenses</button>
          <button class="btn btn-outline-primary btn-custom">Print Finances</button>
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
</script>
</body>
</html>