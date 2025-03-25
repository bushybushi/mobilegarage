<?php
	session_start();
	require_once "../models/parts_model.php";
	$partsMang = new partManagement();
	
	$partsMang->Delete();
?>