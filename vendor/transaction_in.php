<?php
	require "lib.php";
	safe_session_start();      
	
	$mysqli = get_sql_connection();

	$src_accountnum = $_POST["debit_accountnum"];
	$dst_accountnum = $_POST["credit_accountnum"];
	$sum = round_sum($_POST["sum"]);
	$user = $_SESSION["user"]["login"];

	$src_currency = get_account_currency($src_accountnum, $mysqli);
	$dst_currency = get_account_currency($dst_accountnum, $mysqli);

	if ($src_currency != $dst_currency) { // разные валюты => конвертация
		$mysqli->query("BEGIN");
		$res = conversion($src_accountnum, $dst_accountnum, $sum, $user, $mysqli);
		if ($res != "") {
			$mysqli->query("ROLLBACK");
			$_SESSION["message-transaction_in"] = "Ошибка перевода. $res";
			header("Location: ../operwork.php#transaction_in");
			return;
		}
		$mysqli->query("COMMIT");
                $_SESSION["message-transaction_in"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../operwork.php#transaction_in");
		return;
	}

	$res = transaction($dst_accountnum, $src_accountnum, $sum, $user, $mysqli);
	if ($res != "")	{
		$_SESSION["message-transaction_in"] = "Ошибка перевода. $res";
        	header("Location: ../operwork.php#transaction_in");
		return;	
	}

	$_SESSION["message-transaction_in"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction_in");
		
?>