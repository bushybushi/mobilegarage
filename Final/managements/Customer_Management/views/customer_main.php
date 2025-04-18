
<?php
// Required file includes for database connection, input sanitization, and models
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/customer_model.php';
require_once '../../UserAccess/protect.php';

// Session message handling for success/error notifications
if (isset($_GET['message'])) {
    $_SESSION['message'] = $_GET['message'];
    $_SESSION['message_type'] = 'success';
} elseif (isset($_GET['error'])) {
    $_SESSION['message'] = $_GET['error'];
    $_SESSION['message_type'] = 'error';
}

// Initialize customer management system
$customerMang = new customerManagement();

// Pagination configuration and parameters
$customersPerPage = 10; // Number of customers to display per page
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : null; // Optional filter parameter
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page number
$offset = ($currentPage - 1) * $customersPerPage; // Calculate offset for database query

// Calculate total number of pages based on customer count
$totalCustomers = $customerMang->getTotalCustomers($filter);
$totalPages = ceil($totalCustomers / $customersPerPage);

// Retrieve paginated customer data
$result = $customerMang->getPaginatedCustomers($customersPerPage, $offset, $filter);

// Clear session messages after they've been displayed
unset($_SESSION['message']);
unset($_SESSION['message_type']);

?>
<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Customer</title>
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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->
  <body>
    <!-- [ Pre-loader ] start - Loading animation while page loads -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start - Main navigation menu -->
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <!-- Logo and brand section -->
        <div class="m-header">
          <a href="../../dashboard.php" class="b-brand text-primary">
            <!-- Company logo -->
            <img src="../../../assets/images/logo.png" style="max-width: 12rem;" alt="" class="logo" />
          </a>
        </div>
        
        <!-- Main navigation items -->
        <div class="navbar-content">
          <ul class="pc-navbar">
            <!-- Main menu section header -->
            <li class="pc-item pc-caption">
              <label>MAIN MENU</label>
              <i class="ti ti-dashboard"></i>
            </li>
            
            <!-- Dashboard navigation item -->
            <li class="pc-item">
              <a href="../../dashboard.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                <span class="pc-mtext">Dashboard</span>
                </a>
            </li>

            <!-- Customers navigation item - Current section -->
            <li class="pc-item">
            <a href="customer_main.php"  class="pc-link">
                <span class="pc-micon"><i class="ti ti-users"></i></span>
                <span class="pc-mtext">Customers</span>
              </a>
            </li>
            
            <!-- Parts Management navigation item -->
            <li class="pc-item">
              <a href="../../Parts_Management/views/parts_main.php"  class="pc-link">
                <span class="pc-micon"><i class="ti ti-box"></i></span>
                <span class="pc-mtext">Parts</span>
              </a>
            </li>
            
            <!-- Job Cards Management navigation item -->
            <li class="pc-item">
              <a href="../../JobCard_Management/views/job_cards_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-folder"></i></span>
                <span class="pc-mtext">Jobs</span>
              </a>
            </li>

            <!-- Accounting Management navigation item -->
            <li class="pc-item">
              <a href="../../Accounting_Management/views/accounting_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-vocabulary"></i></span>
                <span class="pc-mtext">Accounting</span>
              </a>
            </li>
            
            <!-- Invoice Management navigation item -->
            <li class="pc-item">
              <a href="../../Invoice_Management/views/invoice_main.php"  class="pc-link">
                <span class="pc-micon"><i class="ti ti-receipt-2"></i></span>
                <span class="pc-mtext">Invoices</span>
              </a>
            </li>
            
            <!-- Logout navigation item - Fixed at bottom -->
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
    
    <!-- [ Header Topbar ] start - Main header with search and user profile -->
    <header class="pc-header">
      <div class="header-wrapper">
        <!-- [Mobile Media Block] start - Mobile responsive navigation -->
        <div class="me-auto pc-mob-drp">
          <ul class="list-unstyled">
            <!-- Mobile sidebar toggle button -->
            <li class="pc-h-item header-mobile-collapse">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            
            <!-- Mobile popup menu toggle -->
            <li class="pc-h-item pc-sidebar-popup">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            
            <!-- Mobile search dropdown -->
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
            
            <!-- Desktop search form -->
            <li class="pc-h-item d-none d-md-inline-flex">
            <form class="header-search" method="GET" action="customer_main.php">
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
        
        <!-- User profile section -->
        <div class="ms-auto">
          <ul class="list-unstyled">
            <!-- User profile dropdown -->
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
              <!-- User profile dropdown menu -->
              <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                <div class="dropdown-header">
                  <!-- Greeting message -->
                  <h4 style="text-align: center;" id="greeting-text">
                    Good Morning
                  </h4>
                  <hr />
                  
                  <!-- User Management link -->
                  <a href="../../User_Management/views/user_main.php" id="users-link" class="dropdown-item">
                    <i class="ti ti-user"></i>
                    <span>User Management</span>
                  </a>
                  
                  <!-- Backup and Restore option -->
                  <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#backupRestoreModal">
                    <i class="ti ti-cloud-upload"></i>
                    <span>Backup and Restore</span>
                  </a>
                  
                  <!-- About information -->
                  <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#aboutModal">
                    <i class="ti ti-info-circle"></i>
                    <span>About</span>
                  </a>
                  
                  <!-- Logout option -->
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
    <title>Customer Management</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- JavaScript dependencies - Load in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Custom utility scripts -->
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/form-functions.js"></script>
    <script src="../assets/js/car-functions.js"></script>
    <script src="../assets/js/customer-functions.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/print.js"></script>
</head>

<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Customer Count and Action Buttons -->
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div class="mb-2 mb-md-0">
                    <h2 class="mb-0">Customers</h2>
                    <!-- Customer Count Display -->
                    <small class="text-muted">Total: <?php echo $totalCustomers; ?> Customers</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-2 mb-2 mb-md-0">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">Name</span>
                        </button>
                        <ul class="dropdown-menu sort-dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Name')">Name</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Email')">Email</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Phone')">Phone</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Address')">Address</a></li>
                        </ul>
                    </div>

                    <!-- Print and Add Buttons Container -->
                    <div class="d-flex gap-2">
                        <!-- Print Button - Opens print modal -->
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#printModal" style="width: 100px;">
                            Print <i class="fas fa-print"></i>
                        </button>
                        <!-- Add New Customer Button - Opens add customer form -->
                        <button href="#" id="addnewcustomer-link" type="button" class="btn btn-primary" style="width: 100px;">
                            Add <span><i class="fas fa-plus"></i></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customer Table - Displays customer information in a responsive table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <!-- Table Header - Column definitions -->
                    <thead>
                        <tr>
                            <th style="display: none;">ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <!-- Table Body - Customer data rows -->
                    <tbody>
                        <?php 
                        $rowCount = 0;
                        foreach ($result as $row): 
                            $rowCount++;
                        ?>
                            <tr>
                                <!-- Hidden Customer ID for reference -->
                                <td style="display: none;"><?php echo htmlspecialchars($row['CustomerID']); ?></td>
                                <!-- Clickable cells that open customer details -->
                                <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['FirstName']); ?> <?php echo htmlspecialchars($row['LastName']); ?></td>
                                <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['Phone']); ?></td>
                                <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['Address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls - Only shown if there are multiple pages -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page Button - Disabled if on first page -->
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>&filter=<?php echo $filter?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next Page Button - Disabled if on last page -->
                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&filter=<?php echo $filter?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

    <!-- Print Modal - Dialog for printing customer information -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Print Customers</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Search and Filter Section - For filtering customers before printing -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <input type="text" id="printSearch" class="form-control" placeholder="Search customers...">
                        </div>
                        <div class="col-md-6">
                            <select id="printFilter" class="form-control">
                                <option value="all">All Customers</option>
                                <option value="name">By Name</option>
                                <option value="email">By Email</option>
                                <option value="phone">By Phone</option>
                            </select>
                        </div>
                    </div>

                    <!-- Print Options - Configuration for print output -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Customer Selection Counter -->
                                <div id="selectionCount" class="text-muted">0 customer(s) selected</div>
                                <!-- Print Action Buttons -->
                                <div>
                                    <button class="btn btn-primary mr-2" style="width: 120px;" onclick="printAllCustomers()">Print All</button>
                                    <button class="btn btn-success" onclick="printSelectedCustomers()">Print Selected</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Table - For selecting customers to print -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped">
                            <!-- Table Header with Checkbox for Select All -->
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="printSelectAll"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <!-- Table Body with Customer Data and Selection Checkboxes -->
                            <tbody id="printCustomersTable">
                                <?php foreach ($result as $row): ?>
                                    <tr data-customer-id="<?php echo htmlspecialchars($row['CustomerID']); ?>">
                                        <td><input type="checkbox" class="print-customer-select"></td>
                                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Print Modal Pagination - Navigation for print modal table -->
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <nav>
                            <ul class="pagination modal-pagination mb-0">
                                <!-- Previous Page Button -->
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadPrintModalPage(<?php echo $currentPage - 1; ?>); return false;" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="#" onclick="loadPrintModalPage(<?php echo $i; ?>); return false;"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next Page Button -->
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

    <!-- Hidden iframe for printing functionality -->
    <iframe id="printFrame" style="display: none;"></iframe>

</body>
</html> 
</div>

    </div>
    <!-- [ Main Content ] end -->

    <!-- Required JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../../assets/js/plugins/popper.min.js"></script>
    <script src="../../../assets/js/plugins/simplebar.min.js"></script>
    <script src="../../../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../../../assets/js/icon/custom-font.js"></script>
    <script src="../../../assets/js/script.js"></script>
    <script src="../../../assets/js/theme.js"></script>
    <script src="../../../assets/js/plugins/feather.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts for sidebar active state, profile dropdown, and auto-open add form -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we should open the add customer form
        if (sessionStorage.getItem('openAddCustomerForm') === 'true') {
            // Clear the flag immediately
            sessionStorage.removeItem('openAddCustomerForm');
            // Add a small delay to ensure the page is fully loaded
            setTimeout(function() {
                $.get('add_customer_form.php', function(response) {
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
            $(profileDropdown).dropdown();
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileDropdown.contains(e.target)) {
                    $(profileDropdown).dropdown('hide');
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
