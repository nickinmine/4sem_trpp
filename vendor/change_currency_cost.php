<?php
	require "lib.php";

	safe_session_start();

	$mysqli = get_sql_connection();

	$buy_sum = standart_sum($_POST["buy_sum"]);
	$cost_sum = standart_sum($_POST["cost_sum"]);
	$sell_sum = standart_sum($_POST["sell_sum"]);
	
	$mysqli->query("BEGIN");

	$stmt = $mysqli->prepare("UPDATE converter SET current = 0 WHERE currency = ? AND current = 1");
	$stmt->bind_param("s", $_POST["currency"]);
	$stmt->execute();
	$stmt = $mysqli->prepare("INSERT INTO converter (currency, dt, buy, cost, sell) VALUES 
		(?, (SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?, ?)");
	$stmt->bind_param("ssss", $_POST["currency"], $buy_sum, $cost_sum, $sell_sum);
	$stmt->execute();

	$mysqli->query("COMMIT");

	$_SESSION["message-currency_cost"] = "Данные о стоимости валюты обновлены.";
	header("Location: ../acc.php#change_currency_cost");
	
?>