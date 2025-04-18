<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../../UserAccess/protect.php';

// Include database connection file and extra expenses model
require_once '../config/db_connection.php';
require_once '../models/extra_expenses_model.php';

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

if (!$expense) {
    header("Location: extra_expenses_main.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Details</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- JavaScript dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/utils.js"></script>

    
</head>

<style>
    .section-header {
        background: #f8f9fa;
        padding: 12px 20px;
        margin: 25px 0 20px 0;
        color: #495057;
        border-radius: 6px;
        display: flex;
        align-items: center;
        border: 1px solid #dee2e6;
    }

    .section-header i {
        margin-right: 12px;
        font-size: 1.1rem;
        color: #6c757d;
    }

    .section-header span {
        font-size: 1.1rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .expense-details-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .expense-detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .expense-detail-row:last-child {
        border-bottom: none;
    }

    .expense-detail-label {
        font-weight: 500;
        color: #495057;
    }

    .expense-detail-value {
        color: #212529;
    }

    .alert {
        margin: 20px 0;
        border-radius: 8px;
        padding: 15px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<body>
    <div class="form-container">
        <!-- Top Navigation Bar -->
        <div class="top-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <a href="javascript:void(0);" onclick="window.location.href='<?php echo $_GET['previous_link']?>'" class="back-arrow mb-2 mb-md-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center mb-2 mb-md-0">
                <h5 class="mb-0">Expense Details</h5>
            </div>
            <div style="width: 30px;" class="d-none d-md-block"></div>
        </div>

        <!-- Expense Details -->
        <div class="section-header">
            <i class="fas fa-info-circle"></i>
            <span>Expense Information</span>
        </div>

        <div class="expense-details-container">
            <div class="expense-detail-row">
                <span class="expense-detail-label">ID</span>
                <span class="expense-detail-value"><?php echo htmlspecialchars($expense['ExpenseID']); ?></span>
            </div>
            <div class="expense-detail-row">
                <span class="expense-detail-label">Description</span>
                <span class="expense-detail-value"><?php echo htmlspecialchars($expense['Description']); ?></span>
            </div>
            <div class="expense-detail-row">
                <span class="expense-detail-label">Date</span>
                <span class="expense-detail-value"><?php echo htmlspecialchars($expense['DateCreated']); ?></span>
            </div>
            <div class="expense-detail-row">
                <span class="expense-detail-label">Amount</span>
                <span class="expense-detail-value">€<?php echo number_format($expense['Expense'], 2); ?></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-primary mr-2" onclick="loadEditForm(<?php echo $expenseId; ?>, '<?php echo $_GET['previous_link']; ?>')" style="width: 100px;">
                Edit <i class="fas fa-edit"></i> 
            </button>
            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $expenseId; ?>)" style="width: 100px;">
                 Delete <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>

    <script>
        function loadEditForm(expenseId, link) {
        $.get('/MGAdmin2025/managements/Accounting_Management/views/edit_extra_expenses_form.php', { id: expenseId, previous_link: link }, function(response) {
        $('#dynamicContent').html(response);
        });
        }
        
        function confirmDelete(expenseId, link) {
            if (confirm('Are you sure you want to delete this expense?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/MGAdmin2025/managements/Accounting_Management/controllers/delete_expense_controller.php';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = expenseId;

        const DescInput = document.createElement('input');
        DescInput.type = 'hidden';
        DescInput.name = 'description';
        DescInput.value = <?php echo json_encode($expense['Description']); ?>;

        const DateInput = document.createElement('input');
        DateInput.type = 'hidden';
        DateInput.name = 'dateCreated';
        DateInput.value = <?php echo json_encode($expense['DateCreated']); ?>;

        
        const ExpenseInput = document.createElement('input');
        ExpenseInput.type = 'hidden';
        ExpenseInput.name = 'expense';
        ExpenseInput.value = <?php echo json_encode($expense['Expense']); ?>;

        const linkInput = document.createElement('input');
        linkInput.type = 'hidden';
        linkInput.name = 'previous_link';
        linkInput.value = <?php echo json_encode($_GET['previous_link']); ?>;

        form.appendChild(idInput);
        form.appendChild(DescInput);
        form.appendChild(DateInput);
        form.appendChild(ExpenseInput);
        form.appendChild(linkInput);
        document.body.appendChild(form);
        form.submit();
        }
}
    </script>
</body>
</html>