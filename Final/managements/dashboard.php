<?php
// Authentication check to ensure only logged-in users can access the dashboard
require_once 'UserAccess/auth_check.php';
checkAuth();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once '../config/db_connection.php';
?>
<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>Dashboard</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon.png"/>

    <!-- [Fonts and Icons] -->
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />
    <!-- Various icon libraries -->
    <link rel="stylesheet" href="../assets/fonts/phosphor/duotone/style.css" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    
    <!-- [CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    <link rel="stylesheet" href="Customer_Management/assets/styles.css" />
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- [JavaScript Libraries] -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
  </head>
  <!-- [Head] end -->

  <!-- [Body] Start -->
  <body>
    <!-- [Pre-loader] -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [Pre-loader] End -->

    <!-- [Sidebar Menu] -->
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header">
          <a href="dashboard.php" class="b-brand text-primary">
            <img src="../assets/images/logo.png" style="max-width: 12rem;" alt="" class="logo" />
          </a>
        </div>
        <div class="navbar-content">
          <ul class="pc-navbar">
            <!-- Main Menu Items -->
            <li class="pc-item pc-caption">
              <label>MAIN MENU</label>
              <i class="ti ti-dashboard"></i>
            </li>
            <!-- Dashboard Link -->
            <li class="pc-item">
              <a href="dashboard.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                <span class="pc-mtext">Dashboard</span>
              </a>
            </li>
            <!-- Customer Management Link -->
            <li class="pc-item">
              <a href="Customer_Management/views/customer_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-users"></i></span>
                <span class="pc-mtext">Customers</span>
              </a>
            </li>
            <!-- Parts Management Link -->
            <li class="pc-item">
              <a href="Parts_Management/views/parts_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-box"></i></span>
                <span class="pc-mtext">Parts</span>
              </a>
            </li>
            <!-- Job Cards Management Link -->
            <li class="pc-item">
              <a href="JobCard_Management/views/job_cards_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-folder"></i></span>
                <span class="pc-mtext">Jobs</span>
              </a>
            </li>
            <!-- Accounting Management Link -->
            <li class="pc-item">
              <a href="Accounting_Management/views/accounting_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-vocabulary"></i></span>
                <span class="pc-mtext">Accounting</span>
              </a>
            </li>
            <!-- Invoice Management Link -->
            <li class="pc-item">
              <a href="Invoice_Management/views/invoice_main.php" class="pc-link">
                <span class="pc-micon"><i class="ti ti-receipt-2"></i></span>
                <span class="pc-mtext">Invoices</span>
              </a>
            </li>
            <!-- Logout Link -->
            <li class="pc-item">
              <a href="UserAccess/process.php?action=logout" class="pc-link" style="margin-bottom:3rem;position: absolute;bottom: 0;">
                <span class="pc-micon"><i class="ti ti-logout"></i></span>
                <span class="pc-mtext">Log Out</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- [Sidebar Menu] end -->

    <!-- [Header Topbar] -->
    <header class="pc-header">
      <div class="header-wrapper">
        <!-- [Mobile Media Block] -->
        <div class="me-auto pc-mob-drp">
          <ul class="list-unstyled">
            <!-- Mobile Menu Toggle -->
            <li class="pc-h-item header-mobile-collapse">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <!-- Mobile Sidebar Popup -->
            <li class="pc-h-item pc-sidebar-popup">
              <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <!-- Mobile Search -->
            <li class="dropdown pc-h-item d-inline-flex d-md-none">
              <a class="pc-head-link head-link-secondary dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
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
            <!-- Desktop Search -->
            <li class="pc-h-item d-none d-md-inline-flex">
              <form class="header-search">
                <i data-feather="search" class="icon-search"></i>
                <input type="search" class="form-control" id="searchInput" placeholder="Search here" autocomplete="off" />
                <button class="btn btn-light-secondary btn-search"><i class="ti ti-adjustments-horizontal"></i></button>
              </form>
            </li>
          </ul>
        </div>
        <!-- [Mobile Media Block end] -->

        <!-- User Profile Dropdown -->
        <div class="ms-auto">
          <ul class="list-unstyled">
            <li class="dropdown pc-h-item header-user-profile">
              <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="../assets/images/profile.png" alt="user-image" class="user-avtar" />
                <span>
                  <i class="ti ti-settings"></i>
                </span>
              </a>
              <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                <div class="dropdown-header">
                  <h4 style="text-align: center;" id="greeting-text">Good Morning</h4>
                  <hr />
                  <!-- User Management Link -->
                  <a href="User_Management/views/user_main.php" id="users-link" class="dropdown-item">
                    <i class="ti ti-user"></i>
                    <span>User Management</span>
                  </a>
                  <!-- Backup and Restore Link -->
                  <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#backupRestoreModal">
                    <i class="ti ti-cloud-upload"></i>
                    <span>Backup and Restore</span>
                  </a>
                  <!-- About Link -->
                  <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#aboutModal">
                    <i class="ti ti-info-circle"></i>
                    <span>About</span>
                  </a>
                  <!-- Logout Link -->
                  <a href="UserAccess/process.php?action=logout" class="dropdown-item">
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
    <!-- [Header] end -->

    <!-- [Main Content] start -->
    <div class="pc-container">
      <!-- Display system messages -->
      <?php
      require_once 'includes/messages.php';
      showMessages();
      ?>
      
      <!-- Dynamic content container -->
      <div id="dynamicContent">
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Dashboard</title>
          <!-- Additional CSS and JavaScript includes -->
          <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
          <!-- Dashboard specific styles -->
          <style>
            /* CSS variables for consistent theming */
            :root {
              --primary-color: #4F46E5;
              --primary-light: #818CF8;
              --secondary-color: #64748B;
              --success-color: #059669;
              --danger-color: #DC2626;
              --background-color: #F8FAFC;
              --card-background: #FFFFFF;
              --transition-speed: 0.2s;
              --border-radius: 16px;
              --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            }
            
            /* General styles */
            * {
              transition: all var(--transition-speed) ease-in-out;
            }
            
            body {
              background-color: var(--background-color);
              color: #1F2937;
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            }

            /* Button focus styles */
            .btn.focus, .btn:focus {
              outline: none;
              box-shadow: none;
            }
            .btn-primary:not(:disabled):not(.disabled).active:focus, .btn-primary:not(:disabled):not(.disabled):active:focus, .show>.btn-primary.dropdown-toggle:focus {
              box-shadow: none;
            }

            /* Widget container styles */
            .widget-container{
              background: var(--card-background);
              border-radius: var(--border-radius);
              padding: 24px;
              box-shadow: var(--box-shadow);
              height: 100%;
            }
            
            /* Shortcuts and stats card styles */
            .shortcuts-container,
            .stats-card {
              background: var(--card-background);
              border-radius: var(--border-radius);
              padding: 24px;
              box-shadow: var(--box-shadow);
              margin-bottom: 16px;
            }

            /* Stats card specific styles */
            .stats-card {
              background: white;
              border-radius: var(--border-radius);
              padding: 24px;
              box-shadow: var(--box-shadow);
              height: 300px;
              margin-bottom: 16px;
            }

            /* Stats title and value styles */
            .stats-title {
              color: #6c757d;
              font-size: 0.9rem;
              font-weight: 500;
              margin-bottom: 4px;
            }

            .stats-value {
              font-size: 24px;
              font-weight: 700;
              color:rgb(0, 0, 0);
              margin: 4px 0;
              letter-spacing: -0.5px;
            }

            /* Trend indicators styles */
            .trend-up,
            .trend-down {
              font-weight: 500;
              font-size: 0.8rem;
              display: flex;
              align-items: center;
              gap: 4px;
              color: #6c757d;
            }

            /* Stats icon styles */
            .stats-icon {
              width: 44px;
              height: 44px;
              border-radius: 10px;
              background: #e9ecef;
              color: #007bff;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 1.25rem;
            }

            /* Shortcuts styles */
            .shortcuts-container {
              background: white;
              border-radius: var(--border-radius);
              padding: 24px;
              box-shadow: var(--box-shadow);
              margin-bottom: 16px;
              color: #007bff;
              transition: all 0.3s ease;
              border: 2px solid #e9ecef;
              display: flex;
              flex-direction: column;
              height: 300px;
            }

            /* Shortcut title styles */
            .shortcuts-title {
              color: #6c757d;
              font-size: 0.9rem;
              font-weight: 600;
              margin-bottom: 12px;
              flex-shrink: 0;
            }

            /* Shortcut content styles */
            .shortcuts-content {
              flex-grow: 1;
              padding-right: 4px;
            }

            /* Shortcut item styles */
            .shortcut-item {
              display: flex;
              align-items: center;
              padding: 12px;
              margin-bottom: 8px;
              border-radius: 10px;
              cursor: pointer;
              transition: all 0.2s ease;
              height: 48px;
            }

            /* Shortcut hover effects */
            .shortcut-item:hover {
              background: #e9ecef;
              transform: translateX(4px);
            }

            /* Shortcut icon styles */
            .shortcut-icon {
              width: 36px;
              height: 36px;
              border-radius: 8px;
              background: #e9ecef;
              display: flex;
              align-items: center;
              justify-content: center;
              margin-right: 12px;
              color: #007bff;
              font-size: 1.1rem;
            }

            /* Shortcut title styles */
            .shortcut-title {
              color:rgb(0, 0, 0);
              font-weight: 500;
              font-size: 0.9rem;
              margin-bottom: 0;
            }

            /* Placeholder styles */
            .shortcut-placeholder {
              width: 100px;
              height: 6px;
              background-color: #E5E7EB;
              border-radius: 4px;
            }

            .shortcut-placeholder.short {
              width: 60px;
            }

            /* Responsive styles */
            @media (max-width: 768px) {
              .widget-container,
              .stats-card {
                padding: 16px;
                margin-bottom: 12px;
                height: auto;
              }
              
              .calendar-day {
                padding: 6px;
                font-size: 0.875rem;
              }
            }

            /* Calendar container styles */
            .calendar-container {
              height: 100%;
              display: flex;
              flex-direction: column;
            }

            #calendar {
              flex-grow: 1;
              height: 100%;
            }

            /* Column layout styles */
            .col-md-4 {
              display: flex;
              flex-direction: column;
              height: 100%;
            }

            .col-md-8 {
              height: 100%;
            }
          </style>
        </head>
        <body>
          <!-- Main dashboard grid -->
          <div class="row g-4">
            <!-- Left Column: Shortcuts, Net Income, and Bookings -->
            <div class="col-md-4">
              <!-- Shortcuts Section -->
              <div class="shortcuts-container mb-4">
                <div class="shortcuts-title">Shortcuts</div>
                
                <!-- Shortcuts List -->
                <div class="shortcuts-content">
                  <!-- Add Customer Shortcut -->
                  <div class="shortcut-item" onclick="redirectToAddCustomer()">
                    <div class="shortcut-icon">
                      <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="shortcut-content">
                      <div class="shortcut-title">Add Customer</div>
                    </div>
                  </div>
                  <!-- Add Job Card Shortcut -->
                  <div class="shortcut-item" onclick="redirectToAddJobCard()">
                    <div class="shortcut-icon">
                      <i class="fas fa-folder-plus"></i>
                    </div>
                    <div class="shortcut-content">
                      <div class="shortcut-title">Add Job Card</div>
                    </div>
                  </div>
                  <!-- Add Part Shortcut -->
                  <div class="shortcut-item" onclick="redirectToAddPart()">
                    <div class="shortcut-icon">
                      <i class="fas fa-box"></i>
                    </div>
                    <div class="shortcut-content">
                      <div class="shortcut-title">Add Part</div>
                    </div>
                  </div>
                  <!-- Add Invoice Shortcut -->
                  <div class="shortcut-item" onclick="redirectToAddInvoice()">
                    <div class="shortcut-icon">
                      <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="shortcut-content">
                      <div class="shortcut-title">Add Invoice</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Net Income Stats -->
              <div class="stats-card mb-4">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="stats-title">New Net Income</div>
                    <div class="stats-value">€<?php
                      try {
                        // Calculate current week's income
                        $FDayCWeek = new DateTime();
                        $FDayCWeek->modify('monday this week');
                        $FDayCWeek->setTime(0, 0, 0);

                        $LDayCWeek = new DateTime();
                        $LDayCWeek->modify('sunday this week');
                        $LDayCWeek->setTime(23, 59, 59);

                        $startDate = $FDayCWeek->format("Y-m-d H:i:s");
                        $endDate = $LDayCWeek->format("Y-m-d H:i:s");

                        $sql = "SELECT COALESCE(SUM(i.Total), 0) as Income
                                FROM jobcards j
                                INNER JOIN invoicejob ij ON j.JobID = ij.JobID
                                INNER JOIN invoices i ON ij.InvoiceID = i.InvoiceID
                                WHERE j.DateFinish BETWEEN :startDate AND :endDate";

                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':startDate', $startDate);
                        $stmt->bindParam(':endDate', $endDate);
                        $stmt->execute();
                        $IncomeCWeek = $stmt->fetchColumn();

                        // Calculate last week's income for comparison
                        $FDayLWeek = new DateTime();
                        $FDayLWeek->modify('monday last week');
                        $FDayLWeek->setTime(0, 0, 0);

                        $LDayLWeek = new DateTime();
                        $LDayLWeek->modify('sunday last week');
                        $LDayLWeek->setTime(23, 59, 59);

                        $startDate = $FDayLWeek->format("Y-m-d H:i:s");
                        $endDate = $LDayLWeek->format("Y-m-d H:i:s");

                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':startDate', $startDate);
                        $stmt->bindParam(':endDate', $endDate);
                        $stmt->execute();
                        $IncomeLWeek = $stmt->fetchColumn();

                        // Calculate percentage change
                        if ($IncomeLWeek != 0) {
                          $IncomePer = number_format((($IncomeCWeek - $IncomeLWeek)/$IncomeLWeek) * 100, 2);
                        } else {
                          $IncomePer = number_format(0, 2);
                        }
                      } catch (PDOException $e) {
                        // Error handling
                        $IncomeCWeek = 0;
                        $IncomeLWeek = 0;
                        $IncomePer = 0;
                      }
                      echo number_format($IncomeCWeek, 2);
                    ?></div>
                    <div class="trend-down">
                      <i class="fas fa-arrow-down"></i>
                      <span><?php echo $IncomePer; ?>% from last week</span>
                    </div>
                  </div>
                  <div class="stats-icon">
                    <i class="fas fa-euro-sign"></i>
                  </div>
                </div>
             
                <hr>
             
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="stats-title">Total Jobs</div>
                    <div class="stats-value"><?php
                      require_once 'JobCard_Management/config/db_connection.php';
                      $sql = "SELECT COUNT(*) as total FROM jobcards 
                              WHERE DateCall >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                      $stmt = $pdo->prepare($sql);
                      $stmt->execute();
                      $result = $stmt->fetch(PDO::FETCH_ASSOC);
                      echo $result['total'];
                    ?></div>
                    <div class="trend-up">
                      <i class="fas fa-arrow-up"></i>
                      <span>Weekly Jobs</span>
                    </div>
                  </div>
                  <div class="stats-icon">
                    <i class="fas fa-calendar"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column: Calendar -->
            <?php include 'includes/calendar.php'; ?>
          </div>
        </div>
      </div>

      <!-- Required JavaScript Libraries -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html> 

    </div>
    <!-- [Main Content] end -->

    <!-- Required JavaScript -->
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/icon/custom-font.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar and Bootstrap Tooltip JavaScript -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>

    <!-- Font and Preset Change Scripts -->
    <script>
      font_change('Inter');
    </script>
    <script>
      preset_change('preset-1');
    </script>

    <!-- Sidebar Active State Script -->
    <script>
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

        // Special handling for dashboard link
        if (currentPath.endsWith('dashboard.php') || currentPath.endsWith('/')) {
            const dashboardLink = document.querySelector('a[href="dashboard.php"]');
            if (dashboardLink) {
                dashboardLink.classList.add('active');
                dashboardLink.closest('.pc-item').classList.add('active');
            }
        }
    });

    // Dynamic Greeting Script
    document.addEventListener('DOMContentLoaded', function() {
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

    // Backup Form Handling Script
    $(document).ready(function () {
      $('#backupForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $(this).find('button');
        const $result = $('#backupResult');
        $btn.prop('disabled', true).text('Backing up...');

        $.ajax({
          url: '/MGAdmin2025/managements/includes/backup.php',
          type: 'POST',
          success: function (response) {
            $result.text(response).css('color', '#90ee90');
          },
          error: function (xhr, status, error) {
            $result.text("Backup failed: " + error).css('color', '#ffcccb');
          },
          complete: function () {
            $btn.prop('disabled', false).text('Backup');
          }
        });
      });
    });

    // Restore Form Handling Script
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

    <!-- Shortcut Redirect Scripts -->
    <script>
    function redirectToAddCustomer() {
        sessionStorage.setItem('openAddCustomerForm', 'true');
        window.location.href = 'Customer_Management/views/customer_main.php';
    }

    function redirectToAddPart() {
        sessionStorage.setItem('openAddPartForm', 'true');
        window.location.href = 'Parts_Management/views/parts_main.php';
    }

    function redirectToAddJobCard() {
        sessionStorage.setItem('openJobCardForm', 'true');
        window.location.href = 'JobCard_Management/views/job_cards_main.php';
    }

    function redirectToAddInvoice() {
        sessionStorage.setItem('openAccountingDetails', 'true');
        window.location.href = 'Accounting_Management/views/accounting_main.php';
    }

    function redirectToAddInvoice() {
        sessionStorage.setItem('openAddInvoiceForm', 'true');
        window.location.href = 'Invoice_Management/views/invoice_main.php';
    }
    </script>

  </body>
  <!-- [Body] end -->
  
  <!-- Include Modals -->
  <?php include 'includes/about_modal.php'; ?>
  <?php include 'includes/backup_modal.php'; ?>
</html>
