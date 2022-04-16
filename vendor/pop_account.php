<?php
	require "lib.php";

	safe_session_start();                            
	
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = 1 AND accountnum LIKE '20202%' AND currency = (" .
		"SELECT currency FROM account WHERE accountnum = ? AND closed = '0000-00-00'" .
		") AND closed = '0000-00-00'");
	$stmt->bind_param("s", $_POST["debit_accountnum"]);
	$stmt->execute();
	$credit_accountnum = $stmt->get_result()->fetch_row()[0]; // счет кассы 	
	if ($credit_accountnum == "") {
		$_SESSION["message-pop"] = "Не найден счет кассы.";
		header("Location: ../operwork.php#pop_account");
		return;                               
	}
	$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
		"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
	$sum = standart_sum($_POST["sum"]);	
	$stmt->bind_param("ssss", $_POST["debit_accountnum"], $credit_accountnum, $sum, $_SESSION["user"]["login"]);
	if (!$stmt->execute()) {
		$_SESSION["message-pop"] = "Снятие не выполнено. Попробуйте позже.";
		header("Location: ../operwork.php#pop_account");
		return;
	}	
	$_SESSION["message-pop"] = "Снятие средств выполнено.";
        header("Location: ../operwork.php#pop_account");
		
?>