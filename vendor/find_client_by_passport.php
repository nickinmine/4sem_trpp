<?php
	session_start();

	require "lib.php";      

	$mysqli = get_sql_connection();
	$result = $mysqli->query("SELECT id FROM clients WHERE passport = '" . $_POST["passport"] . "'");
	$id = $result->fetch_row()[0];
	if ($id == "") {
		$_SESSION["message-client"] = "Нет клиента с таким паспортом.";
		header('Location: ../oper.php');
	}
	else {  
		$_SESSION["client"] = [ "id" => $id ];                                           
		header('Location: ../operwork.php');
	}
	
?>