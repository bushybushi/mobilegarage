<?php
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Get the part ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Query to fetch part's information with supplier details
$partSql = 'SELECT p.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail,
            ps.PiecesPurch, ps.PricePerPiece
            FROM Parts p
            LEFT JOIN PartsSupply ps ON p.PartID = ps.PartID
            LEFT JOIN Invoices i ON ps.InvoiceID = i.InvoiceID
            LEFT JOIN InvoiceSupply ins ON i.InvoiceID = ins.InvoiceID
            LEFT JOIN Suppliers s ON ins.SupplierID = s.SupplierID
            WHERE p.PartID = ?';
$partStmt = $pdo->prepare($partSql);
$partStmt->execute([$id]);

// Store the part data in a variable
$old_part = $partStmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Part</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

<!-- Main Content Container -->
<div class="form-container">
    <!-- Top Navigation Bar with Part Description and Action Buttons -->
    <div class="top-container d-flex justify-content-between align-items-center">
            <!-- Back Arrow Button -->
            <a href="javascript:void(0);" onclick="window.location.href='part_view.php?id=<?php echo $id; ?>'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Part Description Display -->
            <div class="flex-grow-1 text-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($old_part['PartDesc']); ?></h5>
                </a>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end">
                <div class="btngroup">
                    <!-- Print Button -->
                    <button href="#" type="button" class="btn btn-success mr-2">Print </button>
                    <!-- Job Cards Button -->
                    <button href="#" type="button" class="btn btn-primary">Job Cards </button>
                </div>
            </div>
        </div>

    <!-- Part Edit Form -->
    <form action="../controllers/update_part_controller.php" method="post">
        <!-- Hidden ID field -->
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <!-- Part Description Input Field -->
        <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="supplierName">Supplier *</label>
                            <input type="text" name="supplierName" class="form-control" value="<?php echo htmlspecialchars($old_part['SupplierName']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="supplierPhone">Supplier Phone</label>
                            <input type="tel" name="supplierPhone" class="form-control" value="<?php echo htmlspecialchars($old_part['SupplierPhone']); ?>">
                        </div>
                        <div class="col">
                            <label for="supplierEmail">Supplier Email</label>
                            <input type="email" name="supplierEmail" class="form-control" value="<?php echo htmlspecialchars($old_part['SupplierEmail']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="description">Part Description *</label>
                            <input type="text" id="description" name="description" class="form-control" value="<?php echo htmlspecialchars($old_part['PartDesc']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="piecesPurch">Pieces Purchased *</label>
                            <input type="number" name="piecesPurch" class="form-control" value="<?php echo htmlspecialchars($old_part['PiecesPurch']); ?>">
                        </div>
                        <div class="col">
                            <label for="pricePerPiece">Price Per Piece *</label>
                            <input type="number" name="pricePerPiece" class="form-control" value="<?php echo htmlspecialchars($old_part['PricePerPiece']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="priceBulk">Bulk Price</label>
                            <input type="number" step="0.01" id="priceBulk" name="priceBulk" class="form-control" value="<?php echo htmlspecialchars($old_part['PriceBulk']); ?>">
                        </div>
                        <div class="col">
                            <label for="sellPrice">Sell Price</label>
                            <input type="number" step="0.01" id="sellPrice" name="sellPrice" class="form-control" value="<?php echo htmlspecialchars($old_part['SellPrice']); ?>">
                        </div>
                    </div>
                </div>

                 <!-- Submit Button -->
       <div class="btngroup">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

</body>
</html>