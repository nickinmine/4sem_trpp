<?php
	require "lib.php";

	safe_session_start();

	if (substr($_POST["accountnum"], 0, 5) != "40800") {
		$_SESSION["message-close"] = "Невозможно закрыть служебный счет.";
		header("Location: ../operwork.php#close_account");
		return;
	}
	
	$mysqli = get_sql_connection();
		
	$mysqli->query("BEGIN");
        $res = close_account($_POST["accountnum"]);
	
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