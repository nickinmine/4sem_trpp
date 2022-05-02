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
	try {
		//addlog("пересчет баланса $current_date");
		// пересчет остатков на счетах
		$stmt = $mysqli->prepare(
			"SELECT DISTINCT db FROM operations WHERE operdate >= ? ".
			"  UNION " . 
			"SELECT DISTINCT cr FROM operations WHERE operdate >= ?");
		$stmt->bind_param("ss", $current_date, $current_date);
		$stmt->execute();
		$accounts = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        
		foreach ($accounts as $acc) {
			$acccurr = "";
			$acctype = "";
			$acc_balance = check_balance2($acc[0], $acccurr, $acctype, $mysqli) * sign_acctype($acctype);
			//addlog("balance " . $acc[0] . ": $acc_balance");
			$stmt->prepare("DELETE FROM balance WHERE account = ? AND dt = ?");
			$stmt->bind_param("ss", $acc[0], $current_date);
			verify($stmt->execute(), "Ошибка 1 сохранения баланса по счету " . $acc[0]);
			$stmt->prepare("INSERT INTO balance (account, dt, sum) VALUES (?, ?, ?)");
			$stmt->bind_param("ssd", $acc[0], $current_date, $acc_balance);
			verify($stmt->execute(), "Ошибка 2 сохранения баланса по счету " . $acc[0]);
		}
		
		// обновление даты
		$mysqli->query("UPDATE operdays SET current = 0 WHERE current = 1");
		$stmt = $mysqli->prepare("INSERT INTO operdays (operdate, current) VALUES (?, 1)");
		$stmt->bind_param("s", $new_date);
		verify($stmt->execute(), "Ошибка установки новой даты операционного дня");

		// пересчет процентов по вкладам
		$result = $mysqli->query("SELECT id FROM deposits WHERE closedate = '0000-00-00'");
		$deposits = array();
		while ($row = $result->fetch_assoc())
			$deposits[] = $row;
		foreach ($deposits as $dep) {
			$id = $dep["id"];
			//addlog("Расчет вклада " . $id);
			$res = update_deposit($id, $new_date, $_SESSION["user"]["login"], $mysqli);
			//addlog("Результат " . $res);
			verify($res == "", "Ошибка при пересчете вклада $id.\n$res");
		}

		// погашение кредитов
		addlog("// погашение кредитов");
		$result = $mysqli->query("SELECT id FROM credits WHERE (closedate = '0000-00-00' OR closedate IS NULL)");
		$credits = [];
		while ($row = $result->fetch_assoc())
			$credits[] = $row;
		addlog("кол-во: " . count($credits));
		foreach ($credits as $cred) {
			$id = $cred["id"];
			addlog("Расчет кредита " . $id);
			$res = update_credit($id, $new_date, $_SESSION["user"]["login"], $mysqli);
			//addlog("Результат " . $res);
			verify($res == "", "Ошибка при пересчете кредита $id.\n$res");
		}

		// сохранение всех изменений
		$mysqli->query("COMMIT");                                                             
		$_SESSION['message-operdate'] = "Установлена дата " . date("d.m.Y", strtotime($new_date));
		header('Location: ../acc.php#change_operdate');
	}
	catch (Exception $e) {
		$mysqli->query("ROLLBACK");
		addlog("Ошибка change_operdate(): " . $e->getMessage());
		$_SESSION['message-operdate'] = $e->getMessage();
		header('Location: ../acc.php#change_operdate');
	}

?>