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

require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/parts_model.php';

session_start();

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
            <div class="title-container d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Parts</h2>
                    <small class="text-muted">Total: <?php echo $result['total_count']; ?> Parts</small>
                </div>
                <!-- Action Buttons -->
                <div class="d-flex">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-3">
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
                    <button type="button" id="printButton" class="btn btn-success mr-3">Print 
                        <span><i class="fas fa-print"></i></span>
                    </button>
                    <!-- Add Button -->
                    <button href="#" id="addnewparts-link" type="button" class="btn btn-primary">Add 
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
                        <tr data-parts-id="<?php echo htmlspecialchars($row['PartID']); ?>">
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['PartID']); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['PartDesc'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['DateCreated']); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierPhone'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['SupplierEmail'] ?? 'N/A'); ?>
                            </td>
                            <td onclick="openParts(<?php echo htmlspecialchars($row['PartID']); ?>)" style="cursor: pointer;">
                                <?php echo htmlspecialchars($row['Vat']); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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
        // Add Parts button click handler
        document.getElementById('addnewparts-link').addEventListener('click', function() {
            window.location.href = 'add_parts_form.php';
        });

        // Function to open parts details
        function openParts(partsId) {
            window.location.href = 'parts_view.php?id=' + partsId;
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
                                <option value="description">Description</option>
                                <option value="supplier">Supplier</option>
                                <option value="price_piece">Price/Piece</option>
                                <option value="sell_price">Sell Price</option>
                                <option value="stock">Stock</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selection Count -->
                    <div class="mb-3">
                        <span id="selectionCount">0 part(s) selected</span>
                    </div>

                    <!-- Print Buttons -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" style="min-width: 150px;" onclick="printAllParts()">Print All</button>
                        <button type="button" class="btn btn-success" style="min-width: 150px;" onclick="printSelectedParts()">Print Selected</button>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table">
                            <tbody id="printPartsTable">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Print Modal Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Print modal page navigation">
                            <ul class="pagination" id="printModalPagination">
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
        let currentPrintPage = 1;
        const printItemsPerPage = 10;
        let totalPrintPages = 1;
        let allPrintParts = [];

        // When the print button is clicked
        $('#printButton').click(function() {
            // Clear any previous selections
            selectedPartsIds.clear();
            updateSelectionCount();
            
            // Reset search and filter
            $('#printSearch').val('');
            $('#printFilter').val('all');
            
            // Load all parts
            loadAllPrintParts();
            
            // Show the modal
            $('#printModal').modal('show');
        });

        // Function to load all parts
        function loadAllPrintParts() {
            // Make AJAX call to get all parts
            $.ajax({
                url: '../printparts/get_print_parts.php',
                method: 'GET',
                success: function(response) {
                    $('#printPartsTable').html(response);
                    
                    // Get total number of rows
                    const totalRows = $('#printPartsTable tr').length;
                    totalPrintPages = Math.ceil(totalRows / printItemsPerPage);
                    
                    // Update pagination
                    updatePrintPagination();
                    
                    // Show only current page items immediately
                    showCurrentPageItems();
                },
                error: function() {
                    $('#printPartsTable').html('<tr><td colspan="7" class="text-center">Error loading parts</td></tr>');
                }
            });
        }

        // Function to update print pagination
        function updatePrintPagination() {
            let paginationHtml = '';
            
            // Previous button
            paginationHtml += `
                <li class="page-item ${currentPrintPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="return changePrintPage(${currentPrintPage - 1})">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPrintPages; i++) {
                paginationHtml += `
                    <li class="page-item ${i === currentPrintPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="return changePrintPage(${i})">${i}</a>
                    </li>
                `;
            }
            
            // Next button
            paginationHtml += `
                <li class="page-item ${currentPrintPage >= totalPrintPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="return changePrintPage(${currentPrintPage + 1})">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
            
            $('#printModalPagination').html(paginationHtml);
        }

        // Function to change page
        window.changePrintPage = function(page) {
            if (page >= 1 && page <= totalPrintPages) {
                currentPrintPage = page;
                showCurrentPageItems();
                updatePrintPagination();
            }
            return false;
        };

        // Function to show current page items
        function showCurrentPageItems() {
            const startIndex = (currentPrintPage - 1) * printItemsPerPage;
            const endIndex = startIndex + printItemsPerPage;
            
            // First, ensure the header is visible
            $('#printPartsTable thead').show();
            
            // Then handle the tbody rows
            $('#printPartsTable tbody tr').each(function(index) {
                if (index >= startIndex && index < endIndex) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Handle filter change
        $('#printFilter').change(function() {
            currentPrintPage = 1; // Reset to first page when filter changes
            $('#printSearch').trigger('keyup');
        });

        // Handle search
        $('#printSearch').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            var filterType = $('#printFilter').val();
            let visibleCount = 0;
            
            $('#printPartsTable tr').each(function() {
                var row = $(this);
                var show = false;
                
                if (searchText === '') {
                    show = true;
                } else {
                    switch(filterType) {
                        case 'description':
                            show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                            break;
                        case 'supplier':
                            show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                            break;
                        case 'price_piece':
                            show = row.find('td:eq(4)').text().toLowerCase().includes(searchText);
                            break;
                        case 'sell_price':
                            show = row.find('td:eq(5)').text().toLowerCase().includes(searchText);
                            break;
                        case 'stock':
                            show = row.find('td:eq(6)').text().toLowerCase().includes(searchText);
                            break;
                        default:
                            show = row.text().toLowerCase().includes(searchText);
                    }
                }
                
                if (show) {
                    visibleCount++;
                    row.show();
                } else {
                    row.hide();
                }
            });

            // Update pagination based on visible items
            totalPrintPages = Math.ceil(visibleCount / printItemsPerPage);
            if (currentPrintPage > totalPrintPages) {
                currentPrintPage = Math.max(1, totalPrintPages);
            }
            updatePrintPagination();
            showCurrentPageItems();
        });

        // Initialize the modal with first page
        $('#printModal').on('shown.bs.modal', function() {
            currentPrintPage = 1;
            showCurrentPageItems();
            updatePrintPagination();
        });
    });
    </script>
</body>
</html> 