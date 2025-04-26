<?php
/**
 * Parts Management Main Dashboard
 * 
 * This file serves as the main dashboard for managing parts in the system. It provides a comprehensive
 * interface for viewing, searching, sorting, and managing parts. The dashboard includes:
 * - A table displaying all parts with their details
 * - Search functionality to filter parts
 * - Sorting capabilities for different columns
 * - Pagination for handling large datasets
 * - Options to add new parts, edit existing ones, and print parts
 * - A print modal for selecting and printing multiple parts
 */

 /* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

require_once '../../UserAccess/protect.php';
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/parts_model.php';

if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Get sort parameter from URL, default to date_desc
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : null;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Create instance of PartsManagement
$partsMang = new PartsManagement();

// Get paginated results
$result = $partsMang->View($sortBy, $page, $filter);

if (!$result) {
    $_SESSION['message'] = "Error loading parts.";
    $_SESSION['message_type'] = "error";
    $result = [
        'data' => [],
        'total_count' => 0,
        'total_pages' => 0,
        'current_page' => 1,
        'per_page' => 10
    ];
}


?>
<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Parts</title>
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
        <form class="px-3" method="GET" action="parts_main.php">
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
    <form class="header-search" method="GET" action="parts_main.php"> <!-- Adjust action if needed -->
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
        aria-haspopup="true"
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
          <a href="../../User_Management/views/user_main.php" class="dropdown-item">
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
    <!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<!-- Custom CSS for popup styling -->
<style>
    /* Popup container styling */
    .popup-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #2196f3;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        color: white;
        font-size: 18px;
        width: 300px;
        z-index: 1000;
        animation: fadeIn 0.5s ease-in-out;
    }

    /* Print table header styling */
    .print-header {
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 1;
    }

    .print-header th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    /* Ensure table header stays on top */
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Popup content styling */
    .popup-content p {
        margin: 0;
        font-weight: bold;
    }

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

    /* Add styles for the message container */
    #messageContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .alert {
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid transparent;
        border-radius: 4px;
        min-width: 300px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
    }

    .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
</style>

<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Customer Count and Action Buttons -->
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div class="mb-2 mb-md-0">
                    <h2 class="mb-0">Parts</h2>
                    <small class="text-muted">Total: <?php echo $result['total_count']; ?> Parts</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-2 mb-2 mb-md-0">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            Sort by: <span id="selectedSort">
                                <?php
                                switch($sortBy) {
                                    case 'parts_number':
                                        echo 'Parts Number';
                                        break;
                                    case 'date_asc':
                                        echo 'Date Created (Oldest)';
                                        break;
                                    case 'date_desc':
                                        echo 'Date Created (Latest)';
                                        break;
                                    case 'supplier':
                                        echo 'Supplier';
                                        break;
                                    default:
                                        echo 'Date Created (Latest)';
                                }
                                ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="#" data-sort="parts_number">Parts Number</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="date_desc">Date Created (Latest)</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="date_asc">Date Created (Oldest)</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="supplier">Supplier</a></li>
                        </ul>
                    </div>

                    <!-- Print Button -->
                    <button type="button" id="printButton" class="btn btn-success mr-2 mb-2 mb-md-0">Print 
                        <span><i class="fas fa-print"></i></span>
                    </button>
                    <!-- Add Button -->
                    <button href="#" id="addnewparts-link" type="button" class="btn btn-primary mb-2 mb-md-0">Add 
                        <span><i class="fas fa-plus"></i></span>
                    </button>
                </div>
            </div>

            <!-- Add message container -->
            <div id="messageContainer" class="mt-3"></div>

            <!-- Customer Table -->
            <div class="table-responsive">
            <table class="table table-striped" >
                <!-- Table Header -->
                <thead>
                    <tr>
                        <th>Parts Nr</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th>Supplier</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>VAT</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody>
                    <?php foreach ($result['data'] as $row): ?>
                        <tr onclick="openParts('<?php echo htmlspecialchars($row['PartID']); ?>')" data-parts-id="<?php echo htmlspecialchars($row['PartID']); ?>">
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['PartID']); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['PartDesc'] ?? 'N/A'); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['DateCreated']); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierPhone'] ?? 'N/A'); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierEmail'] ?? 'N/A'); ?>
                            </td>
                            <td style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['Vat']); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <!-- Pagination for the main table -->
            <?php if ($result['total_pages'] > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $result['current_page'] <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $result['current_page'] - 1; ?>&sort=<?php echo $sortBy; ?>&filter=<?php echo $filter?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $result['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&filter=<?php echo $filter?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo $result['current_page'] >= $result['total_pages'] ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $result['current_page'] + 1; ?>&sort=<?php echo $sortBy; ?>&filter=<?php echo $filter?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- JavaScript Functions -->
    <script>

        $('#addnewparts-link').on('click', function(e) {
        e.preventDefault();
        $.get('add_parts_form.php', function(response) {
            $('#dynamicContent').html(response);
        });
    });

    function openParts(partsId) {
        $.get('parts_view.php', { id: partsId, previous_link: '/MGAdmin2025/managements/Parts_Management/views/parts_main.php' }, function(response) {
            $('#dynamicContent').html(response);
        });
    }

        // Auto-hide message after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('customPopup');
            if (popup) {
                setTimeout(function() {
                    popup.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                    setTimeout(function() {
                        popup.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>

    <!-- Print Modal -->
    <!-- 
        This modal provides a dedicated interface for printing parts. It includes:
        - Search functionality to find specific parts
        - Filtering options by different part attributes
        - Checkbox selection for multiple parts
        - Print buttons for all or selected parts
        - A paginated table view of parts
    -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Print Parts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Search and Filter Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="printSearch" class="form-control" placeholder="Search...">
                        </div>
                        <div class="col-md-6">
                            <select id="printFilter" class="form-control">
                                <option value="all">All Parts</option>
                                <option value="part_number">Part Number</option>
                                <option value="name">Part Name</option>
                                <option value="supplier">Supplier</option>
                                <option value="category">Category</option>
                                <option value="price">Price</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div id="selectionCount" class="text-muted">0 part(s) selected</div>
                                <div>
                                    <button id="clearSelectionsBtn" class="btn btn-secondary mr-2" style="width: 120px; display:none;" onclick="clearPrintSelections()">Clear</button>
                                    <button class="btn btn-primary mr-2" style="width: 120px;" onclick="printAllParts()">Print All</button>
                                    <button class="btn btn-success" onclick="printSelectedParts()">Print Selected</button>
                    </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped" id="printPartsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-visible" onchange="toggleAllParts(this)">
                                    </th>
                                    <th>Parts Nr</th>
                                    <th>Description</th>
                                    <th>Date Created</th>
                                    <th>Supplier</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>VAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Print modal pagination">
                            <ul class="pagination mb-0" id="printModalPagination">
                                <!-- Pagination will be loaded dynamically -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Hidden iframe for print preview -->
    <!-- 
    This hidden iframe is used to maintain a clean print layout without affecting
    the main page's appearance. It ensures that the printed output is properly
    formatted and professional looking.
    -->
    <iframe id="printFrame" style="display: none;"></iframe>

    <!-- Include the print scripts -->
    <script src="../printparts/scripts.js"></script>

    <script>
    // Connect print button to modal
    $(document).ready(function() {
        $('#printButton').click(function() {
            $('#printModal').modal('show');
        });
    });
    </script>
</body>
</html> 
</div>
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

    function updateSort(sortBy) {
        document.getElementById('selectedSort').textContent = sortBy;
        
        const tbody = document.querySelector('table tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch(sortBy) {
                case 'Customer':
                    aValue = a.cells[1].textContent.trim();
                    bValue = b.cells[1].textContent.trim();
                    break;
                case 'Date':
                    // Extract dates from the Job Start/End date column
                    const aDateText = a.cells[4].textContent.trim();
                    const bDateText = b.cells[4].textContent.trim();
                    
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
                    aValue = a.cells[5].textContent.trim();
                    bValue = b.cells[5].textContent.trim();
                    
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
        $.ajax({
            url: 'print/get_print_jobs.php',
            method: 'GET',
            data: { page: page },
            success: function(response) {
                $('#printJobsTable').html(response);
                
                // Restore selections after loading new page
                $('.print-job-select').each(function() {
                    const jobId = $(this).closest('tr').data('job-id');
                    $(this).prop('checked', selectedJobIds.has(jobId));
                });
                
                // Update select all checkbox state
                const totalCheckboxes = $('.print-job-select').length;
                const checkedCheckboxes = $('.print-job-select:checked').length;
                $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
                
                updateSelectionCount();
            },
            error: function() {
                alert('Error loading jobs. Please try again.');
            }
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

    // Initialize print modal functionality
    $(document).ready(function() {
        // Load first page when modal opens
        $('#printModal').on('show.bs.modal', function() {
            loadPrintModalPage(1);
        });

        // Handle print select all checkbox
        $('#printSelectAll').change(function() {
            const isChecked = $(this).prop('checked');
            $('.print-job-select').prop('checked', isChecked);
            
            if (isChecked) {
                $('.print-job-select').each(function() {
                    selectedJobIds.add($(this).closest('tr').data('job-id'));
                });
            } else {
                selectedJobIds.clear();
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
            
            const totalCheckboxes = $('.print-job-select').length;
            const checkedCheckboxes = $('.print-job-select:checked').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            
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

    // Handle search field input
    $('.search-field').on('input', function() {
        const filterId = $(this).attr('id').replace('search', '').toLowerCase();
        searchValues[filterId] = $(this).val().toLowerCase();
    });

    // Apply filters
    $('#applyFilters').click(function() {
        searchValues = {
            'customer': $('#searchCustomer').val().toLowerCase(),
            'car': $('#searchCar').val().toLowerCase(),
            'phone': $('#searchPhone').val().toLowerCase(),
            'status': $('#searchStatus').val().toLowerCase()
        };
        
        selectedFilters = [];
        if ($('#filterAll').is(':checked')) {
            selectedFilters = ['all'];
        } else {
            $('.form-check-input').not('#filterAll').each(function() {
                if ($(this).is(':checked')) {
                    selectedFilters.push($(this).val());
                }
            });
        }
        
        $('#searchFilterModal').modal('hide');
        performSearch();
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


<!-- Add this script for sidebar active state, profile dropdown, and auto-open add form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should open the add customer form
    if (sessionStorage.getItem('openJobCardForm') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openJobCardForm');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('job_cards.php', function(response) {
                $('#dynamicContent').html(response);
            });
        }, 100);
    }

    // Get current page path
    const currentPath = window.location.pathname;
    
    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.pc-navbar .pc-link');
    
    // Function to check if a link matches the current path (Improved)
    function isLinkActive(link) {
        const href = link.getAttribute('href');
        if (!href) return false;
        
        // Normalize the href potentially containing relative paths
        // Create a URL object based on the current window location
        const linkUrl = new URL(href, window.location.href);
        
        // Compare the pathnames
        // This handles differences in protocol, host, etc., and compares only the path part
        return linkUrl.pathname === currentPath;
    }
    
    // Add active class to matching link
    sidebarLinks.forEach(link => {
        // Remove any existing active class first to avoid duplicates if logic runs multiple times
        link.classList.remove('active');
        link.closest('.pc-item')?.classList.remove('active'); // Use optional chaining
        
        if (isLinkActive(link)) {
            link.classList.add('active');
            const parentItem = link.closest('.pc-item');
            if (parentItem) {
                parentItem.classList.add('active');
            }
        }
    });

    // Initialize Bootstrap dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

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

    // Handle backup and restore modals
    $('#backupForm').on('submit', function(e) {
        e.preventDefault();
    const $btn = $(this).find('button');
	const $result = $('#backupResult');
    $btn.prop('disabled', true).text('Backing up...');

    $.ajax({
      url: '/MGAdmin2025/managements/includes/backup.php',
      type: 'POST',
            success: function(response) {
                $result.text(response).css('color', '#90ee90');
      },
            error: function(xhr, status, error) {
                $result.text("Backup failed: " + error).css('color', '#ffcccb');
      },
            complete: function() {
        $btn.prop('disabled', false).text('Backup');
      }
    });
  });

    $('#restoreForm').on('submit', function(e) {
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
            success: function(response) {
        $modal.modal('show');
        $result.text(response).css('color', '#90ee90');
      },
            error: function(xhr, status, error) {
        $modal.modal('show');
        $result.text("Restore failed: " + error).css('color', '#ffcccb');
      },
            complete: function() {
        $btn.prop('disabled', false).text('Restore');
      }
    });
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
});
</script>

<script>
  font_change('Inter');
</script>
 
<script>
  preset_change('preset-1');
</script>
  
  <!-- Include the About Modal -->
  <?php include '../../includes/about_modal.php'; ?>
  
  <?php include '../../includes/backup_modal.php'; ?>

  </body>
  <!-- [Body] end -->
  
</html>

<script>
  $(document).ready(function() {
    // Check for part to open from accounting view
    const partId = sessionStorage.getItem('openPartId');
    if (partId) {
      // Clear the storage immediately
      sessionStorage.removeItem('openPartId');
      // Load the part view
      $.get('parts_view.php', { id: partId }, function(response) {
        $('#dynamicContent').html(response);
      });
    }

    // Check if we need to open the add form
    const openPartForm = sessionStorage.getItem('openPartForm');
    if (openPartForm === 'true') {
      // Load the add part form
      $.get('parts.php', function(response) {
        $('#dynamicContent').html(response);
      });
      // Clear the flag
      sessionStorage.removeItem('openPartForm');
    }
  });
</script>

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



