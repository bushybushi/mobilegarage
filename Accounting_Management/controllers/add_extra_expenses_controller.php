<?php
	session_start();
	require_once "../models/extra_expenses_model.php";
	$extraExpenseMang = new extraExpenseManagement();
	
	$result = $extraExpenseMang->Add();
	
	// Redirect to the main expenses page
	header("Location: ../views/extra_expenses_main.php");
	exit();
?> 