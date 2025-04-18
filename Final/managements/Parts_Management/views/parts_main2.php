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
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Create instance of PartsManagement
$partsMang = new PartsManagement();

// Get paginated results
$result = $partsMang->View($sortBy, $page);

if (!$result) {
    $_SESSION['message'] = "Error loading parts.";
    $_SESSION['message_type'] = "error";
    $result = [
        'data' => [],
        'total_count' => 0,
        'total_pages' => 0,
        'current_page' => 1,
        'per_page' => 15
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
<link rel="stylesheet" href="../../../assets/fonts/material.css" />
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
          <a href="../../Parts_Management/views/parts_main.php" class="pc-link">
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
    <!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
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
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <li><a class="dropdown-item" href="?sort=parts_number">Parts Number</a></li>
                            <li><a class="dropdown-item" href="?sort=date_desc">Date Created (Latest)</a></li>
                            <li><a class="dropdown-item" href="?sort=date_asc">Date Created (Oldest)</a></li>
                            <li><a class="dropdown-item" href="?sort=supplier">Supplier</a></li>
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
            <table class="table table-striped">
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
                            <td  style="cursor: pointer;">
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


            <!-- Pagination Controls -->
            <?php if ($result['total_pages'] > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sortBy; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo $page >= $result['total_pages'] ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sortBy; ?>" aria-label="Next">
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
                    <div class="table-responsive">
                        <table class="table">
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
                            <tbody id="partsTable">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Print modal pagination">
                            <ul class="pagination" id="printModalPagination">
                                <!-- Pagination will be loaded dynamically -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        /* Print Modal Styles */
        /* 
            These styles ensure the print modal has a clean, professional appearance:
            - Consistent spacing and sizing
            - Responsive table layout
            - Custom scrollbar for better usability
            - Hover effects for better interaction feedback
            - Sticky headers for easy navigation
        */
        #printModal .modal-dialog {
            max-width: 800px;
            margin: 1.75rem auto;
        }
        
        #printModal .table {
            margin: 0;
            border-collapse: collapse;
            width: 100%;
        }
        
        #printModal .table th,
        #printModal .table td {
            padding: 8px;
            font-size: 14px;
            border: none;
            vertical-align: middle;
        }
        
        #printModal .table th:first-child,
        #printModal .table td:first-child {
            width: 30px;
            padding: 8px 4px;
            text-align: center;
        }
        
        #printModal .print-invoice-select {
            width: 14px;
            height: 14px;
            margin: 0;
            padding: 0;
            display: block;
            cursor: pointer;
        }
        
        #printModal #select-all-visible {
            width: 14px;
            height: 14px;
            margin: 0;
            padding: 0;
            display: block;
            cursor: pointer;
        }
        
        #printModal .btn {
            padding: 6px 20px;
            font-size: 14px;
            margin-right: 8px;
        }
        
        #printModal .form-control {
            font-size: 14px;
            height: calc(1.5em + 0.75rem + 2px);
            padding: 6px 12px;
        }
        
        #printModal .table-responsive {
            margin: 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        #printModal .modal-body {
            padding: 16px;
        }
        
        #printModal .table tr {
            border-bottom: 1px solid #dee2e6;
        }

        #printModal .table tr:hover {
            background-color: #f8f9fa;
        }

        #printModal .table thead tr {
            border-bottom: 2px solid #dee2e6;
        }

        #printModal .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        #printModal .table th input[type="checkbox"],
        #printModal .table td input[type="checkbox"] {
            width: 14px;
            height: 14px;
            margin: 0;
            padding: 0;
            display: block;
            cursor: pointer;
        }

        /* Custom scrollbar styles */
        #printModal .table-responsive::-webkit-scrollbar {
            width: 6px;
        }

        #printModal .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #printModal .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #printModal .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Search and filter container */
        #printModal .row.mb-3 {
            margin: -4px;
        }

        #printModal .row.mb-3 > div {
            padding: 4px;
        }

        /* Selection count styling */
        #printModal #selectionCount {
            display: inline-block;
            padding: 4px 0;
            font-size: 14px;
            color: #666;
        }

        /* Print buttons container */
        #printModal .mb-3 {
            margin-bottom: 0.75rem !important;
        }

        /* Pagination styling */
        #printModal .pagination {
            margin: 0.75rem 0 0 0;
        }

        #printModal .page-link {
            padding: 0.375rem 0.75rem;
        }

        #printModal .modal-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        #printModal .modal-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>

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

    </div>
    <!-- [ Main Content ] end -->

     <!-- Required Js -->
<script src="../../../assets/js/plugins/popper.min.js"></script>
<script src="../../../assets/js/plugins/simplebar.min.js"></script>
<script src="../../../assets/js/plugins/bootstrap.min.js"></script>
<script src="../../../assets/js/icon/custom-font.js"></script>
<script src="../../../assets/js/script.js"></script>
<script src="../../../assets/js/theme.js"></script>
<script src="../../../assets/js/plugins/feather.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<!-- Add this script for sidebar active state and profile dropdown -->
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Check if we should open the add part form
    if (sessionStorage.getItem('openAddPartForm') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openAddPartForm');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('add_parts_form.php', function(response) {
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
