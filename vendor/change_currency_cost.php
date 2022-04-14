<?php
	session_start();

	require "lib.php";

	$mysqli = get_sql_connection();

	$mysqli->query("BEGIN");

	$stmt = $mysqli->prepare("UPDATE converter SET current = 0 WHERE currency = ? AND current = 1");
	$stmt->bind_param("s", $_POST["currency"]);
	$stmt->execute();
	$stmt = $mysqli->prepare("INSERT INTO converter (currency, cost, date) VALUES 
		(?, ?, (SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1))");
	$sum = standart_sum($_POST["sum"]);
	$stmt->bind_param("ss", $_POST["currency"], $sum);
	$stmt->execute();

	$mysqli->query("COMMIT");

	$_SESSION["message-currency_cost"] = "Данные о стоимости валюты обновлены.";
	header("Location: ../acc.php#change_currency_cost");
	
?>