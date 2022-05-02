<?php
	require "lib.php";

	safe_session_start();

	$accountnum = $_POST["accountnum"];
	
	if (substr($accountnum, 0, 5) != "40817") {
		$_SESSION["message-close"] = "Невозможно закрыть служебный счет.";
		header("Location: ../operwork.php#close_account");
		return;
	}
	
	$mysqli = get_sql_connection();
		
	$mysqli->query("BEGIN");
	
	$stmt = $mysqli->prepare("SELECT count(*) FROM credits WHERE curacc = ? AND closedate IS NULL");
	$stmt->bind_param("s", $accountnum);
	$stmt->execute();
	$cnt = $stmt->get_result()->fetch_row()[0];
	if ($cnt == 1) {
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close"] = "Счет не закрыт, т.к. привязан к действующему кредиту.";
		header("Location: ../operwork.php#close_account");
		return;
	}

        $res = close_account($accountnum);
	
	if ($res != "") { 
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close"] = "Счет не закрыт. " . $res;
		header("Location: ../operwork.php#close_account");
		return;
	}
	$mysqli->query("COMMIT");

	$_SESSION["message-close"] = "Счет закрыт.";
	header("Location: ../operwork.php#close_account");
			
?>