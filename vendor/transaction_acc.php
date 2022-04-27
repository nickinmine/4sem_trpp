<?php
	require "lib.php";
	safe_session_start();       
	
	$mysqli = get_sql_connection();

	$debit_currency = get_account_currency($_POST["debit_accountnum"], $mysqli);
	$credit_currency = get_account_currency($_POST["credit_accountnum"], $mysqli);

	if ($credit_currency != $debit_currency) {
		//conversion($_POST["debit_accountnum"], $_POST["credit_accountnum"], $_POST["sum"], $_SESSION["user"]["login"]);
		//$_SESSION["message-transaction_in"] = "Успешный перевод с конвертацией валют.";
		// конвертация по курсу покупки/продажи не имеет смысла, т.к. доходы с курсовой разницы все равно попадут на счет банка
		// вместо этого лучше сделать перевод между нужными счетами и счетами доходов/расходов банка с соответствующей валютой.
		$_SESSION["message-transaction_acc"] = "Выберите счета с одной и той же валютой."; 
		header("Location: ../acc.php#transaction_acc");
		return;
	}
	$res = transaction($_POST["debit_accountnum"], $_POST["credit_accountnum"], round_sum($_POST["sum"]), $_SESSION["user"]["login"], $mysqli);
	if ($res != "") {
		$_SESSION["message-transaction_acc"] = "Ошибка перевода." . $res;
        	header("Location: ../acc.php#transaction_acc");
		return;
	}	
	$_SESSION["message-transaction_acc"] = "Успешный перевод.";
        header("Location: ../acc.php#transaction_acc");
		
?>