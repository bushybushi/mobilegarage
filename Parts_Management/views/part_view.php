<?php
// Include database connection file
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

// Get part ID from URL parameter
$partId = $_GET['id'];

// SQL query to fetch part details with supplier information
$sql = "SELECT p.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail,
        ps.PiecesPurch, ps.PricePerPiece
        FROM Parts p
        LEFT JOIN PartsSupply ps ON p.PartID = ps.PartID
        LEFT JOIN Suppliers s ON ps.SupplierID = s.SupplierID
        WHERE p.PartID = :partId";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partId', $partId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the part data
$part = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part View</title>
    
    <!-- CSS dependencies -->
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
            <a href="javascript:void(0);" onclick="window.location.href='parts_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Part Description Display -->
            <div class="flex-grow-1 text-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($part['PartDesc']); ?></h5>
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

        <!-- Part View Form -->
        <div class="form-content">
            <!-- Disable form fields for view-only mode -->
            <fieldset disabled>
                <!-- Part Information Section -->
                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="supplierName">Supplier *</label>
                            <input type="text" name="supplierName" class="form-control" value="<?php echo htmlspecialchars($part['SupplierName']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="supplierPhone">Supplier Phone</label>
                            <input type="tel" name="supplierPhone" class="form-control" value="<?php echo htmlspecialchars($part['SupplierPhone']); ?>">
                        </div>
                        <div class="col">
                            <label for="supplierEmail">Supplier Email</label>
                            <input type="email" name="supplierEmail" class="form-control" value="<?php echo htmlspecialchars($part['SupplierEmail']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="description">Part Description *</label>
                            <input type="text" id="description" name="description" class="form-control" value="<?php echo htmlspecialchars($part['PartDesc']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="piecesPurch">Pieces Purchased *</label>
                            <input type="number" name="piecesPurch" class="form-control" value="<?php echo htmlspecialchars($part['PiecesPurch']); ?>">
                        </div>
                        <div class="col">
                            <label for="pricePerPiece">Price Per Piece *</label>
                            <input type="number" name="pricePerPiece" class="form-control" value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col">
                            <label for="priceBulk">Bulk Price</label>
                            <input type="number" step="0.01" id="priceBulk" name="priceBulk" class="form-control" value="<?php echo htmlspecialchars($part['PriceBulk']); ?>">
                        </div>
                        <div class="col">
                            <label for="sellPrice">Sell Price</label>
                            <input type="number" step="0.01" id="sellPrice" name="sellPrice" class="form-control" value="<?php echo htmlspecialchars($part['SellPrice']); ?>">
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Action Buttons -->
            <div class="btngroup">
                <!-- Edit Button - Links to edit_part.php -->
                <a href="edit_part.php?id=<?php echo $partId; ?>" class="btn btn-primary">Edit</a>
                <!-- Delete Button - Form with POST method -->
                <form id="deleteForm" method="POST" action="../controllers/delete_part_controller.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $partId; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this part?');">Delete</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>