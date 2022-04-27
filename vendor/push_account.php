<?php
	require "lib.php";	
	safe_session_start();                                
	
	$mysqli = get_sql_connection();

	$user = $_SESSION["user"]["login"];
        $user_accountnum = $_POST["credit_accountnum"];
	$cassa_accountnum = "";
	$sum = round_sum($_POST["sum"]);	

	$res = find_bank_account($user_accountnum, "20202%", $cassa_accountnum, $mysqli);
	if ($res != "") {
		$_SESSION["message-push"] = "Не найден счет кассы. " . $res;
		header("Location: ../operwork.php#push_account");
		return;                               
	}
	//addlog("Счет кассы: $cassa_accountnum");

	$res = transaction($user_accountnum, $cassa_accountnum, $sum, $user, $mysqli);
	if ($res != "") {
		$_SESSION["message-push"] = "Пополнение не выполнено. " . $res;
		header("Location: ../operwork.php#push_account");
		return;
	}

	$_SESSION["message-push"] = "Счет пополнен.";
        header("Location: ../operwork.php#push_account");
?>