<?php
// ExtraExpensesMain.php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';
require_once '../models/extra_expenses_model.php';

// Get sort parameter from URL
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'DateCreated';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort parameter
$allowedSortFields = ['DateCreated', 'Description', 'Expense'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'DateCreated';
}

// Validate sort order
if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
    $sortOrder = 'DESC';
}

// SQL query to fetch all extra expenses
$sql = "SELECT ExpenseID, Description, DateCreated, Expense FROM extraexpenses";

// Get total number of expenses
$totalExpenses = $pdo->query("SELECT COUNT(*) FROM extraexpenses")->fetchColumn();
$expensesPerPage = 10;
$totalPages = ceil($totalExpenses / $expensesPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $expensesPerPage;

// Modify the main query to include ORDER BY, LIMIT and OFFSET
$sql .= " ORDER BY $sortBy $sortOrder LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $expensesPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch all results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start session for handling messages
session_start();

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<i class='fas fa-check-circle'></i>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

    // Add script to auto-hide popup
    echo "<script>
        setTimeout(function() {
            document.getElementById('customPopup').classList.add('popup-hide');
            setTimeout(function() {
                document.getElementById('customPopup').remove();
            }, 500);
        }, 3000);
    </script>";

    // Clear session message after displaying
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extra Expenses Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../assets/scripts.js"></script>
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
<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Expense Count and Action Buttons -->
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div class="mb-2 mb-md-0">
                    <h2 class="mb-0">Extra Expenses</h2>
                    <small class="text-muted">Total: <?php echo count($result); ?> Expenses</small>
                </div>
                <div class="d-flex flex-wrap">
                    <div class="dropdown mr-2 mb-2 mb-md-0">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort"><?php echo $sortBy; ?></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="?sort=DateCreated&order=<?php echo $sortBy === 'DateCreated' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Date <?php echo $sortBy === 'DateCreated' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                            <li><a class="dropdown-item" href="?sort=Description&order=<?php echo $sortBy === 'Description' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Description <?php echo $sortBy === 'Description' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                            <li><a class="dropdown-item" href="?sort=Expense&order=<?php echo $sortBy === 'Expense' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Amount <?php echo $sortBy === 'Expense' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                        </ul>
                    </div>
                    <a href="#" id="addnewexpense-link" class="btn btn-primary" style="width: 100px;">Add 
                        <span>
                            <i class="fas fa-plus"></i>
                        </span>
                    </a>
                </div>
            </div>

            <!-- Expenses Table -->
             <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $expense): ?>
                        <tr onclick="openForm('<?php echo $expense['ExpenseID']; ?>')">
                            <td><?php echo htmlspecialchars($expense['ExpenseID']); ?></td>
                            <td><?php echo htmlspecialchars($expense['Description']); ?></td>
                            <td><?php echo htmlspecialchars($expense['DateCreated']); ?></td>
                            <td>$<?php echo number_format($expense['Expense'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <!-- Pagination for the main table -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center main-pagination">
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add event listener for the add new expense link
        document.getElementById('addnewexpense-link').addEventListener('click', function(e) {
            e.preventDefault();
            $.get('add_extra_expenses_form.php', {previous_link: '/MGAdmin2025/managements/Accounting_Management/views/extra_expenses_main.php'}, function(response) {
                $('#dynamicContent').html(response);
            });
        });

        // Update the selected sort text in the dropdown button
        document.addEventListener('DOMContentLoaded', function() {
            const selectedSort = document.getElementById('selectedSort');
            selectedSort.textContent = '<?php echo $sortBy; ?>';
        });


        function openForm(expenseId) {
            $.get('extraexpenses_view.php', {id: expenseId, previous_link: '/MGAdmin2025/managements/Accounting_Management/views/extra_expenses_main.php'},function(response) {
                $('#dynamicContent').html(response);
            });
        }
    </script>
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
        if (isLinkActive(link) || link.getAttribute('href') === 'accounting_main.php') {
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

<script>
$(document).ready(function() {
    // Check for expense to open from accounting view
    const expenseId = sessionStorage.getItem('openExpenseId');
    if (expenseId) {
        // Clear the storage immediately
        sessionStorage.removeItem('openExpenseId');
        // Load the expense view
        $.get('extraexpenses_view.php', { id: expenseId, previous_link: 'extra_expenses_main.php' }, function(response) {
            $('#dynamicContent').html(response);
        });
    }
});
</script>

  </body>
  <!-- [Body] end -->
  
  <!-- Include the About Modal -->
  <?php include '../../includes/about_modal.php'; ?>
  
  
  <!-- Include the Backup Modal -->
  <?php include '../../includes/backup_modal.php'; ?>
</html> 