<?php
	require "lib.php";

	safe_session_start();

	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("INSERT INTO clients (name, email, birthdate, passport, address, phone, passgiven, passcode, passdate, sex, birthplace, reg) " .
		"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	$name = $_POST["name"];
	$phone = standart_phone($_POST["phone"]);
	$passport = $_POST["passport"];
	$passgiven = $_POST["passgiven"];
	$passcode = $_POST["passcode"];
	$passdate = ($_POST["passdate"] == "" ? "0000-00-00" : $_POST["passdate"]);
	$sex = $_POST["sex"];
	$birthdate = ($_POST["birthdate"] == "" ? "0000-00-00" : $_POST["birthdate"]);
	$birthplace = $_POST["birthplace"];
	$address = $_POST["address"];
	$reg = $_POST['reg'];
	$email = $_POST["email"];
     
	$stmt->bind_param("ssssssssssss", $name, $email, $birthdate, $passport, $address, $phone, $passgiven, $passcode, $passdate, $sex, $birthplace, $reg);
	$stmt->execute();
                                      
	header('Location: ../oper.php');
	$_SESSION['message-create-client'] = "Клиент успешно создан.";


?>