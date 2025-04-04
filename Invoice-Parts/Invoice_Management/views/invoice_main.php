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

require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/invoice_model.php';

session_start();

// Get sort parameter from URL, default to date_desc
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Pagination settings
$items_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count of invoices
$count_sql = "SELECT COUNT(DISTINCT i.InvoiceID) as total FROM Invoices i";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute();
$total_invoices = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_invoices / $items_per_page);

// SQL query to fetch all invoices with their related information
$sql = "SELECT i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Total, i.Vat,
               s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail
        FROM Invoices i
        LEFT JOIN PartsSupply ps ON i.InvoiceID = ps.InvoiceID
        LEFT JOIN Parts p ON ps.PartID = p.PartID
        LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
        GROUP BY i.InvoiceID";

// Add ORDER BY clause based on sort parameter
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

// Add LIMIT and OFFSET for pagination
$sql .= " LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

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
            <div class="title-container d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Invoices</h2>
                    <small class="text-muted">Total: <?php echo count($result); ?> Invoices</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-3">
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
                               href="?sort=invoice_number&page=<?php echo $current_page; ?>">Invoice Number</a>
                            <a class="dropdown-item <?php echo $sortBy === 'date_asc' ? 'active' : ''; ?>" 
                               href="?sort=date_asc&page=<?php echo $current_page; ?>">Date Created (Oldest)</a>
                            <a class="dropdown-item <?php echo $sortBy === 'date_desc' ? 'active' : ''; ?>" 
                               href="?sort=date_desc&page=<?php echo $current_page; ?>">Date Created (Latest)</a>
                            <a class="dropdown-item <?php echo $sortBy === 'supplier' ? 'active' : ''; ?>" 
                               href="?sort=supplier&page=<?php echo $current_page; ?>">Supplier</a>
                        </div>
                    </div>
                    <!-- Print Button -->
                    <button type="button" class="btn btn-success mr-3" data-toggle="modal" data-target="#printModal">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <!-- Add New Invoice Button -->
                    <a href="add_invoice_form.php" id="addnewinvoice-link" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add
                    </a>
                </div>
            </div>

            <!-- Invoices Table -->
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

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sortBy; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
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

                    <!-- Selection Count -->
                    <div class="mb-3">
                        <span id="selectionCount">0 invoice(s) selected</span>
                    </div>

                    <!-- Print Buttons -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" style="min-width: 150px;" onclick="printAllInvoices()">Print All</button>
                        <button type="button" class="btn btn-success" style="min-width: 150px;" onclick="printSelectedInvoices()">Print Selected</button>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table">
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
            window.location.href = 'invoice_view.php?id=' + invoiceId;
        }

        // Auto-hide message when DOM is loaded
        // This creates a smooth fade-out effect for notification messages
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
</body>
</html> 