<?php
	session_start();

	require "lib.php";      
	
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT id FROM clients WHERE phone = ?");
	$credit_phone = standart_phone($_POST["credit_phone"]);
	$stmt->bind_param("s", $credit_phone);
	$stmt->execute();
	$credit_id = $stmt->get_result()->fetch_row()[0];  	
	if ($credit_id == "") {
		$_SESSION["message-transaction_out"] = "Клиент с таким номером телефона не найден.";
		header("Location: ../operwork.php#transaction");
		return;                               
	}
	if ($_SESSION["client"]["id"] == $credit_id) {
		$_SESSION["message-transaction_out"] = "Перевод себе недоступен по номеру телефона.";
		header("Location: ../operwork.php#transaction_out");
		return;
	}
	if (check_balance($_POST["debit_accountnum"]) - $_POST["sum"] < 0) {
		$_SESSION["message-transaction_out"] = "Перевод не выполнен. Недостаточно средств.";
		header("Location: ../operwork.php#transaction_out");
		return;
	}
	$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND currency = (" .
		"SELECT currency FROM account WHERE accountnum = ? AND closed = '0000-00-00' " .
		") AND closed = '0000-00-00' AND `default` = 1");
	$stmt->bind_param("ss", $credit_id, $_POST["debit_accountnum"]);
	$stmt->execute();
	$credit_accountnum = $stmt->get_result()->fetch_row()[0];	             
	if ($credit_accountnum == "") {
		$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND closed = '0000-00-00' AND `default` = 1");
		$stmt->bind_param("s", $credit_id);
		$stmt->execute();
		$credit_accountnum = $stmt->get_result()->fetch_row()[0];	             
	        if ($credit_accountnum == "") {
			$_SESSION["message-transaction_out"] = "У клиента нет подходящего счета для принятия перевода.";
			header("Location: ../operwork.php#transaction_out");
			return;
		}
		$stmt->prepare("SELECT currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $_POST["debit_accountnum"]);
		$stmt->execute();
		$debit_currency = $stmt->get_result()->fetch_row()[0];

		$stmt->bind_param("s", $credit_accountnum);
		$stmt->execute();
		$credit_currency = $stmt->get_result()->fetch_row()[0];
		
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
		$stmt->bind_param("ssss", $convert_credit_accountnum, $credit_accountnum, $conv_sum, $_SESSION["user"]["login"]);
		$stmt->execute();

                $mysqli->query("COMMIT");

	        $_SESSION["message-transaction_out"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../operwork.php#transaction_out");
		return;                               
	}                                                          
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
	$sum = standart_sum($_POST["sum"]);	
	$stmt->bind_param("ssss", $_POST["debit_accountnum"], $credit_accountnum, $sum, $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-transaction_out"] = "Перевод не выполнен. Попробуйте позже.";
		header("Location: ../operwork.php#transaction_out");
		return;
	}	
	$_SESSION["message-transaction_out"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction_out");
		
?>