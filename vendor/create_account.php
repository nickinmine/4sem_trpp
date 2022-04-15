<?php
	session_start();

	require "lib.php";

	$idclient = (int)$_SESSION["client"]["id"];
	$currency = $_POST["currency"];
	$acc2p = "40800";
	$descript = "Счет физ. лица";
	
	$res = create_account($idclient, $currency, $acc2p, $descript);

	if ($res != "") {
		$_SESSION['message-client'] = "Ошибка при создании счета. " . $res;
		header('Location: ../operwork.php#create_account');
		return;
	}

	$_SESSION['message-client'] = "Счет успешно создан.";
	header('Location: ../operwork.php#create_account');

?>