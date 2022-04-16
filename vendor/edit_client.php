<?php
	require "lib.php";

	safe_session_start();

	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("UPDATE clients SET name = ?, email = ?, birthdate = ?, passport = ?, address = ?, phone = ?, passgiven = ?, " .
		"passcode = ?, passdate = ?, sex = ?, birthplace = ?, reg = ? WHERE id = ?");
 
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
	$email = $_POST["email"];
	$reg = $_POST["reg"];
	$id = $_SESSION["client"]["id"];
     
	$stmt->bind_param("ssssssssssssi", $name, $email, $birthdate, $passport, $address, $phone, $passgiven, $passcode, $passdate, $sex, $birthplace, $reg, $id);
	$stmt->execute();

	//$_SESSION["client"]["id"] = 
	header('Location: ../operwork.php#edit_client');
	$_SESSION['message-edit'] = "Информация о клиенте отредактирована.";


?>