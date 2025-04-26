<?php
require_once '../../UserAccess/protect.php';
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : null;

// SQL query to fetch all job cards with related information
$sql = "SELECT j.JobID, j.Location, j.DateCall, j.JobDesc, j.DateStart, j.DateFinish, 
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, 
        car.LicenseNr, car.Brand, car.Model, 
        pn.Nr as PhoneNumber,
        a.Address
        FROM jobcards j 
        LEFT JOIN jobcar jc ON j.JobID = jc.JobID
        LEFT JOIN cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN carassoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN phonenumbers pn ON c.CustomerID = pn.CustomerID
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID";

// Add WHERE clause if filtering
if (!empty($filter)) {
    $sql .= " WHERE 
            LOWER(j.Location) LIKE :filter OR
            LOWER(j.DateCall) LIKE :filter OR
            LOWER(j.JobDesc) LIKE :filter OR
            LOWER(j.DateStart) LIKE :filter OR
            LOWER(j.DateFinish) LIKE :filter OR
            LOWER(c.FirstName) LIKE :filter OR
            LOWER(c.LastName) LIKE :filter OR
            LOWER(car.LicenseNr) LIKE :filter OR
            LOWER(car.Brand) LIKE :filter OR
            LOWER(car.Model) LIKE :filter OR
            LOWER(pn.Nr) LIKE :filter OR
            LOWER(a.Address) LIKE :filter";
}

$sql .= " ORDER BY j.DateCall DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters
if (!empty($filter)) {
    $filter = '%' . strtolower($filter) . '%';
    $stmt->bindValue(':filter', $filter, PDO::PARAM_STR);
}

$stmt->execute();
$allJobCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalJobCardsCount = count($allJobCards);

$itemsPerPage = 10;
$totalPages = ceil($totalJobCardsCount / $itemsPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;
$result = array_slice($allJobCards, $offset, $itemsPerPage);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Job Cards</title>
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
<link rel="stylesheet" href="../assets/styles.css" />
<link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function() {
    // Check for job card to open from accounting view
    const jobCardId = sessionStorage.getItem('openJobCardId');
    if (jobCardId) {
      // Clear the storage immediately
      sessionStorage.removeItem('openJobCardId');
      // Load the job card view
      $.get('job_card_view.php', { id: jobCardId }, function(response) {
        $('#dynamicContent').html(response);
      });
    }

    // Check for pending job view to load
    const pendingJobId = sessionStorage.getItem('pendingJobId');
    if (pendingJobId) {
      // Load the job card view
      $.get('job_card_view.php', { id: pendingJobId }, function(response) {
        $('#dynamicContent').html(response);
      });
      // Clear the pending job ID
      sessionStorage.removeItem('pendingJobId');
    }

    // Check if we need to open the add form
    const openJobCardForm = sessionStorage.getItem('openJobCardForm');
    if (openJobCardForm === 'true') {
      // Load the add job card form
      $.get('job_cards.php', function(response) {
        $('#dynamicContent').html(response);
      });
      // Clear the flag
      sessionStorage.removeItem('openJobCardForm');
    }
  });
</script>

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
          <a href="../../Accounting_Management/views/accounting_main.php" class="pc-link">
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
        aria-haspopup="true"
        aria-expanded="false"
      >
        <i class="ti ti-search"></i>
      </a>
      <div class="dropdown-menu pc-h-dropdown drp-search" style="min-width: 300px;">
        <form class="px-3" method="GET" action="job_cards_main.php">
          <div class="mb-0 d-flex align-items-center">
            <i data-feather="search"></i>
            <input 
              type="search" 
              name="filter" 
              class="form-control border-0 shadow-none" 
              placeholder="Search here . . ." 
              value="<?= isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : '' ?>" 
              autocomplete="off"
            />
          </div>
        </form>
      </div>
    </li>
    <li class="pc-h-item d-none d-md-inline-flex">
      <form class="header-search" method="GET" action="job_cards_main.php">
        <i data-feather="search" class="icon-search"></i>
        <input 
          type="search" 
          name="filter" 
          class="form-control" 
          id="searchInput" 
          placeholder="Search here" 
          value="<?= isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : '' ?>" 
          autocomplete="off"
        />
        <button type="submit" class="btn btn-light-secondary btn-search">
          <i class="ti ti-adjustments-horizontal"></i>
        </button>
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
    <style>



/* Fade in animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, -55%); }
    to { opacity: 1; transform: translate(-50%, -50%); }
}

/* Fade out animation */
@keyframes fadeOut {
    from { opacity: 1; transform: translate(-50%, -50%); }
    to { opacity: 0; transform: translate(-50%, -55%); }
}

body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: left;
    background-color: #fff;
}

/* Status styles */
.status-open {
    color: #28a745;
    font-weight: bold;
}

.status-closed {
    color: rgb(255, 0, 0);
    font-weight: bold;
}

.title-container {
    margin-bottom: 1.5rem;
}


.header-search {
    position: relative;
}

.search-filter-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 5px;
    z-index: 1000;
    display: none;
}

.search-filter-dropdown .dropdown-item {
    transition: background-color 0.2s;
}

.search-filter-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.search-filter-dropdown .dropdown-item.active {
    background-color: #007bff;
    font-weight: 500;
}

.search-field {
    transition: all 0.3s ease;
}

.search-field:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

</style>



    <div class="form-container">
        <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <div class="mb-2 mb-md-0">
                <h2 class="mb-0">Job Cards</h2>
                <small class="text-muted">Total: <?php echo $totalJobCardsCount; ?> Job Cards</small>
            </div>
            <div class="d-flex flex-wrap">
                <div class="dropdown mr-2 mb-2 mb-md-0">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Sort by: <span id="selectedSort">Date</span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#" onclick="updateSort('Customer')">Customer</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateSort('Date')">Date</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateSort('Status')">Status</a></li>
                    </ul>
                </div>
                <div class="d-flex gap-2">
                    <button href="#" type="button" class="btn btn-success" data-toggle="modal" data-target="#printModal" style="width: 100px;">Print 
                        <span><i class="fas fa-print"></i></span>
                    </button>
                    <button href="#" id="addnewjobcard-link" type="button" class="btn btn-primary" style="width: 100px;">Add
                        <span><i class="fas fa-plus"></i></span>
                    </button>
                </div>
            </div>
        </div>

<div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Car Info</th>
                    <th>Phone</th>
                    <th>Job Start/End date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr onclick="openForm('<?php echo $row['JobID']; ?>')">
                        <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
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
                        <td><?php 
                            if (!empty($row['DateFinish'])) {
                                echo '<span class="status-closed">CLOSED</span>';
                            } else {
                                echo '<span class="status-open">OPEN</span>';
                            }
                        ?></td>
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
                    <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>&filter=<?php echo $filter?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&filter=<?php echo $filter?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>


<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Print Job Cards</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="printSearch" class="form-control" placeholder="Search job cards...">
                    </div>
                    <div class="col-md-6">
                        <select id="printFilter" class="form-control">
                            <option value="all">All Job Cards</option>
                            <option value="name">Customer Name</option>
                            <option value="car">Car Info</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <span id="selectionCount">0 job(s) selected</span>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-primary" style="width: 120px;" onclick="printAllJobs()">Print All</button>
                        <button type="button" class="btn btn-success ml-2" style="width: 120px;"onclick="printSelectedJobs()">Print Selected</button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="printSelectAll"></th>
                                <th>Name</th>
                                <th>Car Info</th>
                                <th>Phone</th>
                                <th>Job Start/End date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="printJobsTable">
                            <!-- Jobs will be loaded here -->
                        </tbody>
                    </table>
                </div>
                 <!-- Print Modal Pagination -->
                 <div class="d-flex justify-content-center align-items-center mt-3">
                        <nav>
                            <ul class="pagination modal-pagination mb-0">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadPrintModalPage(<?php echo $currentPage - 1; ?>); return false;" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="#" onclick="loadPrintModalPage(<?php echo $i; ?>); return false;"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadPrintModalPage(<?php echo $currentPage + 1; ?>); return false;" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden iframe for printing -->
<iframe id="printFrame" style="display: none;"></iframe>
<script>

// Add new job card link
$('#addnewjobcard-link').on('click', function(e) {
    e.preventDefault();
    $.get('job_cards.php', function(response) {
        $('#dynamicContent').html(response);
    });
});

function openForm(jobId) {
    $.get('job_card_view.php', { id: jobId }, function(response) {
        $('#dynamicContent').html(response);
    });
}

// Print functions
function printAllJobs() {
    const iframe = document.getElementById('printFrame');
    iframe.style.display = 'block';
    iframe.src = 'print/PrintJobCardList.php';
    
    iframe.onload = function() {
        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                console.error('Print error:', e);
                alert('Error printing. Please try again.');
            }
            
            setTimeout(function() {
                iframe.style.display = 'none';
            }, 1000);
        }, 1000);
    };
    
    $('#printModal').modal('hide');
}

function printSelectedJobs() {
    if (selectedJobIds.size === 0) {
        alert('Please select at least one job to print');
        return;
    }
    
    const iframe = document.getElementById('printFrame');
    iframe.style.display = 'block';
    iframe.src = 'print/PrintSelectedJobs.php?ids=' + Array.from(selectedJobIds).join(',');
    
    iframe.onload = function() {
        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                console.error('Print error:', e);
                alert('Error printing. Please try again.');
            }
            
            setTimeout(function() {
                iframe.style.display = 'none';
            }, 1000);
        }, 1000);
    };
    
    $('#printModal').modal('hide');
}

function updateSort(sortBy) {
    document.getElementById('selectedSort').textContent = sortBy;
    
    const tbody = document.querySelector('table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortBy) {
            case 'Customer':
                aValue = a.cells[0].textContent.trim();
                bValue = b.cells[0].textContent.trim();
                break;
            case 'Date':
                // Extract dates from the Job Start/End date column
                const aDateText = a.cells[3].textContent.trim();
                const bDateText = b.cells[3].textContent.trim();
                
                // Try to get start date first, if not available use end date
                const aStartDate = aDateText.split(' - ')[0];
                const bStartDate = bDateText.split(' - ')[0];
                
                if (aStartDate === 'N/A' && bStartDate === 'N/A') {
                    return 0;
                } else if (aStartDate === 'N/A') {
                    return 1; // b comes first
                } else if (bStartDate === 'N/A') {
                    return -1; // a comes first
                }
                
                // Convert DD/MM/YYYY to Date objects
                const aParts = aStartDate.split('/');
                const bParts = bStartDate.split('/');
                
                if (aParts.length === 3 && bParts.length === 3) {
                    aValue = new Date(aParts[2], aParts[1] - 1, aParts[0]);
                    bValue = new Date(bParts[2], bParts[1] - 1, bParts[0]);
                    return bValue - aValue; // Most recent first
                }
                
                return 0;
            case 'Status':
                aValue = a.cells[4].textContent.trim();
                bValue = b.cells[4].textContent.trim();
                
                // Custom order: OPEN, CLOSED
                const statusOrder = {
                    'OPEN': 0,
                    'CLOSED': 1
                };
                
                return statusOrder[aValue] - statusOrder[bValue];
        }
        
        return aValue.localeCompare(bValue);
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}
</script>

<script>

    $(document).ready(function() {
        updateSort('Date');
    });

    
    // Global variable to store selected job IDs
    let selectedJobIds = new Set();

    // Function to update selection count
    function updateSelectionCount() {
        const selectedCount = selectedJobIds.size;
        $('#selectionCount').text(selectedCount + ' job(s) selected');
    }

    // Function to load print modal page
    function loadPrintModalPage(page) {
        printModalCurrentPage = page;
        $.ajax({
            url: 'print/get_print_jobs.php',
            method: 'GET',
            data: { page: page },
            success: function(response) {
                $('#printJobsTable').html(response);
                
                // Restore selections after loading new page
                $('.print-job-select:not([disabled])').each(function() {
                    const jobId = $(this).closest('tr').data('job-id');
                    if (selectedJobIds.has(jobId)) {
                        $(this).prop('checked', true);
                    }
                });
                
                // Update select all checkbox state
                const totalCheckboxes = $('.print-job-select:not([disabled])').length;
                const checkedCheckboxes = $('.print-job-select:checked:not([disabled])').length;
                $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
                
                // Update pagination
                updatePrintModalPagination();
                
                updateSelectionCount();
            },
            error: function() {
                alert('Error loading jobs. Please try again.');
            }
        });
    }

    // Function to update print modal pagination
    function updatePrintModalPagination() {
        const paginationContainer = $('.modal-pagination');
        if (!paginationContainer.length) return;

        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${printModalCurrentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${printModalCurrentPage - 1}">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= printModalTotalPages; i++) {
            paginationHtml += `
                <li class="page-item ${i === printModalCurrentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next button
        paginationHtml += `
            <li class="page-item ${printModalCurrentPage >= printModalTotalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${printModalCurrentPage + 1}">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;

        paginationContainer.html(paginationHtml);
    }

    // Initialize print modal functionality
    $(document).ready(function() {
        // Load first page when modal opens
        $('#printModal').on('show.bs.modal', function() {
            printModalCurrentPage = 1;
            selectedJobIds.clear();
            updateSelectionCount();
            loadPrintModalPage(1);
        });

        // Handle pagination clicks
        $(document).on('click', '.modal-pagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page >= 1 && page <= printModalTotalPages) {
                loadPrintModalPage(page);
            }
        });

        // Handle print select all checkbox
        $('#printSelectAll').change(function() {
            const isChecked = $(this).prop('checked');
            $('.print-job-select:not([disabled])').prop('checked', isChecked);
            
            if (isChecked) {
                $('.print-job-select:not([disabled])').each(function() {
                    selectedJobIds.add($(this).closest('tr').data('job-id'));
                });
            } else {
                $('.print-job-select:not([disabled])').each(function() {
                    selectedJobIds.delete($(this).closest('tr').data('job-id'));
                });
            }
            
            updateSelectionCount();
        });

        // Handle individual checkbox changes
        $(document).on('change', '.print-job-select', function() {
            const jobId = $(this).closest('tr').data('job-id');
            if ($(this).prop('checked')) {
                selectedJobIds.add(jobId);
            } else {
                selectedJobIds.delete(jobId);
            }
            
            const totalCheckboxes = $('.print-job-select:not([disabled])').length;
            const checkedCheckboxes = $('.print-job-select:checked:not([disabled])').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
            
            updateSelectionCount();
        });

        // Handle search functionality
        $('#printSearch').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            var filterType = $('#printFilter').val();
            
            $('#printJobsTable tr').each(function() {
                var row = $(this);
                var show = false;
                
                if (searchText === '') {
                    show = true;
                } else {
                    switch(filterType) {
                        case 'name':
                            show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                            break;
                        case 'car':
                            show = row.find('td:eq(2)').text().toLowerCase().includes(searchText);
                            break;
                        case 'status':
                            show = row.find('td:eq(5)').text().toLowerCase().includes(searchText);
                            break;
                        default:
                            show = row.text().toLowerCase().includes(searchText);
                    }
                }
                
                row.toggle(show);
            });
        });

        // Handle filter change
        $('#printFilter').change(function() {
            $('#printSearch').trigger('keyup');
        });
    });




    $(document).ready(function() {
    let selectedFilters = ['all'];
    let searchValues = {
        'customer': '',
        'car': '',
        'phone': '',
        'status': ''
    };
    let printModalCurrentPage = 1; // Track print modal pagination separately
    let printModalTotalPages = 1; // Track total pages for print modal
});
    
    
    // Show modal when clicking filter button
    $('#searchFilterBtn').click(function(e) {
        e.preventDefault();
        $('#searchFilterModal').modal('show');
    });

    // Handle "All" checkbox
    $('#filterAll').change(function() {
        if ($(this).is(':checked')) {
            $('.form-check-input').not(this).prop('checked', false);
            $('.search-field').prop('disabled', true).val('');
            selectedFilters = ['all'];
            searchValues = {
                'customer': '',
                'car': '',
                'phone': '',
                'status': ''
            };
        }
    });
    
    // Handle other checkboxes
    $('.form-check-input').not('#filterAll').change(function() {
        const filterId = $(this).val();
        const searchField = $('#search' + filterId.charAt(0).toUpperCase() + filterId.slice(1));
        
        if ($(this).is(':checked')) {
            $('#filterAll').prop('checked', false);
            searchField.prop('disabled', false);
            if (!selectedFilters.includes(filterId)) {
                selectedFilters.push(filterId);
            }
        } else {
            searchField.prop('disabled', true).val('');
            const index = selectedFilters.indexOf(filterId);
            if (index > -1) {
                selectedFilters.splice(index, 1);
            }
            if (selectedFilters.length === 0) {
                $('#filterAll').prop('checked', true);
                selectedFilters = ['all'];
            }
        }
    });

</script>
</div>

    </div>
    <!-- [ Main Content ] end -->

     <!-- Required Js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/js/plugins/simplebar.min.js"></script>
<script src="../../../assets/js/icon/custom-font.js"></script>
<script src="../../../assets/js/script.js"></script>
<script src="../../../assets/js/theme.js"></script>
<script src="../../../assets/js/plugins/feather.min.js"></script>

<!-- Add customer-functions.js -->
<!-- <script src="../assets/js/customer-functions.js"></script> -->

<!-- Add this script for sidebar active state, profile dropdown, and auto-open add form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Get current page path
    const currentPath = window.location.pathname;
    
    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.pc-navbar .pc-link');
    
    // Function to check if a link matches the current path
    function isLinkActive(link) {
        const href = link.getAttribute('href');
        if (!href) return false;

        // Normalize the href potentially containing relative paths
        const linkUrl = new URL(href, window.location.href);
        return linkUrl.pathname === currentPath;
    }
    
    // Add active class to matching link
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        link.closest('.pc-item')?.classList.remove('active');
        
        if (isLinkActive(link)) {
            link.classList.add('active');
            const parentItem = link.closest('.pc-item');
            if (parentItem) {
                parentItem.classList.add('active');
            }
        }
    });

    // Initialize sort dropdown
    const sortDropdown = document.getElementById('dropdownMenuButton1');
    if (sortDropdown) {
        const dropdown = new bootstrap.Dropdown(sortDropdown);
        
        // Handle sort item clicks
        $('.dropdown-menu .dropdown-item').on('click', function(e) {
            e.preventDefault();
            const sortBy = $(this).data('sort');
            if (sortBy) {
                // Update the selected sort text
                $('#selectedSort').text($(this).text());
                // Update the URL with the new sort parameter
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortBy);
                window.location.href = url.toString();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!sortDropdown.contains(e.target)) {
                dropdown.hide();
            }
        });
    }

    // Initialize profile dropdown
    const profileDropdown = document.querySelector('.header-user-profile .dropdown-toggle');
    if (profileDropdown) {
        const profileDropdownInstance = new bootstrap.Dropdown(profileDropdown);
        
        // Handle profile dropdown item clicks
        $('.dropdown-user-profile .dropdown-item').on('click', function(e) {
            // Only prevent default for modal triggers
            if ($(this).data('bs-toggle')) {
                e.preventDefault();
            } else {
                // For non-modal links, let them work normally
                const href = $(this).attr('href');
                if (href) {
                    window.location.href = href;
                }
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target)) {
                profileDropdownInstance.hide();
            }
        });
    }

    // Initialize modals
    const backupModal = new bootstrap.Modal(document.getElementById('backupRestoreModal'));
    const aboutModal = new bootstrap.Modal(document.getElementById('aboutModal'));

    // Handle modal triggers
    $('[data-bs-target="#backupRestoreModal"]').on('click', function(e) {
        e.preventDefault();
        backupModal.show();
    });

    $('[data-bs-target="#aboutModal"]').on('click', function(e) {
        e.preventDefault();
        aboutModal.show();
    });

    // Handle modal close buttons
    $('.modal .btn-close, .modal .btn-secondary').on('click', function() {
        const modalId = $(this).closest('.modal').attr('id');
        if (modalId === 'backupRestoreModal') {
            backupModal.hide();
        } else if (modalId === 'aboutModal') {
            aboutModal.hide();
        }
    });

    // Remove any existing event listeners that might interfere
    $(document).off('click', '.dropdown-user-profile .dropdown-item[href*="User_Management"]');
    $(document).off('click', '.dropdown-user-profile .dropdown-item[href*="UserAccess"]');

    // Dynamic greeting
    const greetingElement = document.getElementById('greeting-text');
    if (greetingElement) {
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
    }
});
</script>

<script>
  font_change('Inter');
</script>
 
<script>
  preset_change('preset-1');
</script>

<?php include '../../includes/about_modal.php'; ?>

<?php include '../../includes/backup_modal.php'; ?>

<!-- Add this script at the bottom of the file, before the closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

  </body>
  <!-- [Body] end -->

</html>
