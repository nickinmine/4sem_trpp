<?php
	require "lib.php";

	session_start();

	if (substr($_POST["accountnum"], 0, 5) != "40800") {
		$_SESSION["message-close"] = "Невозможно закрыть служебный счет.";
		header("Location: ../operwork.php#close_account");
		return;
	}
	if (check_balance($_POST["accountnum"]) != 0) {
		$_SESSION["message-close"] = "Закрыть можно только пустой счет.";
		header("Location: ../operwork.php#close_account");
		return;
	}
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT currency, `default`, idclient FROM account WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["accountnum"]);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_row();
	$currency = $data[0];
	$default = $data[1];
	$isclient = $data[2];
	
	$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
	$stmt->bind_param("is", $idclient, $currency);
	$stmt->execute();
	$cntaccount = $stmt->get_result()->fetch_row()[0];

	$mysqli->query("BEGIN");

	if ($default == 1 && $cntaccount > 1) {
		$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND `default` = 0 AND currency = ? LIMIT 1");
		$stmt->bind_param("is", $idlient, $currency);
		$stmt->execute();
		$new_default = $stmt->get_result()->fetch_row()[0];
	
		$stmt = $mysqli->prepare("UPDATE account SET `default` = 1 WHERE accountnum = ?");
		$stmt->bind_param("s", $new_default);
		$stmt->execute();	
	}
	$stmt = $mysqli->prepare("UPDATE account SET `default` = 0 WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["accountnum"]);
	$stmt->execute();

	$stmt = $mysqli->prepare("UPDATE account SET closed = (SELECT operdate FROM operdays WHERE current = 1) WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["accountnum"]);
	$stmt->execute();
	
	$mysqli->query("COMMIT");

	$_SESSION["message-close"] = "Счет закрыт.";
	header("Location: ../operwork.php#close_account");
			
?>