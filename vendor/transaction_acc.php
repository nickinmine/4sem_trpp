<?php
	session_start();

	require "lib.php";      
	
	$mysqli = get_sql_connection();

	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["debit_accountnum"]);
	$stmt->execute();
	$debit_currency = $stmt->get_result()->fetch_row()[0];
        
	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["credit_accountnum"]);
	$stmt->execute();
	$credit_currency = $stmt->get_result()->fetch_row()[0];

	if ($_POST["debit_accountnum"] == $_POST["credit_accountnum"]) {
		$_SESSION["message-transaction_acc"] = "Выберите разные счета.";
        	header("Location: ../acc.php#transaction_acc");
		return;
	}	
	if ($credit_currency != $debit_currency) {
        	$_SESSION["message-transaction_acc"] = "Выберите счета с одинаковой валютой.";
        	header("Location: ../acc.php#transaction_acc");
		return;
	}
	if (check_balance($_POST["debit_accountnum"]) - $_POST["sum"] < 0) {
		$_SESSION["message-transaction_acc"] = "Перевод не выполнен. Недостаточно средств.";
		header("Location: ../acc.php#transaction_acc");
		return;
	}
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
	$sum = standart_sum($_POST["sum"]);	
	$stmt->bind_param("ssss", $_POST["debit_accountnum"], $_POST["credit_accountnum"], $sum, $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-transaction_acc"] = "Перевод не выполнен. Попробуйте позже.";
		header("Location: ../acc.php#transaction_acc");
		return;
	}	
	$_SESSION["message-transaction_acc"] = "Успешный перевод.";
        header("Location: ../acc.php#transaction_acc");
		
?>