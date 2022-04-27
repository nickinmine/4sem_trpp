<?php
	require "lib.php";	
	safe_session_start();

	$src_accountnum = $_POST["debit_accountnum"];
	$dst_phone = standart_phone($_POST["credit_phone"]);
	$sum = round_sum($_POST["sum"]);
	$user = $_SESSION["user"]["login"];
	
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT id FROM clients WHERE phone = ?");
	
	$stmt->bind_param("s", $dst_phone);
	$stmt->execute();
	$dst_id = $stmt->get_result()->fetch_row()[0];  	
	if ($dst_id == "") {
		$_SESSION["message-transaction_out"] = "Клиент с таким номером телефона не найден.";
		header("Location: ../operwork.php#transaction_out");
		return;
	}
	if ($_SESSION["client"]["id"] == $dst_id) {
		$_SESSION["message-transaction_out"] = "Перевод себе недоступен по номеру телефона.";
		header("Location: ../operwork.php#transaction_out");
		return;
	}
	$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND currency = (" .
		"SELECT currency FROM account WHERE accountnum = ? AND closed = '0000-00-00' " .
		") AND closed = '0000-00-00' AND `default` = 1");
	$stmt->bind_param("ss", $dst_id, $src_accountnum);
	$stmt->execute();
	$dst_accountnum = $stmt->get_result()->fetch_row()[0];
	//addlog("dst_accountnum = $dst_accountnum");

	if ($dst_accountnum == "") { // счет в той же валюте не найден
		$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND closed = '0000-00-00' AND `default` = 1");
		$stmt->bind_param("s", $dst_id);
		$stmt->execute();
		$dst_accountnum = $stmt->get_result()->fetch_row()[0];
	        if ($dst_accountnum == "") {
			$_SESSION["message-transaction_out"] = "У клиента нет подходящего счета для принятия перевода.";
			header("Location: ../operwork.php#transaction_out");
			return;
		}
		$mysqli->query("BEGIN");
		$res = conversion($src_accountnum, $dst_accountnum, $sum, $user, $mysqli);
		if ($res != "") {
			$mysqli->query("ROLLBACK");
			$_SESSION["message-transaction_out"] = "Ошибка перевода. $res";
			header("Location: ../operwork.php#transaction_out");
			return;
		}
		$mysqli->query("COMMIT");
                $_SESSION["message-transaction_out"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../operwork.php#transaction_out");
		return;                               
	}                                                          
	$res = transaction($dst_accountnum, $src_accountnum, $sum, $user);
	if ($res != "") {
		$_SESSION["message-transaction_out"] = "Перевод не выполнен. $res";
	        header("Location: ../operwork.php#transaction_out");
		return;	
	}		
	$_SESSION["message-transaction_out"] = "Успешный перевод.";
        header("Location: ../operwork.php#transaction_out");
		
?>