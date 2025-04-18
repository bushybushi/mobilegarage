<?php
	session_start();
	
	// Redirect to the main expenses page
	header("Location: " . $_POST['previous_link']);

	require_once "../models/extra_expenses_model.php";
	$extraExpenseMang = new extraExpenseManagement();
	
	$result = $extraExpenseMang->Add();
	
	exit();
?> 