<?php
/**
 * Invoice Management System - Main Dashboard
 * 
 * This page serves as the main dashboard for managing invoices. It displays a list of all invoices
 * with their key details and provides functionality for searching, sorting, and pagination.
 * Users can also add new invoices, edit existing ones, and print invoices from this interface.
 */
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

require_once '../../UserAccess/protect.php';
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/invoice_model.php';

// immediately after you create your $pdo object:
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
$pdo->setAttribute(PDO::ATTR_ERRMODE,      PDO::ERRMODE_EXCEPTION);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get sort parameter from URL, default to date_desc
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : null;

if (!empty($filter)){
$filter = '%' . strtolower($filter) . '%';
}

$count_sql = "
  SELECT COUNT(DISTINCT i.InvoiceID) as total
  FROM invoices i
    LEFT JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
    LEFT JOIN parts p        ON ps.PartID    = p.PartID
    LEFT JOIN suppliers s    ON p.SupplierID  = s.SupplierID
";

// Check if filter is set and modify the query accordingly
if (!empty($filter)) {
    $count_sql .= "
      WHERE (
        LOWER(i.InvoiceNr)    LIKE :filter OR
        LOWER(i.DateCreated)  LIKE :filter OR
        LOWER(i.Vat)          LIKE :filter OR
        LOWER(i.Total)        LIKE :filter OR
        LOWER(s.Name)         LIKE :filter OR
        LOWER(s.PhoneNr)      LIKE :filter OR
        LOWER(s.Email)        LIKE :filter
      )
    ";
}

// Prepare and execute the count query
$count_stmt = $pdo->prepare($count_sql);
if (!empty($filter)) {
    $count_stmt->bindValue(':filter', $filter, PDO::PARAM_STR);
}
$count_stmt->execute();
$total_invoices = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages    = ceil($total_invoices / $items_per_page);


// 2) Main data query
$sql = "
  SELECT
    i.InvoiceID,
    i.InvoiceNr,
    i.DateCreated,
    i.Vat,
    i.Total,
    s.Name  AS SupplierName,
    s.PhoneNr   AS SupplierPhone,
    s.Email     AS SupplierEmail,
    GROUP_CONCAT(DISTINCT p.PartDesc SEPARATOR ', ') AS Parts
  FROM invoices i
    LEFT JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
    LEFT JOIN parts p        ON ps.PartID    = p.PartID
    LEFT JOIN suppliers s    ON p.SupplierID  = s.SupplierID
";

// filtering
if (!empty($filter)) {
    $sql .= "
      WHERE (
        LOWER(i.InvoiceNr)    LIKE :filter OR
        LOWER(i.DateCreated)  LIKE :filter OR
        LOWER(i.Vat)          LIKE :filter OR
        LOWER(i.Total)        LIKE :filter OR
        LOWER(s.Name)         LIKE :filter OR
        LOWER(s.PhoneNr)      LIKE :filter OR
        LOWER(s.Email)        LIKE :filter
      )
    ";
}

// **Make sure there's a space before each added clause!**
$sql .= "
  GROUP BY
    i.InvoiceID,
    i.InvoiceNr,
    i.DateCreated,
    i.Vat,
    i.Total,
    s.Name,
    s.PhoneNr,
    s.Email
";

// ordering
switch ($sortBy) {
    case 'invoice_number':
        $sql .= " ORDER BY CAST(NULLIF(i.InvoiceNr, '') AS UNSIGNED)";
        break;
    case 'date_asc':
        $sql .= " ORDER BY i.DateCreated ASC";
        break;
    case 'date_desc':
        $sql .= " ORDER BY i.DateCreated DESC";
        break;
    case 'supplier':
        $sql .= " ORDER BY s.Name ASC";
        break;
    default:
        $sql .= " ORDER BY i.DateCreated DESC";
}

// pagination
$sql .= " LIMIT :limit OFFSET :offset";

// Prepare and execute the main query
$stmt = $pdo->prepare($sql);
if (!empty($filter)) {
    $stmt->bindValue(':filter', $filter, PDO::PARAM_STR);
}
$stmt->bindValue(':limit',  $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Invoices</title>
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
          <a href="invoice_main.php"  class="pc-link">
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
    <form class="header-search" method="GET" action="invoice_main.php"> <!-- Adjust action if needed -->
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

   
    <!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Add any custom styles here */
        .dropdown-menu {
            min-width: 200px;
        }
        .dropdown-item {
            padding: 8px 20px;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
        }
        .table tr:hover {
            background-color: #f8f9fa;
        }
        .pagination {
            margin-bottom: 0;
        }
        .page-link {
            padding: 0.5rem 0.75rem;
        }
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .title-container {
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }
        .popup-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
            animation: slideIn 0.5s ease-in-out;
        }
        .popup-content {
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #28a745;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Invoice Count and Action Buttons -->
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <div class="mb-3 mb-md-0">
                    <h2 class="mb-0">Invoices</h2>
                    <small class="text-muted">Total: <?php echo count($result); ?> Invoices</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mb-2 mb-md-0">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">
                                <?php
                                switch($sortBy) {
                                    case 'invoice_number':
                                        echo 'Invoice Number';
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
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?php echo $sortBy === 'invoice_number' ? 'active' : ''; ?>" 
                               href="?sort=invoice_number&page=<?php echo $current_page; ?>&filter=<?php echo $filter?>">Invoice Number</a>
                            <a class="dropdown-item <?php echo $sortBy === 'date_asc' ? 'active' : ''; ?>" 
                               href="?sort=date_asc&page=<?php echo $current_page; ?>&filter=<?php echo $filter?>">Date Created (Oldest)</a>
                            <a class="dropdown-item <?php echo $sortBy === 'date_desc' ? 'active' : ''; ?>" 
                               href="?sort=date_desc&page=<?php echo $current_page; ?>&filter=<?php echo $filter?>">Date Created (Latest)</a>
                            <a class="dropdown-item <?php echo $sortBy === 'supplier' ? 'active' : ''; ?>" 
                               href="?sort=supplier&page=<?php echo $current_page; ?>&filter=<?php echo $filter?>">Supplier</a>
                        </div>
                    </div>
                    <!-- Print Button -->
                    <button type="button" class="btn btn-success mb-2 mb-md-0" data-toggle="modal" data-target="#printModal">
                        Print <i class="fas fa-print"></i> 
                    </button>
                    <!-- Add New Invoice Button -->
                    <button type="button" id="addnewinvoice-link" class="btn btn-primary mb-2 mb-md-0">
                        Add <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="table-responsive">
            <table class="table table-striped">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <th>Invoice Nr</th>
                        <th>Date Created</th>
                        <th>Supplier</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>VAT</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr data-invoice-id="<?php echo htmlspecialchars($row['InvoiceID']); ?>">
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['InvoiceNr'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['DateCreated']); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierPhone'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierEmail'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                €<?php echo htmlspecialchars($row['Total']); ?>
                            </td>
                            <td onclick="openInvoice(<?php echo htmlspecialchars($row['InvoiceID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['Vat']); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sortBy; ?>&filter=<?php echo $filter?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&filter=<?php echo $filter?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>&filter=<?php echo $filter?>">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sortBy; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Modal -->
    <!-- 
        This modal provides a dedicated interface for printing invoices. It includes:
        - Search functionality to find specific invoices
        - Filtering options by different invoice attributes
        - Checkbox selection for multiple invoices
        - Print buttons for all or selected invoices
        - A paginated table view of invoices
    -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Print Invoices</h5>
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
                                <option value="all">All Invoices</option>
                                <option value="invoice_number">Invoice Number</option>
                                <option value="supplier">Supplier</option>
                                <option value="total">Total</option>
                                <option value="vat">VAT</option>
                            </select>
                        </div>
                    </div>

                    

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div id="selectionCount" class="text-muted">0 customer(s) selected</div>
                                <div>
                                    <button id="clearSelectionsBtn" class="btn btn-secondary mr-2" style="width: 120px; display:none;" onclick="clearPrintSelections()">Clear</button>
                                    <button class="btn btn-primary mr-2" style="width: 120px;" onclick="printAllInvoices()">Print All</button>
                                    <button class="btn btn-success" onclick="printSelectedInvoices()">Print Selected</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-visible" onchange="toggleAllInvoices(this)">
                                    </th>
                                    <th>Invoice Nr</th>
                                    <th>Date Created</th>
                                    <th>Supplier</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>VAT</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTable">
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

    <!-- Hidden iframe for printing -->
    <!-- 
        This hidden iframe is used as a container for the print preview,
        allowing for a clean print layout without affecting the main page
    -->
    <iframe id="printFrame" style="display: none;"></iframe>

    <!-- Include the print scripts -->
    <script src="../printinvoice/scripts.js"></script>

    <script>
        // Function to open invoice details in a new view
        function openInvoice(invoiceId) {
            $.get('invoice_view.php', { id: invoiceId }, function(response) {
                $('#dynamicContent').html(response);
            });
        }

        $('#addnewinvoice-link').on('click', function(e) {
        e.preventDefault();
        $.get('add_invoice_form.php', function(response) {
            $('#dynamicContent').html(response);
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<!-- Add this script for sidebar active state and profile dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should open the add part form
    if (sessionStorage.getItem('openAddInvoiceForm') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openAddInvoiceForm');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('add_invoice_form.php', function(response) {
                $('#dynamicContent').html(response);
            });
        }, 100);
    }

    // Check if we have a stored invoice ID to view
    const selectedInvoiceId = sessionStorage.getItem('selectedInvoiceId');
    if (selectedInvoiceId) {
        // Clear the stored ID
        sessionStorage.removeItem('selectedInvoiceId');
        // Load the invoice view
        $.get('invoice_view.php', { id: selectedInvoiceId }, function(response) {
            $('#dynamicContent').html(response);
        });
    }
});
    
document.addEventListener('DOMContentLoaded', function() {
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
        profileDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target)) {
                const dropdownMenu = profileDropdown.nextElementSibling;
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
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

<?php include '../../includes/about_modal.php'; ?>

<?php include '../../includes/backup_modal.php'; ?>

  </body>
  <!-- [Body] end -->
</html>
