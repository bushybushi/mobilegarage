<?php

// Include the input sanitization file
require_once '../sanitize_inputs.php';

// Get the PDO instance from the included file
$pdo = require '../db_connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$customerSql = 'SELECT * from customers where CustomerID = ?';
$customerStmt = $pdo->prepare($customerSql);
$customerStmt->execute([$id]);

$old_customer = $customerStmt->fetch();


$addressSql = 'select Address from Addresses where CustomerID = ?';
$addressStmt = $pdo->prepare($addressSql);
$addressStmt->execute([$id]);

$old_address = $addressStmt->fetchAll();

$phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
$phoneStmt = $pdo->prepare($phoneSql);
$phoneStmt->execute([$id]);

$old_phone = $phoneStmt->fetchAll();

$emailSql = 'SELECT Emails from Emails where CustomerID = ?';
$emailStmt = $pdo->prepare($emailSql);
$emailStmt->execute([$id]);

$old_email = $emailStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
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
    <form action="EditCustomer.php" method="post">
        <label for="firstName">First Name</label>
        <input type="text" id="firstName" name="firstName" value = "<?php echo htmlspecialchars($old_customer['FirstName'], ENT_QUOTES, 'UTF-8'); ?>"  required>

        <label for="surname">Surname</label>
        <input type="text" id="surname" name="surname" value = "<?php echo htmlspecialchars($old_customer['LastName'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="companyName">Company Name</label>
        <input type="text" id="companyName" name="companyName" value = "<?php echo htmlspecialchars($old_customer['Company'], ENT_QUOTES, 'UTF-8'); ?>">

		<?php 
		foreach ($old_address as $row)
		
        echo '<label for="address">Address</label>
        <input type="text" id="address" name="address" value = "' . htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8') . '">';
		
		foreach ($old_phone as $row)
		
        echo '<label for="phoneNumber">Phone Number</label>
        <input type="tel" id="phoneNumber" name="phoneNumber" value = "' . htmlspecialchars($row['Nr'], ENT_QUOTES, 'UTF-8') . '">';
		
		foreach ($old_email as $row)
		
        echo '<label for="emailAddress">Email Address</label>
        <input type="email" id="emailAddress" name="emailAddress" value = "' . htmlspecialchars($row['Emails'], ENT_QUOTES, 'UTF-8') . '" required>';
		?>

        <input  type="submit" value="Edit Customer" class = "submit-button">
    </form>
</div>
</div>
</body>
</html>