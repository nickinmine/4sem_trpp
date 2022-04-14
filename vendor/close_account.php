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
	
	$stmt = $mysqli->prepare("UPDATE account SET closed = (SELECT operdate FROM operdays WHERE current = 1) WHERE accountnum = ?");
	$stmt->bind_param("s", $_POST["accountnum"]);
	$stmt->execute();
	
	$_SESSION["message-close"] = "Счет закрыт.";
	header("Location: ../operwork.php#close_account");
			
?>