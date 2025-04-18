<?php
	session_start();
	require_once "../models/car_model.php";
	$carMang = new carManagement(true); // Require POST method
	
	$carMang->Update();
?>