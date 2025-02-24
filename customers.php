<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="styles.css">

<?php

// Include the input sanitization file
require_once 'sanitize_inputs.php';

// Get the PDO instance from the included file
$pdo = require 'db_connection.php';

// Current page
$cpage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Code to find how many pages there needs to be
// Starting by counting how many customers are in the database

$customerSql = "select count(customerid) from customers";
$customerStmt = $pdo->prepare($customerSql);
$customerStmt->execute();

$maxpage = $customerStmt->fetchColumn();


// Offset used in SQL query to set current page customers
$offset = (($cpage-1) * 10);
// SQL query to get the customers for the current page
$customerSql = "SELECT c.*, p.nr, e.emails, a.address
				FROM customers c
				LEFT JOIN (
				SELECT customerID, MIN(nr) AS nr
				FROM phonenumbers
				GROUP BY customerID
				) p ON c.customerID = p.customerID
				LEFT JOIN (
				SELECT customerID, MIN(emails) AS emails
				FROM emails
				GROUP BY customerID
				) e ON c.customerID = e.customerID
				LEFT JOIN (
				SELECT customerID, MIN(address) AS address
				FROM addresses	
				GROUP BY customerID
				) a ON c.customerID = a.customerID
				LIMIT 10 OFFSET $offset";

//executing the query and inserting into results
$customerStmt = $pdo->query($customerSql);

$results = $customerStmt->fetchAll();
?>
	
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
	<div>
	<table class = "main-table">
	<tr>
	<th>Total: X Customers</th>
	<th>
	<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Sort</a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </li></th>
	<th><a class="button-confirm">Print</a></th>
	<th><a class="button-primary" href="AddNewCustomer/add_customer.html">Add Customer</a></th>
	</tr>
	</table>
	</div>
	
    <div>
	<table class = "main-table">
		<tr>
			<th>Name</th>
			<th>E-mail</th>
			<th>Phone</th>
			<th>Address</th>
		<tr>
		
	<?php
		foreach ($results as $row) {
			echo '<tr>';
			$id = $row['CustomerID'];
			echo '<td>' . '<a href="#/?id=' . $id . '">' . $row['FirstName'] . ' ' . $row['LastName'] . '</a>' . '</td>';
			echo '<td>' . $row['emails'] . '</td>';
			echo '<td>' . $row['nr'] . '</td>';
			echo '<td>' . $row['address'] . '</td>';
			echo '</tr>';
		}
		?>
	</table>
	</div>
	<nav aria-label="Page navigation example" class="pagination">
	<tr>
		<td class="page-item"><a class="page-link" href="#">Previous</a></td>
		<td class="page-item"><a class="page-link" href="#">1</a></td>
		<td class="page-item"><a class="page-link" href="#">2</a></td>
		<td class="page-item"><a class="page-link" href="#">3</a></td>
		<td class="page-item"><a class="page-link" href="#">Next</a></td>
	</tr>
	</nav>
</div>
</body>
</html>