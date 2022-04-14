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
		$_SESSION["message-transaction_in"] = "Выберите разные счета.";
        	header("Location: ../operwork.php#transaction_in");
		return;
	}
	if (check_balance($_POST["debit_accountnum"]) - $_POST["sum"] < 0) {
		$_SESSION["message-transaction_in"] = "Перевод не выполнен. Недостаточно средств.";
		header("Location: ../operwork.php#transaction_in");
		return;
	}	
	if ($debit_currency != $credit_currency) {
		$stmt->prepare("SELECT accountnum FROM account WHERE accountnum LIKE '30303%' AND currency = ?");
		$stmt->bind_param("s", $debit_currency);
		$stmt->execute();
		$convert_debit_accountnum = $stmt->get_result()->fetch_row()[0];

		$stmt->bind_param("s", $credit_currency);
		$stmt->execute();
		$convert_credit_accountnum = $stmt->get_result()->fetch_row()[0]; 

		$mysqli->query("BEGIN");
	
		$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
			"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
		$sum = standart_sum($_POST["sum"]);	
		$stmt->bind_param("ssss", $_POST["debit_accountnum"], $convert_debit_accountnum, $sum, $_SESSION["user"]["login"]);
		$stmt->execute();

		$conv_sum = convert_sum($sum, $debit_currency, $credit_currency);
		$stmt->bind_param("ssss", $convert_credit_accountnum, $_POST["credit_accountnum"], $conv_sum, $_SESSION["user"]["login"]);
		$stmt->execute();

                $mysqli->query("COMMIT");

	        $_SESSION["message-transaction_in"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../operwork.php#transaction_in");
		return;
	}
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
	$sum = standart_sum($_POST["sum"]);	
	$stmt->bind_param("ssss", $_POST["debit_accountnum"], $_POST["credit_accountnum"], $sum, $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-transaction_in"] = "Перевод не выполнен. Попробуйте позже.";
		header("Location: ../operwork.php#transaction_in");
		return;
	}	
	$_SESSION["message-transaction_in"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction_in");
		
?>