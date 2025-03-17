<?php
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Part</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <!-- Part Form Container -->
    <div class="form-container">
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='parts_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                <h5>Add Part</h5>
            </div>
            <div style="width: 30px;"></div>
        </div>
        <form action="../controllers/add_part_controller.php" method="POST">

            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                    <label for="supplierName">Supplier *</label>
                        <input type="text" name="supplierName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['supplierName']) ? $sanitizedInputs['supplierName'] : ''); ?>" required>
                        <?php if (isset($errors['supplierName'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['supplierName']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label for="supplierPhone">Supplier Phone</label>
                        <input type="number" name="supplierPhone" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['supplierPhone']) ? $sanitizedInputs['supplierPhone'] : ''); ?>">
                        <?php if (isset($errors['supplierPhone'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['supplierPhone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label for="supplierEmail">Supplier Email</label>
                        <input type="email" name="supplierEmail" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['supplierEmail']) ? $sanitizedInputs['supplierEmail'] : ''); ?>">
                        <?php if (isset($errors['supplierEmail'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['supplierEmail']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label for="description">Part Description *</label>
                        <input type="text" id="description" name="description" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['description']) ? $sanitizedInputs['description'] : ''); ?>" required>
                        <?php if (isset($errors['description'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['description']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label for="piecesPurch">Pieces Purchased *</label>
                        <input type="number" name="piecesPurch" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['piecesPurch']) ? $sanitizedInputs['piecesPurch'] : ''); ?>">
                        <?php if (isset($errors['piecesPurch'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['piecesPurch']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label for="pricePerPiece">Price Per Piece *</label>
                        <input type="number" name="pricePerPiece" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['pricePerPiece']) ? $sanitizedInputs['pricePerPiece'] : ''); ?>">
                        <?php if (isset($errors['pricePerPiece'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['pricePerPiece']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label for="priceBulk">Bulk Price</label>
                        <input type="number" name="priceBulk" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['priceBulk']) ? $sanitizedInputs['priceBulk'] : ''); ?>">
                        <?php if (isset($errors['priceBulk'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['priceBulk']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label for="sellPrice">Sell Price</label>
                        <input type="number" name="sellPrice" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['sellPrice']) ? $sanitizedInputs['sellPrice'] : ''); ?>">
                        <?php if (isset($errors['sellPrice'])): ?>
                            <div class="error"><?php echo htmlspecialchars($errors['sellPrice']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="btngroup">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
