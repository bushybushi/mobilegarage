<?php
	session_start();
	require_once "../models/customer_model.php";
	$customerMang = new customerManagement();
	
	$customerMang->Update();
?>