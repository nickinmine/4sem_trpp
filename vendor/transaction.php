<?php
	session_start();

	require "lib.php";      
	
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT id FROM clients WHERE phone = ?");
	$stmt->bind_param("s", $_POST["credit_phone"]);
	$stmt->execute();
	$credit_id = $stmt->get_result()->fetch_row()[0];  	
	if ($credit_id == "") {
		$_SESSION["message-transaction"] = "Клиент с таким номером телефона не найден.";
		header("Location: ../operwork.php#transaction");
		return;                               
	}
	$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND currency = (" .
		"SELECT currency FROM account WHERE accountnum = ? AND closed = '0000-00-00' " .
		") AND closed = '0000-00-00' AND `default` = 1");
	$stmt->bind_param("ss", $credit_id, $_POST["debit_accountnum"]);
	$stmt->execute();
	$credit_accountnum = $stmt->get_result()->fetch_row()[0];  	
	if ($credit_accountnum == "") {
		$_SESSION["message-transaction"] = "У клиента нет подходящего счета для принятия перевода.";
		header("Location: ../operwork.php#transaction");
		return;                               
	}
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");	
	$stmt->bind_param("ssss", $_POST["debit_accountnum"], $credit_accountnum, $_POST["sum"], $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-transaction"] = "Перевод не выполнен. Попробуйте позже.";
		header("Location: ../operwork.php#transaction");
		return;
	}	
	$_SESSION["message-transaction"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction");
		
?>