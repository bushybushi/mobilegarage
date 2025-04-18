<?php
// UserManagementMain.php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/user_model.php';
require_once '../../UserAccess/protect.php';
protectAdminPage(); // This ensures only admins can access this section

// SQL query to fetch all users
$sql = "SELECT username, email, admin FROM users";

// Get total number of users
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$usersPerPage = 10;
$totalPages = ceil($totalUsers / $usersPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $usersPerPage;

// Modify the main query to include LIMIT and OFFSET
$sql .= " ORDER BY username ASC LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch all results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<link rel="stylesheet" href="styles.css" />
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
    <title>User Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../assets/scripts.js"></script>

</head>

<body>
    <!-- Main Content Container -->

        <div class="form-container">
            <!-- Title Bar with User Count and Action Buttons -->
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <!-- User Count Display -->
                 <div class="mb-2 mb-md-0">
                    <h2 class="mb-0">User Management</h2>
                    <small class="text-muted">Total: <?php echo count($result); ?> Users</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-2 mb-2 mb-md-0">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">Username</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Username')">Username</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Email')">Email</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateSort('Admin')">Admin Status</a></li>
                        </ul>
                    </div>
                    <!-- Add New User Button -->
                    <button href="#" id="addnewuser-link" type="button" class="btn btn-primary" style="width: 100px;">Add 
                        <span>
                            <i class="ti ti-plus"></i>
                        </span>
                    </button>
                </div>
            </div>

            <!-- User Table -->
            <div class="table-responsive">
            <table class="table table-striped">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <th style="display: none;">Username</th>
                        <th>Role</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody>
                    <?php 
                    $rowCount = 0;
                    foreach ($result as $row): 
                        $rowCount++;
                    ?>
                        <tr>
                            <td style="display: none;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td onclick="openForm('<?php echo $row['username']; ?>')"><?php echo $row['admin'] ? 'Admin' : 'User'; ?></td>
                            <td onclick="openForm('<?php echo $row['username']; ?>')"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td onclick="openForm('<?php echo $row['username']; ?>')"><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Add empty rows to maintain table size
                    $emptyRows = $usersPerPage - $rowCount;
                    for ($i = 0; $i < $emptyRows; $i++): 
                    ?>
                        <tr class="empty-row">
                            <td style="display: none;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            </div>

            <!-- Pagination for the main table -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center main-pagination">
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<!-- Add this script for sidebar active state, profile dropdown, and auto-open add form -->
<script>
function openForm(username) {
    $.get('user_view.php', { id: username }, function(response) {
        $('#dynamicContent').html(response);
    });
}


document.addEventListener('DOMContentLoaded', function() {
    // Check if we should open the add customer form
    if (sessionStorage.getItem('openAddUserForm') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openAddUserForm');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('add_user_form.php', function(response) {
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


  </body>
  <!-- [Body] end -->
  
  <!-- Include the About Modal -->
  <?php include '../../includes/about_modal.php'; ?>

  <?php include '../../includes/backup_modal.php'; ?>
</html>
