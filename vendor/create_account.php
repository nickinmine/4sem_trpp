<?php
	session_start();

	require "lib.php";

	$idclient = (int)$_SESSION["client"]["id"];
	$currency = $_POST["currency"];
	$accountnum = generate_accountnum("40800", $currency);
	
	$mysqli = get_sql_connection();
	
	$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
	$stmt->bind_param("is", $idclient, $currency);
	$stmt->execute();
	$cntaccount = $stmt->get_result()->fetch_row()[0];

	$default = 1; // счет по умолчанию для приема переводов
	if ($cntaccount > 0)
		$default = 0;

	$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript, `default`) VALUES (?, ?, ?, ?, ?)");
	
	$descript = "Счет физ. лица";
        $stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript, $default);

	if (!$stmt->execute()) {
		$_SESSION['message-client'] = "Счет не создан.";
		header('Location: ../operwork.php#create_account');
		return;
	}
	$_SESSION['message-client'] = "Счет успешно создан.";
	header('Location: ../operwork.php#create_account');

?>