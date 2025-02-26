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
    <form action="update_customer.php" method="post">
        <label for="firstName">First Name</label>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="firstName" value = "<?php echo htmlspecialchars($old_customer['FirstName'], ENT_QUOTES, 'UTF-8'); ?>"  required>

        <label for="surname">Surname</label>
        <input type="text" name="surname" value = "<?php echo htmlspecialchars($old_customer['LastName'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="companyName">Company Name</label>
        <input type="text" name="companyName" value = "<?php echo htmlspecialchars($old_customer['Company'], ENT_QUOTES, 'UTF-8'); ?>">

		<?php 
		//Addresses
		echo '<div id="addresses">';
		if (!empty($old_address)) {
		foreach ($old_address as $row)
		
        echo '<div>
		<label for="address">Address</label>
        <input type="text" name="address[]" value = "' . htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8') . '">
		<button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
		</div>';
		
		} else
		{
		echo '<div>
		<label for="address">Address</label>
        <input type="text" name="address[]">
		</div>';	
		}
		
		echo '<button type="button" onclick="addAddressField()">Add Another Address</button>';
		echo '</div>';
		
		//Phone Numbers
		echo '<div id="phoneNumbers">';
		if (!empty($old_phone)) {
		foreach ($old_phone as $row)
		
        echo '<div>
		<label for="phoneNumber">Phone Number</label>
        <input type="tel" name="phoneNumber[]" value = "' . htmlspecialchars($row['Nr'], ENT_QUOTES, 'UTF-8') . '">
		<button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
		</div>';
		
		} else
		{
		echo '<div>
		<label for="phoneNumber">Phone Number</label>
        <input type="text" name="phoneNumber[]">
		</div>';	
		}
		echo '<button type="button" onclick="addPhoneNumberField()">Add Another Phone Number</button>';
		echo '</div>';
		
		//Emails
		echo '<div id="emailAddresses">';
		if (!empty($old_email)) {
		foreach ($old_email as $row)
		
        echo '<div>
		<label for="emailAddress">Email Address</label>
        
		<input type="email" name="emailAddress[]" value = "' . htmlspecialchars($row['Emails'], ENT_QUOTES, 'UTF-8') . '">
		<button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
		</div>';
		} else
		{
		echo '<div>
		<label for="emailAddress">Email Address</label>
        <input type="email" name="emailAddress[]">
		</div>';	
		}
		
		echo '<button type="button" onclick="addEmailAddressField()">Add Another Email</button>';
		echo '</div>';
		?>
		
		
	
        <input  type="submit" value="Edit Customer" class = "submit-button">
    </form>
</div>
</div>

<script>
        function addAddressField() {
            const container = document.getElementById('addresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="address[]">Address</label>
                <input type="text" name="address[]" value="">
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function addPhoneNumberField() {
            const container = document.getElementById('phoneNumbers');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="phoneNumber[]">Phone Number</label>
                <input type="tel" name="phoneNumber[]" value="" required>
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function addEmailAddressField() {
            const container = document.getElementById('emailAddresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="emailAddress[]">Email Address</label>
                <input type="email" name="emailAddress[]" value="">
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function removeField(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>