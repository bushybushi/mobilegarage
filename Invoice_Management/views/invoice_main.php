<?php
// Invoice Main View
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/invoice_model.php';

session_start();

// Get sort parameter from URL, default to date_desc
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

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

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
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
</style>

<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Customer Count and Action Buttons -->
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
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="?sort=invoice_number">Invoice Number</a></li>
                            <li><a class="dropdown-item" href="?sort=date_desc">Date Created (Latest)</a></li>
                            <li><a class="dropdown-item" href="?sort=date_asc">Date Created (Oldest)</a></li>
                            <li><a class="dropdown-item" href="?sort=supplier">Supplier</a></li>
                        </ul>
                    </div>

                    <!-- Print Button -->
                    <button type="button" id="printButton" class="btn btn-success mr-3">Print 
                        <span><i class="fas fa-print"></i></span>
                    </button>
                    <!-- Add Button -->
                    <button href="#" id="addnewinvoice-link" type="button" class="btn btn-primary">Add 
                        <span><i class="fas fa-plus"></i></span>
                    </button>
                </div>
            </div>

            <!-- Add message container -->
            <div id="messageContainer" class="mt-3"></div>

            <!-- Customer Table -->
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
    </div>

    <!-- JavaScript Functions -->
    <script>
        // Add Invoice button click handler
        document.getElementById('addnewinvoice-link').addEventListener('click', function() {
            window.location.href = 'add_invoice_form.php';
        });

        // Function to open invoice details
        function openInvoice(invoiceId) {
            window.location.href = 'invoice_view.php?id=' + invoiceId;
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
                                <option value="number">Invoice Number</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selection Count -->
                    <div class="mb-3">
                        <span id="selectionCount">0 invoice(s) selected</span>
                    </div>

                    <!-- Print Buttons -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" onclick="printAllInvoices()">Print All</button>
                        <button type="button" class="btn btn-success" onclick="printSelectedInvoices()">Print Selected</button>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="printSelectAll"></th>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                    <th>VAT</th>
                                </tr>
                            </thead>
                            <tbody id="printInvoicesTable">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden iframe for printing -->
    <iframe id="printFrame" style="display: none;"></iframe>

    <!-- Include the print scripts -->
    <script src="../printinvoice/scripts.js"></script>

    <script>
    // Connect print button to modal
    $(document).ready(function() {
        // When the print button is clicked
        $('#printButton').click(function() {
            // Clear any previous selections
            selectedInvoiceIds.clear();
            updateSelectionCount();
            
            // Load the first page of invoices
            loadPrintModalPage(1);
            
            // Show the modal
            $('#printModal').modal('show');
        });

        // Handle filter change
        $('#printFilter').change(function() {
            $('#printSearch').trigger('keyup');
        });

        // Handle search
        $('#printSearch').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            var filterType = $('#printFilter').val();
            
            $('#printInvoicesTable tr').each(function() {
                var row = $(this);
                var show = false;
                
                if (searchText === '') {
                    show = true;
                } else {
                    switch(filterType) {
                        case 'number':
                            show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                            break;
                        case 'supplier':
                            show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                            break;
                        default:
                            show = row.text().toLowerCase().includes(searchText);
                    }
                }
                
                row.toggle(show);
            });
        });
    });
    </script>
</body>
</html> 