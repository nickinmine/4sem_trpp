<?php
	require "lib.php";
	safe_session_start();

	$type = $_POST["type"];
	$src_accountnum = $_POST["debit_accountnum"];
	$sum = $_POST["sum"];
	$user = $_SESSION["user"]["login"];

	$mysqli = get_sql_connection();

	$stmt = $mysqli->prepare("SELECT currency FROM depositeterms WHERE `type` = ?");
	$stmt->bind_param("s", $type);
	$stmt->execute();
	$deposit_currency = $stmt->get_result()->fetch_row()[0];

	$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $src_accountnum);
	$stmt->execute();
	$accountnum_currency = $stmt->get_result()->fetch_row()[0];

	if ($deposit_currency != $accountnum_currency) {
		$_SESSION["message-create_deposit"] = "Валюта выбранного вклада и счета не совпадают.";
		header("Location: ../operwork.php#create_deposit");
		return;
	}

	$mysqli->query("BEGIN");
	$res = create_deposit($type, $src_accountnum, $sum, $user, $mysqli);
	if ($res != "") {
		$mysqli->query("ROLLBACK");
		$_SESSION["message-create_deposit"] = "Ошибка при открытии вклада.\n$res";
		header("Location: ../operwork.php#create_deposit");
		return;	
	}
	//$mysqli->query("ROLLBACK"); // !!! test
	$mysqli->query("COMMIT");

	$_SESSION["message-create_deposit"] = "Вклад открыт.";
	header("Location: ../operwork.php#create_deposit");
		
?>