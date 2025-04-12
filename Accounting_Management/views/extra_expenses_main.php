<?php
// ExtraExpensesMain.php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/extra_expenses_model.php';

// Get sort parameter from URL
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'DateCreated';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort parameter
$allowedSortFields = ['DateCreated', 'Description', 'Expense'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'DateCreated';
}

// Validate sort order
if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
    $sortOrder = 'DESC';
}

// SQL query to fetch all extra expenses
$sql = "SELECT ExpenseID, Description, DateCreated, Expense FROM extraexpenses";

// Get total number of expenses
$totalExpenses = $pdo->query("SELECT COUNT(*) FROM extraexpenses")->fetchColumn();
$expensesPerPage = 10;
$totalPages = ceil($totalExpenses / $expensesPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $expensesPerPage;

// Modify the main query to include ORDER BY, LIMIT and OFFSET
$sql .= " ORDER BY $sortBy $sortOrder LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $expensesPerPage, PDO::PARAM_INT);
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
    
    /* Back arrow styles */
    .back-arrow {
        position: fixed;
        top: 20px;
        left: 20px;
        font-size: 24px;
        color: black;
        cursor: pointer;
        z-index: 1000;
        background-color: rgba(255, 255, 255, 0.7);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .back-arrow:hover {
        background-color: rgba(255, 255, 255, 0.9);
        transform: scale(1.1);
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extra Expenses Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../assets/scripts.js" defer></script>
</head>

<body>
    <!-- Back arrow to return to accounting view -->
    <div class="back-arrow" onclick="window.location.href='view_accounting.php'">
        <i class="fas fa-arrow-left" style="font-size: 24px; color: black;"></i>
    </div>
    
    <!-- Add iframe for printing -->
    <iframe id="printFrame"></iframe>

    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Expense Count and Action Buttons -->
            <div class="title-container d-flex justify-content-between align-items-center">
                <!-- Expense Count Display -->
                <div>
                    Total: <?php echo count($result); ?> Expenses
                </div>
                <!-- Action Buttons -->
                <div class="d-flex">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort"><?php echo $sortBy; ?></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="?sort=DateCreated&order=<?php echo $sortBy === 'DateCreated' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Date <?php echo $sortBy === 'DateCreated' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                            <li><a class="dropdown-item" href="?sort=Description&order=<?php echo $sortBy === 'Description' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Description <?php echo $sortBy === 'Description' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                            <li><a class="dropdown-item" href="?sort=Expense&order=<?php echo $sortBy === 'Expense' && $sortOrder === 'DESC' ? 'ASC' : 'DESC'; ?>">Amount <?php echo $sortBy === 'Expense' ? ($sortOrder === 'DESC' ? '↓' : '↑') : ''; ?></a></li>
                        </ul>
                    </div>

                    <!-- Add New Expense Button -->
                    <a href="add_extra_expenses_form.php" class="btn btn-primary" style="width: 100px;">Add 
                        <span>
                            <i class="fas fa-plus"></i>
                        </span>
                    </a>
                </div>
            </div>

            <!-- Expenses Table -->
            <table class="table table-striped">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <th style="display: none;">ID</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th>Expense Amount</th>
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
                            <td style="display: none;"><?php echo htmlspecialchars($row['ExpenseID']); ?></td>
                            <td><?php echo htmlspecialchars($row['Description']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td>$<?php echo number_format($row['Expense'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Add empty rows to maintain table size
                    $emptyRows = $expensesPerPage - $rowCount;
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

            <!-- Pagination for the main table -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center main-pagination">
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Update the selected sort text in the dropdown button
        document.addEventListener('DOMContentLoaded', function() {
            const selectedSort = document.getElementById('selectedSort');
            selectedSort.textContent = '<?php echo $sortBy; ?>';
        });
    </script>
</body>
</html> 