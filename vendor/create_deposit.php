<?php
	require "lib.php";

	safe_session_start();

	$type = $_POST["type"];
	$debit_accountnum = $_POST["debit_accountnum"];
	$sum = $_POST["sum"];

	$mysqli = get_sql_connection();

	$stmt = $mysqli->prepare("SELECT currency FROM depositeterms WHERE `type` = ?");
	$stmt->bind_param("s", $type);
	$stmt->execute();
	$deposit_currency = $stmt->get_result()->fetch_row()[0];

	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $debit_accountnum);
	$stmt->execute();
	$accountnum_currency = $stmt->get_result()->fetch_row()[0];

	if ($deposit_currency != $accountnum_currency) {
		$_SESSION["message-create_deposit"] = "Валюта выбранного вклада и счета не совпадают.";
		header("Location: ../operwork.php#create_deposit");
		return;
	}

	$mysqli->query("BEGIN");
	$res = create_deposit($type, $debit_accountnum, $sum, $_SESSION["user"]["login"]);
	if ($res != "") {
		$mysqli->query("ROLLBACK");
		$_SESSION["message-create_deposit"] = "Ошибка при открытии вклада.\n" . $res;
		header("Location: ../operwork.php#create_deposit");
		return;	
	}
	$mysqli->query("COMMIT");

	$_SESSION["message-create_deposit"] = "Вклад открыт.";
	header("Location: ../operwork.php#create_deposit");
		
?>