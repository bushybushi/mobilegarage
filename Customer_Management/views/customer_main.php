<?php
// UserManagementMain.php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/customer_model.php';

// SQL query to fetch all customers with their related information
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
        addresses.Address, phonenumbers.nr, emails.Emails 
        FROM customers 
        JOIN addresses ON customers.CustomerID = addresses.CustomerID 
        JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
        JOIN emails ON customers.CustomerID = emails.CustomerID";

// Get total number of customers
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$customersPerPage = 10;
$totalPages = ceil($totalCustomers / $customersPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $customersPerPage;

// Modify the main query to include LIMIT and OFFSET
$sql .= " ORDER BY customers.FirstName ASC LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $customersPerPage, PDO::PARAM_INT);
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
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Customer Count and Action Buttons -->
            <div class="title-container d-flex justify-content-between align-items-center">
                <!-- Customer Count Display -->
                <div>
                    Total: <?php echo count($result); ?> Customers
                </div>
                <!-- Action Buttons -->
                <div class="d-flex">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">Name</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Name')">Name</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Email')">Email</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Phone')">Phone</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Address')">Address</a></li>
                        </ul>
                    </div>

                    <!-- Print Button -->
                    <button class="btn btn-success mr-2" style="width: 100px;" type="button" data-toggle="modal" data-target="#printModal">
                        Print <i class="ti ti-printer"></i>
                    </button>
                    <!-- Add New Customer Button -->
                    <button href="#" id="addnewcustomer-link" type="button" class="btn btn-primary" style="width: 100px;">Add 
                        <span>
                            <i class="ti ti-plus"></i>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Customer Table -->
            <table class="table table-striped">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <th style="display: none;">ID</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th>Address</th>
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
                            <td style="display: none;"><?php echo htmlspecialchars($row['CustomerID']); ?></td>
                            <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                            <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['Emails']); ?></td>
                            <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['nr']); ?></td>
                            <td onclick="openForm('<?php echo $row['CustomerID']; ?>')"><?php echo htmlspecialchars($row['Address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Add empty rows to maintain table size
                    $emptyRows = $customersPerPage - $rowCount;
                    for ($i = 0; $i < $emptyRows; $i++): 
                    ?>
                        <tr class="empty-row">
                            <td style="display: none;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

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
    </div>

    <!-- Print Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Print Customers</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Search and Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
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

                    <!-- Print Options -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div id="selectionCount" class="text-muted">0 customer(s) selected</div>
                                <div>
                                    <button class="btn btn-primary mr-2" style="width: 120px;" onclick="printAllCustomers()">Print All</button>
                                    <button class="btn btn-success" style="width: 120px;" onclick="printSelectedCustomers()">Print Selected</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="printSelectAll"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody id="printCustomersTable">
                                <?php foreach ($result as $row): ?>
                                    <tr data-customer-id="<?php echo htmlspecialchars($row['CustomerID']); ?>">
                                        <td><input type="checkbox" class="print-customer-select"></td>
                                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Emails']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nr']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Print Modal Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $customersPerPage, $totalCustomers); ?> of <?php echo $totalCustomers; ?> customers
                        </div>
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

    <!-- Add print iframe (hidden) -->
    <iframe id="printFrame" style="display: none;"></iframe>

</body>
</html> 
