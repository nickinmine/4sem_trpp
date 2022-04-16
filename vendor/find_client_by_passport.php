<?php
	require "lib.php";

	safe_session_start();      

	$mysqli = get_sql_connection();

	$stmt = $mysqli->prepare("SELECT id FROM clients WHERE passport = ?");
	$stmt->bind_param("s", $_POST["passport"]);
	$stmt->execute();	
	$id = $stmt->get_result()->fetch_row()[0];
	if ($id == "") {
		$_SESSION["message-client"] = "Нет клиента с таким паспортом.";
		header('Location: ../oper.php');
	}
	else {  
		$_SESSION["client"] = [ "id" => $id ];                                           
		header('Location: ../operwork.php');
	}
	
?>