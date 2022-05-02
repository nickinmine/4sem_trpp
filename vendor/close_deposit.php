<?php
	require "lib.php";
	safe_session_start();
	
	$id = $_POST["id"];
	$outacc = $_POST["accountnum"];
	$user = $_SESSION["user"]["login"];

	$mysqli = get_sql_connection();

	$mysqli->query("BEGIN");
	try {
		$stmt = $mysqli->prepare("SELECT mainacc, percacc, IFNULL(capdate, opendate) capdate FROM deposits WHERE id = ?");	
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		$mainacc = $result["mainacc"];
		$sum = check_balance($mainacc);
		$percacc = $result["percacc"];
		$capdate = $result["capdate"];
		
		$row = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row();
		verify($row, "Не найден текущий рабочий день");
		$operdate = $row[0];
        
		$src_bank_accountnum = "";
		$res = find_bank_account($mainacc, "70601".$currency."%0001", $src_bank_accountnum, $mysqli);
		verify($res == "", "Не найден счет расходов банка. $res");
        
		$oldsum = check_balance($percacc, $mysqli);
		if ($oldsum > 0) { // "возврат" начисленных процентов после последней капитализации
			$res = transaction($src_bank_accountnum, $percacc, $oldsum, $user, $mysqli);
			verify($res == "", "Внутренняя транзакция 0 не проведена. $res");
		}		
                                                    
		if ($operdate > $capdate) { // после последней капитализации начисляем по ставке "до востребования"
			$rate = $mysqli->query("SELECT rate FROM depositeterms WHERE `type` = 'dv'")->fetch_row()[0];	
			$dvsum = round_sum(check_balance($mainacc) * diff_date($capdate, $operdate) * $rate / 100 / 365);
			//addlog("dvsum = " . $dvsum);
        
			$res = transaction($percacc, $src_bank_accountnum, $dvsum, $user, $mysqli);
			verify ($res == "", "Внутренняя транзакция 1 не проведена. $res");

			//addlog("$percacc balance = " . check_balance($percacc, $mysqli));
			$res = transaction($mainacc, $percacc, $dvsum, $user, $mysqli);
			verify($res == "", "Внутренняя транзакция 2 не проведена. $res");

			$sum += $dvsum;
		}

		$res = transaction($outacc, $mainacc, $sum, $user, $mysqli); // вывод средств со вклада
		verify($res == "", "Транзакция на внешний счет не проведена. $res");

		//addlog("Баланс основного счета ($mainacc): " . sprintf("%.6f", check_balance($mainacc, $mysqli)));
		$res = close_account($mainacc, $mysqli);
		verify($res == "", "Основной счет вклада не закрыт. " . $res);
		$res = close_account($percacc, $mysqli);
		verify($res == "", "Дополнительный счет вклада не закрыт. " . $res);

		$stmt = $mysqli->prepare("UPDATE deposits SET closedate = ? WHERE id = ?");
		$stmt->bind_param("si", $operdate, $id);
		verify($stmt->execute(), "Вклад не закрыт.");
	}
	catch (Exception $e) { // произошла ошибка!
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_deposit"] = $e->getMessage();
		header("Location: ../operwork.php#close_deposit");
		return;
	}
	$mysqli->query("COMMIT");

	$_SESSION["message-close_deposit"] = "Вклад закрыт.";
	header("Location: ../operwork.php#close_deposit");
			
?>