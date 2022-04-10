<?php
	session_start();

	require "lib.php";

	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("INSERT INTO clients (name, email, birthdate, passport, address, phone, passgiven, passcode, passdate, sex, birthplace) " . 
		"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");	        
 
	$name = $_POST["name"];
	$phone = $_POST["phone"];
	$passport = $_POST["passport"];
	$passgiven = $_POST["passgiven"];
	$passcode = $_POST["passcode"];
	$passdate = ($_POST["passdate"] == "" ? "0000-00-00" : $_POST["passdate"]);
	$sex = $_POST["sex"];
	$birthdate = ($_POST["birthdate"] == "" ? "0000-00-00" : $_POST["birthdate"]);
	$birthplace = $_POST["birthplace"];
	$address = $_POST["address"];
	$email = $_POST["email"];
     
	$stmt->bind_param("sssssssssss", $name, $email, $birthdate, $passport, $address, $phone, $passgiven, $passcode, $passdate, $sex, $birthplace);
	$stmt->execute();

	header('Location: ../oper.php');
	$_SESSION['message'] = "Клиент успешно создан.";


?>