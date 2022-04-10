<?php
	session_start();

	require "lib.php";

	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript) VALUES (?, ?, ?, ?)");
	
	$idclient = $_SESSION["client"]["id"];
	$currency = $_POST["currency"];
	$accountnum = generate_accountnum("40800", $currency);
	$descript = "Счет физ. лица";
        $stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript);

	if (!$stmt->execute()) {
		$_SESSION['message-client'] = "Счет не создан.";
		header('Location: ../operwork.php#create_account');
		return;
	}
	$_SESSION['message-client'] = "Счет успешно создан.";
	header('Location: ../operwork.php#create_account');

?>