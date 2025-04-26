<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';


//Finds first and last date of this month
$FDayCMonth = new DateTime();
$FDayCMonth->modify('first day of this month');

$LDayCMonth = new DateTime();
$LDayCMonth->modify('last day of this month');

//Finds income based on this month
$startDate = $FDayCMonth->format("Y-m-d");
$endDate = $LDayCMonth->format("Y-m-d");
$sql = "SELECT j.JobID, j.DriveCosts
        FROM jobcards j
        WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$jobCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$IncomeCMonth = 0;
foreach ($jobCards as $jobCard) {
    // Get parts for this job card
    $partsSql = "SELECT p.PartDesc, jp.PiecesSold, p.SellPrice, p.Vat
                 FROM jobcardparts jp
                 JOIN parts p ON jp.PartID = p.PartID
                 WHERE jp.JobID = :jobId";
    
    $partsStmt = $pdo->prepare($partsSql);
    $partsStmt->bindParam(':jobId', $jobCard['JobID']);
    $partsStmt->execute();
    $parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate parts subtotal and VAT
    $subtotal = 0;
    $totalVat = 0;
    foreach ($parts as $part) {
        $lineTotal = $part['PiecesSold'] * $part['SellPrice'];
        $subtotal += $lineTotal;
        $totalVat += $lineTotal * ($part['Vat'] / 100);
    }
    // Add drive costs to subtotal
    $subtotal += $jobCard['DriveCosts'];
    
    // Add total (subtotal + VAT) to monthly income
    $IncomeCMonth += $subtotal + $totalVat;
}

//Finds expenses based on this month
$sql = "SELECT SUM(total) AS Expenses
	FROM (
    SELECT SUM(p.PiecesPurch * p.PricePerPiece) AS total
    FROM parts p
    WHERE p.DateCreated BETWEEN :startDate AND :endDate

    UNION ALL

    SELECT SUM(e.Expense) AS total
    FROM extraexpenses e
    WHERE e.DateCreated BETWEEN :startDate AND :endDate
) AS combined;";

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
		FROM jobcards j
		LEFT JOIN invoicejob ij ON j.JobID = ij.JobID
		LEFT JOIN invoices i ON ij.InvoiceID = i.InvoiceID
		
		WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeCWeek = $stmt->fetchColumn() ?? 0;


//Finds current week's expenses
$sql = "SELECT (SELECT SUM(p.PiecesPurch * p.PricePerPiece)) as Expenses
        FROM parts p
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
		FROM jobcards j
		LEFT JOIN invoicejob ij ON j.JobID = ij.JobID
		LEFT JOIN invoices i ON ij.InvoiceID = i.InvoiceID
		
		WHERE j.DateFinish BETWEEN :startDate AND :endDate";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$IncomeLWeek = $stmt->fetchColumn() ?? 0;


//Finds current week's expenses
$sql = "SELECT SUM(total) AS Expenses
	FROM (
    SELECT SUM(p.PiecesPurch * p.PricePerPiece) AS total
    FROM parts p
    WHERE p.DateCreated BETWEEN :startDate AND :endDate

    UNION ALL

    SELECT SUM(e.Expense) AS total
    FROM extraexpenses e
    WHERE e.DateCreated BETWEEN :startDate AND :endDate
	) AS combined;";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->execute();
$ExpensesLWeek = $stmt->fetchColumn() ?? 0;

if ($IncomeLWeek != 0)
$IncomePer = number_format((($IncomeCWeek - $IncomeLWeek)/$IncomeLWeek) * 100,2);
else
$IncomePer = number_format(0,2);	

if ($ExpensesLWeek != 0)
$ExpensesPer = number_format((($ExpensesCWeek - $ExpensesLWeek)/$ExpensesLWeek) * 100,2);
else
$ExpensesPer = number_format(0,2);
?>
<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Accounting</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <link rel="shortcut icon" type="image/png" href="../../../assets/images/icon.png"/>

 
 <!-- [Google Font] Family -->
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
<!-- [phosphor Icons] https://phosphoricons.com/ -->
<link rel="stylesheet" href="../../../assets/fonts/phosphor/duotone/style.css" />
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="../../../assets/fonts/tabler-icons.min.css" />
<!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="../../../assets/fonts/feather.css" />
<!-- [Font Awesome Icons] https://fontawesome.com/icons -->
<link rel="stylesheet" href="../../../assets/fonts/fontawesome.css" />
<!-- [Material Icons] https://fonts.google.com/icons -->
<link rel="stylesheet" href="../assets/fonts/material.css" />
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="../../../assets/css/style.css" id="main-style-link" />
<link rel="stylesheet" href="../../../assets/css/style-preset.css" />
<link rel="stylesheet" href="styles.css" />
<link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->
  <body>
    <!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="../../dashboard.php" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="../../../assets/images/logo.png" style="max-width: 12rem;" alt="" class="logo" />
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label>MAIN MENU</label>
          <i class="ti ti-dashboard"></i>
        </li>
        <li class="pc-item">
          <a href="../../dashboard.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
            </a>
        </li>

        <li class="pc-item">
        <a href="../../Customer_Management/views/customer_main.php"  class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Customers</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="../../Parts_Management/views/parts_main.php"  class="pc-link">
            <span class="pc-micon"><i class="ti ti-box"></i></span>
            <span class="pc-mtext">Parts</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="../../JobCard_Management/views/job_cards_main.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-folder"></i></span>
            <span class="pc-mtext">Jobs</span>
          </a>
        </li>

        <li class="pc-item">
          <a href="accounting_main.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-vocabulary"></i></span>
            <span class="pc-mtext">Accounting</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="../../Invoice_Management/views/invoice_main.php"  class="pc-link">
            <span class="pc-micon"><i class="ti ti-receipt-2"></i></span>
            <span class="pc-mtext">Invoices</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="../../UserAccess/process.php?action=logout" class="pc-link" style="margin-bottom:3rem;position: absolute;bottom: 0;">
            <span class="pc-micon"><i class="ti ti-logout"></i></span>
            <span class="pc-mtext">Log Out</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->
 <!-- [ Header Topbar ] start -->
 <header class="pc-header">
  <div class="header-wrapper">
    <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <li class="pc-h-item header-mobile-collapse">
      <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="dropdown pc-h-item d-inline-flex d-md-none">
      <a
        class="pc-head-link head-link-secondary dropdown-toggle arrow-none m-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ti ti-search"></i>
      </a>
      <div class="dropdown-menu pc-h-dropdown drp-search">
        <form class="px-3">
          <div class="mb-0 d-flex align-items-center">
            <i data-feather="search"></i>
            <input type="search" class="form-control border-0 shadow-none" placeholder="Search here . . ." />
          </div>
        </form>
      </div>
    </li>
    <li class="pc-h-item d-none d-md-inline-flex">
      <form class="header-search">

      
        <i data-feather="search" class="icon-search"></i>
        <input type="search" class="form-control" id="searchInput" placeholder="Search here. . ." autocomplete="off" />
        <button class="btn btn-light-secondary btn-search"><i class="ti ti-adjustments-horizontal"></i></button>
         </form>
  
    </li>
  </ul>
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <img src="../../../assets/images/profile.png" alt="user-image" class="user-avtar" />
        <span>
          <i class="ti ti-settings"></i>
        </span>
      </a>
      <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header">
          <h4 style="text-align: center;" id="greeting-text">
            Good Morning
          </h4>
          <hr />
          <a href="../../User_Management/views/user_main.php" id="users-link" class="dropdown-item">
            <i class="ti ti-user"></i>
            <span>User Management</span>
          </a>
          <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#backupRestoreModal">
            <i class="ti ti-cloud-upload"></i>
            <span>Backup and Restore</span>
          </a>
          <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#aboutModal">
            <i class="ti ti-info-circle"></i>
            <span>About</span>
          </a>
          <a href="../../UserAccess/process.php?action=logout" class="dropdown-item">
            <i class="ti ti-logout"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </li>
  </ul>
</div>
</div>
</header>
<!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
    <div id="dynamicContent">
    <html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounting Dashboard</title>
  <style>
    body {
      background-color: #f1f7f9;
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
        <div style="height: 500px;">
          <canvas id="incomeChart"></canvas>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-4">
          <button class="btn btn-outline-primary btn-custom w-md-auto" onclick="loadJobCardsDetails()">Job Cards - Details</button>
          <button class="btn btn-outline-primary btn-custom w-md-auto" onclick="loadPartsDetails()">Parts - Details</button>
          <button class="btn btn-outline-primary btn-custom w-md-auto" onclick="loadExtraExpenses()">Extra Expenses</button>
          <button class="btn btn-outline-primary btn-custom w-md-auto" onclick="loadFinances()">Finances - Details</button>
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
    FROM jobcards j
    WHERE j.DateFinish >= DATE_FORMAT(NOW(), '%Y-01-01')

    UNION

    SELECT DISTINCT DATE_FORMAT(p.DateCreated, '%Y-%m-01') AS MonthDate
    FROM parts p
    WHERE p.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')

    UNION

    SELECT DISTINCT DATE_FORMAT(e.DateCreated, '%Y-%m-01') AS MonthDate
    FROM extraexpenses e
    WHERE e.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')
) AS data

LEFT JOIN (
    SELECT 
        DATE_FORMAT(j.DateFinish, '%Y-%m-01') AS MonthDate,
        SUM(
            COALESCE(
                (SELECT SUM(jp.PiecesSold * p.SellPrice) 
                 FROM jobcardparts jp 
                 JOIN parts p ON jp.PartID = p.PartID 
                 WHERE jp.JobID = j.JobID),
                0
            ) + 
            COALESCE(
                (SELECT SUM(jp.PiecesSold * p.SellPrice * p.Vat / 100) 
                 FROM jobcardparts jp 
                 JOIN parts p ON jp.PartID = p.PartID 
                 WHERE jp.JobID = j.JobID),
                0
            ) + 
            j.DriveCosts
        ) AS TotalIncome
    FROM jobcards j
    WHERE j.DateFinish >= DATE_FORMAT(NOW(), '%Y-01-01')
    GROUP BY MonthDate
) AS i ON i.MonthDate = data.MonthDate

LEFT JOIN (
    SELECT 
        MonthDate,
        SUM(TotalExpense) AS TotalExpenses
    FROM (
        SELECT 
            DATE_FORMAT(p.DateCreated, '%Y-%m-01') AS MonthDate,
            SUM(p.PiecesPurch * p.PricePerPiece) AS TotalExpense
        FROM parts p
        WHERE p.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')
        GROUP BY MonthDate

        UNION ALL

        SELECT 
            DATE_FORMAT(e.DateCreated, '%Y-%m-01') AS MonthDate,
            SUM(e.Expense) AS TotalExpense
        FROM extraexpenses e
        WHERE e.DateCreated >= DATE_FORMAT(NOW(), '%Y-01-01')
        GROUP BY MonthDate
    ) AS monthly_expenses
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
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: {
              size: 12
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              size: 11
            }
          }
        },
        x: {
          ticks: {
            font: {
              size: 11
            }
          }
        }
      }
    }
  });

  function loadJobCardsDetails() {
  window.location.href = 'view_job_cards.php';


  }

  function loadPartsDetails() {
    window.location.href = 'view_parts.php';
  }

  function loadExtraExpenses() {
    window.location.href = 'extra_expenses_main.php';
  }

  

  function loadFinances() {
  window.location.href = 'view_finances.php';


  }

  
</script>
</body>
</html>
</div>

    </div>
    <!-- [ Main Content ] end -->

     <!-- Required Js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../../assets/js/plugins/popper.min.js"></script>
<script src="../../../assets/js/plugins/simplebar.min.js"></script>
<script src="../../../assets/js/plugins/bootstrap.min.js"></script>
<script src="../../../assets/js/icon/custom-font.js"></script>
<script src="../../../assets/js/script.js"></script>
<script src="../../../assets/js/theme.js"></script>
<script src="../../../assets/js/plugins/feather.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<!-- Add this script for sidebar active state, profile dropdown, and auto-open add form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should open the add customer form
    if (sessionStorage.getItem('openAccountingDetails') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openAccountingDetails');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('accounting_main.php', function(response) {
                $('#dynamicContent').html(response);
            });
        }, 100);
    }

    // Get current page path
    const currentPath = window.location.pathname;
    
    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.pc-navbar .pc-link');
    
    // Function to check if a link matches the current path
    function isLinkActive(link) {
        const href = link.getAttribute('href');
        if (!href) return false;
        
        // Handle relative paths
        if (href.startsWith('../')) {
            return currentPath.includes(href.replace('../', ''));
        }
        
        // Handle absolute paths
        return currentPath.includes(href);
    }
    
    // Add active class to matching link
    sidebarLinks.forEach(link => {
        if (isLinkActive(link)) {
            link.classList.add('active');
            // Also add active class to parent li element
            link.closest('.pc-item').classList.add('active');
        }
    });

    // Initialize profile dropdown
    const profileDropdown = document.querySelector('.header-user-profile .dropdown-toggle');
    if (profileDropdown) {
        const dropdown = new bootstrap.Dropdown(profileDropdown);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target)) {
                dropdown.hide();
            }
        });
    }

    // Dynamic greeting
    const greetingElement = document.getElementById('greeting-text');
    if (!greetingElement) return;

    const hour = new Date().getHours();
    let greeting = '';

    if (hour < 12) {
        greeting = 'Good Morning';
    } else if (hour < 17) {
        greeting = 'Good Afternoon';
    } else {
        greeting = 'Good Evening';
    }

    greetingElement.textContent = greeting;
});
 

// Backup dynamic display for errors handling
$(document).ready(function () {
  $('#backupForm').on('submit', function (e) {
    e.preventDefault(); // Prevent default form submit

    // Disable button and show "Backing up..." text
    const $btn = $(this).find('button');
	const $result = $('#backupResult');
    $btn.prop('disabled', true).text('Backing up...');

    $.ajax({
      url: '/MGAdmin2025/managements/includes/backup.php',
      type: 'POST',
      success: function (response) {
        $result.text(response).css('color', '#90ee90'); // light green
      },
      error: function (xhr, status, error) {
        $result.text("Backup failed: " + error).css('color', '#ffcccb'); // light red
      },
      complete: function () {
        $btn.prop('disabled', false).text('Backup');
      }
    });
  });
});
// Restore dynamic display for errors handling
$(document).ready(function () {
  $('#restoreForm').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const $btn = $(this).find('button');
    const $modal = $('#restoreModal');
    const $result = $('#backupResult');

    $btn.prop('disabled', true).text('Restoring...');

    $.ajax({
      url: '/MGAdmin2025/managements/includes/restore.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        $modal.modal('show');
        $result.text(response).css('color', '#90ee90');
      },
      error: function (xhr, status, error) {
        $modal.modal('show');
        $result.text("Restore failed: " + error).css('color', '#ffcccb');
      },
      complete: function () {
        $btn.prop('disabled', false).text('Restore');
      }
    });
  });
}); 

</script>

<script>
  font_change('Inter');
</script>
 
<script>
  preset_change('preset-1');
</script>

  </body>
  <!-- [Body] end -->
  
  <!-- Include the About Modal -->
  <?php include '../../includes/about_modal.php'; ?>
  
  
  <!-- Include the Backup Modal -->
  <?php include '../../includes/backup_modal.php'; ?>
</html>
