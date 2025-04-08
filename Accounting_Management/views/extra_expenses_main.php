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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../assets/scripts.js"></script>
</head>

<body>
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