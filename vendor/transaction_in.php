<?php
	require "lib.php";

	safe_session_start();      
	
	$mysqli = get_sql_connection();

	$debit_accountnum = $_POST["debit_accountnum"];
	$credit_accountnum = $_POST["credit_accountnum"];
	$sum = $_POST["sum"];

	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $debit_accountnum);
	$stmt->execute();
	$debit_currency = $stmt->get_result()->fetch_row()[0];
        
	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $credit_accountnum);
	$stmt->execute();
	$credit_currency = $stmt->get_result()->fetch_row()[0];

	if ($debit_accountnum == $credit_accountnum) {
		$_SESSION["message-transaction_in"] = "Выберите разные счета.";
        	header("Location: ../operwork.php#transaction_in");
		return;
	}
	if ($debit_currency != $credit_currency) {
		conversion($debit_accountnum, $credit_accountnum, $sum, $_SESSION["user"]["login"]);
                $_SESSION["message-transaction_in"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../operwork.php#transaction_in");
		return;
	}
	$res = transaction($debit_accountnum, $credit_accountnum, $sum, $_SESSION["user"]["login"]);
	if ($res != "")	{
		$_SESSION["message-transaction_in"] = "Ошибка перевода. " . $res;
        	header("Location: ../operwork.php#transaction_in");
		return;	
	}
	$_SESSION["message-transaction_in"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction_in");
		
?>