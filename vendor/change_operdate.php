<?php
	require "lib.php";

	safe_session_start();

        $mysqli = get_sql_connection();
	$current_date = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row()[0];
	$new_date = $_POST["date"];
	if ($new_date <= $current_date) {
		$_SESSION['message-operdate'] = "Некорректная новая дата.";
		header('Location: ../acc.php#change_operdate');
		return;
	}
	$mysqli->query("BEGIN");

	// пересчет остатков на счетах, процентов по вкладам, погашению кредитов...
	$stmt = $mysqli->prepare("SELECT DISTINCT db FROM operations WHERE operdate >= ? UNION " . 
		"SELECT DISTINCT cr FROM operations WHERE operdate >= ?");
	$stmt->bind_param("ss", $current_date, $current_date);
	$stmt->execute();
	$accounts = $stmt->get_result()->fetch_all(MYSQLI_NUM);

	foreach ($accounts as $acc) {
		$acc_balance = check_balance($acc[0]);
		$stmt->prepare("INSERT INTO balance (account, dt, sum) VALUES (?, ?, ?)");
		$stmt->bind_param("ssd", $acc[0], $current_date, $acc_balance);
		$stmt->execute();
	}
	// пересчет процентов по вкладам
	$deposits = $mysqli->query("SELECT id FROM deposits");
	foreach ($deposits as $id) {
		$res = update_deposit($id, $new_date, $_SESSION["user"]["login"]);
		if ($res != "") {
			$_SESSION['message-operdate'] = "Ошибка при пересчете вкладов.\n" . $res;
			header('Location: ../acc.php#change_operdate');
		}
	}
	// обновление даты
	$mysqli->query("UPDATE operdays SET current = 0 WHERE current = 1");
	$stmt = $mysqli->prepare("INSERT INTO operdays (operdate, current) VALUES (?, 1)");
	$stmt->bind_param("s", $new_date);
	$stmt->execute();
	
	$mysqli->query("COMMIT");                                                             

	$_SESSION['message-operdate'] = "Установлена дата " . date("d.m.Y", strtotime($new_date));
	header('Location: ../acc.php#change_operdate');

?>