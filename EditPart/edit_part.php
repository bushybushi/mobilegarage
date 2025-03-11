<?php
// Include the input sanitization file
require_once '../sanitize_inputs.php';

// Get the PDO instance from the included file
$pdo = require '../db_connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$partSql = 'SELECT PartDesc, NAME, Email, PhoneNr, PiecesPurch, PricePerPiece, PriceBulk, Vat, SellPrice, Sold, Stock
            FROM parts JOIN partssupply USING (PartID)
            	JOIN invoices USING (InvoiceID)
                JOIN invoicesupply USING (InvoiceID)
                JOIN suppliers USING (SupplierID) 
            WHERE PartID = ?';
$partStmt = $pdo->prepare($partSql);
$partStmt->execute([$id]);

$old_part = $partStmt->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Part</title>
    <link rel="stylesheet" href="../styles.css">

	
</head>
<body>

<!-- Top bar -->
<header>
	<h2>Mobile Garage</h2>
</header>

<!-- Sidebar -->
<div class = 'sidebar'>
<p>Side Menu</p>
	<a href = "" class = "sidebar-button">Dashboard</a>
	<a href = "" class = "sidebar-button">Customers</a>
	<a href = "" class = "sidebar-button">Parts</a>
	<a href = "" class = "sidebar-button">Jobs</a>
	<a href = "" class = "sidebar-button">Accounting</a>
	<a href = "" class = "sidebar-button">Invoices</a>
</div>
<!-- Container for main area-->
<div class = "container">

<!-- Main Content area -->
<div class = "main-content">
    <form action="update_part.php" method="post">
        <label for="partdesc">Part Description</label>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="partdesc" value = "<?php echo htmlspecialchars($old_part['PartDesc'], ENT_QUOTES, 'UTF-8'); ?>"  required>

        <label for="supplier">Supplier</label>
        <input type="text" name="supplier" value = "<?php echo htmlspecialchars($old_part['NAME'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="suppemail">Supplier Email</label>
        <input type="text" name="suppemail" value = "<?php echo htmlspecialchars($old_part['Email'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="suppphone">Supplier Phone</label>
        <input type="text" name="suppphone" value = "<?php echo htmlspecialchars($old_part['PhoneNr'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="piecespurch">Pieces Purchased</label>
        <input type="text" name="piecespurch" value = "<?php echo htmlspecialchars($old_part['PiecesPurch'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="priceperpiece">Price Per Piece</label>
        <input type="text" name="priceperpiece" value = "<?php echo htmlspecialchars($old_part['PricePerPiece'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="pricebulk">Price Bulk</label>
        <input type="text" name="pricebulk" value = "<?php echo htmlspecialchars($old_part['PriceBulk'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="vat">VAT</label>
        <input type="text" name="vat" value = "<?php echo htmlspecialchars($old_part['Vat'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="sellprice">Selling Price</label>
        <input type="text" name="sellprice" value = "<?php echo htmlspecialchars($old_part['SellPrice'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="sold">Pieces Sold</label>
        <input type="text" name="sold" value = "<?php echo htmlspecialchars($old_part['Sold'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="stock">Pieces in Stock</label>
        <input type="text" name="stock" value = "<?php echo htmlspecialchars($old_part['Stock'], ENT_QUOTES, 'UTF-8'); ?>">
	
        <input  type="submit" value="Edit Part" class = "submit-button">
    </form>
</div>
</div>


</body>
</html>