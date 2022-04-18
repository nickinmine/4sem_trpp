<?php
	require "lib.php";

	safe_session_start();       
	
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
		conversion($_POST["debit_accountnum"], $_POST["credit_accountnum"], $_POST["sum"], $_SESSION["user"]["login"]);
                $_SESSION["message-transaction_in"] = "Успешный перевод с конвертацией валют.";
        	header("Location: ../acc.php#transaction_acc");
		return;
	}
	$res = transaction($_POST["debit_accountnum"], $_POST["credit_accountnum"], $_POST["sum"], $_SESSION["user"]["login"]);
	if ($res != "") {
		$_SESSION["message-transaction_acc"] = "Ошибка перевода." . $res;
        	header("Location: ../acc.php#transaction_acc");
		return;
	}	
	$_SESSION["message-transaction_acc"] = "Успешный перевод.";
        header("Location: ../acc.php#transaction_acc");
		
?>