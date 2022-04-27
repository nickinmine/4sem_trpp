<?php
	require "lib.php";
	safe_session_start();                            
	
	$mysqli = get_sql_connection();

	$user = $_SESSION["user"]["login"];
	$user_accountnum = $_POST["debit_accountnum"];
	$cassa_accountnum = "";
	$sum = round_sum($_POST["sum"]);

	$res = find_bank_account($user_accountnum, "20202%", $cassa_accountnum, $mysqli);
	if ($res != "") {
		$_SESSION["message-pop"] = "Не найден счет кассы. " . $res;
		header("Location: ../operwork.php#pop_account");
		return;                               
	}
	//addlog("Счет кассы: $cassa_accountnum");

	$res = transaction($cassa_accountnum, $user_accountnum, $sum, $user, $mysqli);
	if ($res != "") {
		$_SESSION["message-pop"] = "Снятие не выполнено. " . $res;
		header("Location: ../operwork.php#pop_account");
		return;
	}

	$_SESSION["message-pop"] = "Снятие средств выполнено.";
        header("Location: ../operwork.php#pop_account");
?>
