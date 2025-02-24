<?php

// Include the input sanitization file
require_once '../sanitize_inputs.php';

// Get the PDO instance from the included file
$pdo = require '../db_connection.php';
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
        <input type="text" id="firstName" name="firstName" required>

        <label for="surname">Surname</label>
        <input type="text" id="surname" name="surname" required>

        <label for="companyName">Company Name</label>
        <input type="text" id="companyName" name="companyName">

        <label for="address">Address</label>
        <input type="text" id="address" name="address">

        <label for="phoneNumber">Phone Number</label>
        <input type="tel" id="phoneNumber" name="phoneNumber">

        <label for="emailAddress">Email Address</label>
        <input type="email" id="emailAddress" name="emailAddress" required>

        <input  type="submit" value="Edit Customer" class = "submit-button">
    </form>
</div>
</div>
</body>
</html>