<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';

$startDate = $_GET['startDate'] ?? NULL;
$endDate = $_GET['endDate'] ?? NULL;

// Pagination parameters
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// SQL query to fetch all parts with related information
$sql = "SELECT p.PartID as PartID, p.DateCreated as DateCreated, 
            p.PartDesc as PartDesc, 
			p.PiecesPurch * p.PricePerPiece as Expenses,
			s.Name as Supplier
        FROM parts p
		LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID";

if ($startDate != NULL && $endDate != NULL) {
    // Transform dates into real dates
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    $sql .= ' WHERE p.DateCreated BETWEEN :startDate AND :endDate';
}

$sql .= " ORDER BY p.DateCreated DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
if ($startDate != NULL && $endDate != NULL) {
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
}
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM parts p";
if ($startDate != NULL && $endDate != NULL) {
    $countSql .= ' WHERE p.DateCreated BETWEEN :startDate AND :endDate';
}

$countStmt = $pdo->prepare($countSql);
if ($startDate != NULL && $endDate != NULL) {
    $countStmt->bindParam(':startDate', $startDate);
    $countStmt->bindParam(':endDate', $endDate);
}
$countStmt->execute();
$totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

session_start();

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Calculate total profit
$totalCosts = 0;
foreach ($result as $row) {
    $expenses = $row['Expenses'] ?: 0;
    $totalCosts += $expenses;
}
?>

<style>
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

    .popup-content p {
        margin: 0;
        font-weight: bold;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }
    
    /* Table styles */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
        font-weight: 600;
        color: #495057;
    }
    
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: #f1f8ff;
    }
    
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    
    .table td:first-child {
        width: 40px;
        text-align: center;
    }
    
    .table td:first-child i {
        color: #6c757d;
        font-size: 1.2rem;
    }
    
    .badge {
        padding: 6px 10px;
        font-weight: 500;
        border-radius: 4px;
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }

    /* Custom button styles */
    #filterButton {
        background-color: #007bff; /* Bootstrap primary blue */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    #filterButton:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    #printButton {
        background-color: #28a745; /* Bootstrap success green */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    #printButton:hover {
        background-color: #218838; /* Darker green on hover */
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
        }
        .no-print {
            display: none;
        }
    }

    .print-section {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100vh;
        background: white;
        z-index: 9999;
        padding: 20px;
        overflow: auto;
        pointer-events: none;
    }

    @media screen {
        .print-section {
            pointer-events: none;
        }
        .print-section * {
            pointer-events: none;
        }
    }

    #printFrame {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 0;
        height: 0;
        border: none;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Management</title>
    
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/scripts.js" defer></script>
	
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
    <!-- Add iframe for printing -->
    <iframe id="printFrame"></iframe>

    <div class="pc-container3">
        <div class="form-container">
            <div class="title-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div class="mb-2 mb-md-0">
                    <h2 class="mb-0">Parts Details</h2>
                    <small class="text-muted">Total: <?php echo count($result); ?> Parts</small>
                </div>
                <div class="d-flex flex-wrap">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo $startDate; ?>" required>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo $endDate; ?>" required>
                    </div>
                    <div class="d-flex align-items-end">
                        <button type="button" id="filterButton" class="btn">Filter</button>
                        <button type="button" id="printButton" class="btn ml-2">Print</button>
                    </div>
                </div>
            </div>

            
            <div class="total-profit p-3">
                Total Costs: <?php echo number_format($totalCosts, 2); ?>
            </div>

            <div class="table-responsive">
            <table class="table table-striped" id="PartsTable">
                <thead>
                    <tr>
                        <th>Part</th>
						<th>Supplier</th>
                        <th>Date Created</th>
                        <th>Expenses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr onclick="openForm(<?php echo $row['PartID']; ?>)">
                            <td><?php echo htmlspecialchars($row['PartDesc']); ?></td>
                            <td><?php echo htmlspecialchars($row['Supplier']); ?></td>
                            <td>
                                <?php 
                                    $rowDate = !empty($row['DateCreated']) ? date('d/m/Y', strtotime($row['DateCreated'])) : 'N/A';
                                    echo $rowDate;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['Expenses'] ?: 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>&startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

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
    document.getElementById('filterButton').addEventListener('click', function() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;

        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert('Start Date cannot be greater than End Date.');
            return;
        }

        var url = new URL(window.location.href);
        var params = new URLSearchParams(url.search);
        if (startDate) params.set('startDate', startDate);
        if (endDate) params.set('endDate', endDate);

        url.search = params.toString();
        window.location.href = url.toString();
    });

    // Print functionality
    document.getElementById('printButton').addEventListener('click', function() {
        try {
            const table = document.getElementById('PartsTable');
            if (!table) {
                console.error('Parts table not found');
                return;
            }

            const tbody = table.getElementsByTagName('tbody')[0];
            if (!tbody) {
                console.error('Table body not found');
                return;
            }

            const rows = tbody.getElementsByTagName('tr');
            console.log('Found rows:', rows.length);
            
            let totalCost = 0;
            
            // Create the print content
            let printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                        }
                        .header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-bottom: 30px;
                             border-bottom: 2px solid #ddd;
                        }
                        .logo {
                            max-height: 80px;
                        }
                        .title {
                            text-align: right;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #f8f9fa;
                        }
                        .total-profit {
                            text-align: right;
                            font-weight: bold;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div>
                            <img src="../assets/logo.png" alt="Logo" style="max-height: 80px;">
                        </div>
                        <div class="title">
                            <h1>Parts</h1>
                            <p>Total Parts: ${rows.length}</p>
                            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                        </div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th>Supplier</th>
                                <th>Date Created</th>
                                <th>Expenses</th>
                            </tr>
                        </thead>
                        <tbody>`;

            // Add table rows
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (!cells || cells.length === 0) continue;
                
                printContent += '<tr>';
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (!cell) continue;
                    
                    const cellText = cell.textContent ? cell.textContent.trim() : '';
                    printContent += `<td>${cellText}</td>`;
                    
                    // If this is the expenses column (last column)
                    if (j === cells.length - 1) {
                        const expenses = parseFloat(cellText.replace(/[^0-9.-]+/g, '')) || 0;
                        totalCost += expenses;
                    }
                }
                
                printContent += '</tr>';
            }

            // Complete the HTML content
            printContent += `
                        </tbody>
                    </table>
                    <div class="total-profit">
                        Total Costs: ${totalCost.toFixed(2)}
                    </div>
                </body>
                </html>`;

            // Get the iframe
            const frame = document.getElementById('printFrame');
            if (!frame) {
                console.error('Print frame not found');
                return;
            }
            
            // Write the content to the iframe
            frame.contentWindow.document.open();
            frame.contentWindow.document.write(printContent);
            frame.contentWindow.document.close();
            
            // Wait for images to load then print
            frame.contentWindow.onload = function() {
                frame.contentWindow.print();
            };
        } catch (error) {
            console.error('Error in print functionality:', error);
            alert('An error occurred while preparing the print preview. Please check the console for details.');
        }
    });

    setTimeout(function() {
        let popup = document.getElementById("customPopup");
        if (popup) {
            popup.style.animation = "fadeOut 0.5s ease-in-out";
            setTimeout(() => popup.remove(), 500);
        }
    }, 3000);

    // Open form functionality
function openForm(PartID) {
    // Store the part ID in session storage
    sessionStorage.setItem('openPartId', PartID);
    // Redirect to parts main page
    window.location.href = '../../Parts_Management/views/parts_main.php';
}

document.addEventListener('DOMContentLoaded', function() {
    // Remove any existing active classes first
    document.querySelectorAll('.pc-link.active, .pc-item.active').forEach(el => {
        el.classList.remove('active');
    });

    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.pc-navbar .pc-link');
    
    // Find the accounting link
    const accountingLink = Array.from(sidebarLinks).find(link => {
        const href = link.getAttribute('href');
        return href && href.includes('accounting');
    });

    if (accountingLink) {
        accountingLink.classList.add('active');
        const parentItem = accountingLink.closest('.pc-item');
        if (parentItem) {
            parentItem.classList.add('active');
        }
    }

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

<!-- Include the About Modal -->
  <?php include '../../includes/about_modal.php'; ?>
  

<!-- Include the Backup Modal -->
  <?php include '../../includes/backup_modal.php'; ?>
</html>