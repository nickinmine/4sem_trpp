<?php
	require "lib.php";

	safe_session_start();

	$type = $_POST["type"];
	$sum = $_POST["sum"];
	$idclient = $_SESSION["client"]["id"];
	if ($idclient == NULL) {
		$_SESSION["message-create_credit"] = "Ошибка при выдаче кредита.\nНе определен клиент";
		header("Location: ../operwork.php#create_credit");
		return;	
	}

	$mysqli = get_sql_connection();

	$mysqli->query("BEGIN");

	$res = create_credit($type, $sum, $_SESSION["user"]["login"], $idclient, $mysqli);
	if ($res != "") {
		$mysqli->query("ROLLBACK");
		$_SESSION["message-create_credit"] = "Ошибка при выдаче кредита.\n" . $res;
		header("Location: ../operwork.php#create_credit");
		return;	
	}
	$mysqli->query("COMMIT");

	$_SESSION["message-create_credit"] = "Кредит выдан.";
	header("Location: ../operwork.php#create_credit");
		
?>