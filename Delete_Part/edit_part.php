<?php
// edit_part.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Check for the partID in the URL
if (!isset($_GET['partID'])) {
    die("No part specified.");
}

$partID = intval($_GET['partID']);

// Retrieve part details
$sql = "SELECT p.PartID, p.PartDesc, p.SellPrice, p.Sold, p.Stock, 
               ps.PiecesPurch, ps.PricePerPiece, s.Name AS Supplier
        FROM parts p
        LEFT JOIN partssupply ps ON p.PartID = ps.PartID
        LEFT JOIN invoicesupply i ON ps.InvoiceID = i.InvoiceID
        LEFT JOIN suppliers s ON i.SupplierID = s.SupplierID
        WHERE p.PartID = :partID";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partID', $partID, PDO::PARAM_INT);
$stmt->execute();
$part = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$part) {
    die("Part not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Part</title>
    <link rel="stylesheet" href="edit_style.css">
    <script>
        function deletePart() {
            let partID = document.getElementById('partID').value;

            if (!confirm("Are you sure you want to delete this part?")) return;

            fetch("delete_part.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "partID=" + partID
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("form-container").style.display = "none";
                    document.getElementById("success-message").style.display = "block";
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</head>
<body>

<div class="container">
    <div class="part-header">
        <h2>Part Details</h2>
    </div>

    <div id="form-container">
        <form>
            <input type="hidden" id="partID" name="partID" value="<?php echo $part['PartID']; ?>">

            <div class="form-group">
                <label>Part Description</label>
                <input type="text" value="<?php echo htmlspecialchars($part['PartDesc']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <input type="text" value="<?php echo htmlspecialchars($part['Supplier']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Sell Price</label>
                <input type="text" value="<?php echo htmlspecialchars($part['SellPrice']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Pieces Purchased</label>
                <input type="text" value="<?php echo htmlspecialchars($part['PiecesPurch']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Price Per Piece</label>
                <input type="text" value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Sold</label>
                <input type="text" value="<?php echo htmlspecialchars($part['Sold']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="text" value="<?php echo htmlspecialchars($part['Stock']); ?>" readonly>
            </div>

            <!-- Delete Button -->
            <button type="button" class="delete-button" onclick="deletePart()">
                Delete Part <i class="fas fa-trash-alt"></i>
            </button>
        </form>
    </div>

    <!-- Success Message -->
    <div id="success-message" class="success-message" style="display: none;">
        <p>Part Deleted Successfully!</p>
        <button onclick="window.location.href='list_parts.php'">Return to Parts List</button>
    </div>
</div>

</body>
</html>
