<?php
	require "lib.php";
	safe_session_start();
	
	$id = $_POST["idcred"]; // идентификатор кредита
	$user = $_SESSION["user"]["login"];

	$mysqli = get_sql_connection();

	$mysqli->query("BEGIN");
	try {
		// определим задолженность по кредиту
		$z = NULL;
		verify(credit_tail_sum($id, $z, $mysqli) == "", "Ошибка определения задолженности по кредиту $id");
		//verify($z["total"] == 0.00, "По кредиту имеется задолженность в размере " . standart_sum($z["total"]));

		// получим данные по договору
		$stmt = $mysqli->prepare("SELECT * FROM credits WHERE id = ?");
		$stmt->bind_param("i", $id);
		verify($stmt->execute(), "MySQL error: " . $mysqli->error);
		$cred = $stmt->get_result()->fetch_assoc();
		verify($cred, "Не найдена информация по договору $id");
		verify($cred["closedate"] == "0000-00-00" || $cred["closedate"] == NULL, "Договор $id уже был закрыт " . out_date($cred["closedate"]));

		// определение остатка на текущем счете
		$curbal = check_balance($cred["curacc"], $mysqli);
		verify($curbal >= $z["total"], "Недостаточно средств на тек. счете для полного погашения кредита $id: " . standart_sum($z["total"] - $curbal));

		// счет доходов банка
		$src_bank_accountnum = "";
		$res = find_bank_account($cred["curacc"], "70601%0001", $src_bank_accountnum, $mysqli);
		verify($res == "", "Не найден счет доходов банка: $res");

		// доначисление процентов и гашение остатков задолженности
		if ($z["od"] > 0.00) { // гашение осн. долга
			$res = transaction($cred["odacc"], $cred["curacc"], $z["od"], $user, $mysqli);
			verify($res == "", "Ошибка гашения осн. долга: $res");
		}
		if ($z["pc2"] > 0.00) { // доначисление процентов с даты посл. обновления
			$res = transaction($src_bank_accountnum, $cred["pcacc"], $z["pc2"], $user, $mysqli);
			verify($res == "", "Ошибка при начислении процентов на осн. долг: $res");
		}
		if ($z["pc"] + $z["pc2"] > 0.00) { // гашение процентов
			$res = transaction($cred["pcacc"], $cred["curacc"], round_sum($z["pc"] + $z["pc2"]), $user, $mysqli);
			verify($res == "", "Ошибка при гашении осн. %%: $res");
		}
		if ($z["prod"] > 0.00) { // гашение проср. долга
			$res = transaction($cred["prodacc"], $cred["curacc"], $z["prod"], $user, $mysqli);
			verify($res == "", "Ошибка гашения проср. долга: $res");
		}
		if ($z["prpc2"] > 0.00) { // доначисление проср. процентов с даты посл. обновления
			$res = transaction($src_bank_accountnum, $cred["prpcacc"], $z["prpc2"], $user, $mysqli);
			verify($res == "", "Ошибка при начислении процентов на проср. долг: $res");
		}
		if ($z["prpc"] + $z["prpc2"] > 0.00) { // гашение проср. процентов
			$res = transaction($cred["prpcacc"], $cred["curacc"], round_sum($z["prpc"] + $z["prpc2"]), $user, $mysqli);
			verify($res == "", "Ошибка при гашении проср. %%: $res");
		}

		// непосредственно закрытие кредита
		$res = close_credit($id, $mysqli);
		verify($res == "", "Ошибка закрытия кредита: $res");

		$mysqli->query("COMMIT");
	}
	catch (Exception $e) { // произошла ошибка!
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_credit"] = $e->getMessage();
		header("Location: ../operwork.php#close_credit");
		return;
	}

	$_SESSION["message-close_credit"] = "Кредит закрыт.";
	header("Location: ../operwork.php#close_credit");
			
?>