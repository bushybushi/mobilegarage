<?php
session_start();
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
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='extra_expenses_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                <h5>Add Extra Expense</h5>
            </div>
            <div style="width: 30px;"></div>
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

        <form action="../controllers/add_extra_expenses_controller.php" method="POST">
            <div class="form-group">
                <label for="description">Description *</label>
                <input type="text" id="description" name="description" class="form-control" required maxlength="50">
                <div class="form-text">Enter a description for the expense (max 50 characters)</div>
            </div>
            
            <div class="form-group">
                <label for="dateCreated">Date Created *</label>
                <input type="date" id="dateCreated" name="dateCreated" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="expense">Expense Amount *</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="expense" name="expense" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="form-text">Enter the expense amount (e.g., 25.50)</div>
            </div>
            
            <div class="btngroup">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</body>
</html> 