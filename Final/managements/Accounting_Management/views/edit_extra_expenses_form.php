<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';

session_start();

// Include the customer model
require_once '../models/extra_expenses_model.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Get expense ID from URL parameter
$expenseId = $_GET['id'] ?? null;


if (!$expenseId) {
    header("Location: extra_expenses_main.php");
    exit;
}

// Create extra expenses object
$extraExpenseMang = new extraExpenseManagement();

// Get specific expense details
$expense = $extraExpenseMang->getExpenseById($expenseId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Extra Expense</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <!-- Extra Expense Form Container -->
    <div class="form-container">
        <div class="top-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <a href="javascript:void(0);" onclick="window.location.href='/MGAdmin2025/managements/Accounting_Management/views/extra_expenses_main.php'" class="back-arrow mb-2 mb-md-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center mb-2 mb-md-0">
                <h5>Edit Extra Expense</h5>
            </div>
            <div style="width: 30px;" class="d-none d-md-block"></div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="../controllers/update_extra_expenses_controller.php" method="POST">
            <div class="form-group">
                <label for="description">Description *</label>
                <input type="hidden" name="previous_link" value="<?php echo htmlspecialchars($_GET['previous_link'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($expenseId, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="text" id="description" name="description" class="form-control" required maxlength="50" value="<?php echo $expense['Description']?>">
                <div class="form-text">Enter a description for the expense (max 50 characters)</div>
            </div>
            
            <div class="form-group">
                <label for="dateCreated">Date Created *</label>
                <input type="date" id="dateCreated" name="dateCreated" class="form-control"
            value="<?php echo htmlspecialchars($expense['DateCreated']); ?>" required>
            </div>

            <div class="form-group">
                <label for="expense">Expense Amount *</label>
                <div class="input-group">
                <span class="input-group-text">€</span>
                <input type="number" id="expense" name="expense" class="form-control" step="0.01" min="0"
                value="<?php echo htmlspecialchars($expense['Expense']); ?>" required>
            </div>
                <div class="form-text">Enter the expense amount (e.g., 25.50)</div>
            </div>
            
            <div class="btngroup">
                <button type="submit" class="btn btn-primary" style="width: 100px;">Save <i class="fas fa-save"></i></button>
            </div>
        </form>
    </div>
</body>
</html> 