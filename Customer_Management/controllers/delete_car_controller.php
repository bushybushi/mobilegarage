<?php
	session_start();
	require_once "../models/car_model.php";
	$carMang = new carManagement();
	
	$carMang->Delete();
?>