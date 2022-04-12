<?php
	session_start();

	require "lib.php";      
	
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = 1 AND accountnum LIKE '20202%' AND currency = (" .
		"SELECT currency FROM account WHERE accountnum = ? AND closed = '0000-00-00'" .
		") AND closed = '0000-00-00'");
	$stmt->bind_param("s", $_POST["credit_accountnum"]);
	$stmt->execute();
	$debit_accountnum = $stmt->get_result()->fetch_row()[0]; // счет кассы 	
	if ($debit_accountnum == "") {
		$_SESSION["message-push"] = "Не найден счет кассы." . $_POST["credit_accountnum"];
		header("Location: ../operwork.php#push_account");
		return;                               
	}
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
	$sum = standart_sum($_POST["sum"]);	
	$stmt->bind_param("ssss", $debit_accountnum, $_POST["credit_accountnum"], $sum, $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-push"] = "Пополнение не выполнено. Попробуйте позже.";
		header("Location: ../operwork.php#push_account");
		return;
	}	
	$_SESSION["message-push"] = "Счет пополнен.";
        header("Location: ../operwork.php#push_account");
		
?>