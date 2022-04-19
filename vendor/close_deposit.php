<?php
	require "lib.php";

	safe_session_start();
	
	$id = $_POST["id"];
	$acc = $_POST["accountnum"];

	$mysqli = get_sql_connection();

	$mysqli->query("BEGIN");
        
	$stmt = $mysqli->prepare("SELECT mainacc, percacc, capdate FROM deposits WHERE id = ?");	
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_row();
	$mainacc = $result[0];
	$sum = check_balance($mainacc);
	$percacc = $result[1];
	$capdate = $result[2];
	
	//addlog($mainacc . " " . $percacc . " " . $update);
	//addlog($id);
	
	$operdate = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row()[0];

	$oldsum = check_balance($percacc);
	if ($oldsum > 0) {
		$res = transaction($percacc, "70601810500000000001", $oldsum, $_SESSION["user"]["login"]);
		if ($res != "") { 
			$mysqli->query("ROLLBACK");
			$_SESSION["message-close_deposit"] = "Внутренняя транзакция 0 не проведена. " . $res;
			header("Location: ../operwork.php#close_deposit");
			return;
		}
	}		
        //addlog($operdate . " > " . $update);
                                            
	if ($operdate > $capdate) {
		$rate = $mysqli->query("SELECT rate FROM depositeterms WHERE `type` = 'dv'")->fetch_row()[0];	
		$dvsum = standart_sum(check_balance($mainacc) * diff_date($capdate, $operdate) * $rate / 100 / 365);
	
		addlog("dvsum = " . $dvsum);

		$res = transaction("70601810500000000001", $percacc, $dvsum, $_SESSION["user"]["login"]);
		if ($res != "") { 
			$mysqli->query("ROLLBACK");
			$_SESSION["message-close_deposit"] = "Внутренняя транзакция 1 не проведена. " . $res;
			header("Location: ../operwork.php#close_deposit");
			return;
		}
		$res = transaction($percacc, $mainacc, $dvsum, $_SESSION["user"]["login"]);
		if ($res != "") { 
			$mysqli->query("ROLLBACK");
			$_SESSION["message-close_deposit"] = "Внутренняя транзакция 2 не проведена. " . $res;
			header("Location: ../operwork.php#close_deposit");
			return;
		}
		$sum += $dvsum;
	}
	$res = transaction($mainacc, $acc, $sum, $_SESSION["user"]["login"]);		
	if ($res != "") { 
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_deposit"] = "Транзакция на внешний счет не проведена. " . $res;
		header("Location: ../operwork.php#close_deposit");
		return;
	}

	$res = close_account($mainacc);
	if ($res != "") { 
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_deposit"] = "Основной счет вклада не закрыт. " . $res;
		header("Location: ../operwork.php#close_deposit");
		return;
	}
	$res = close_account($percacc);
	if ($res != "") { 
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_deposit"] = "Дополнительный счет вклада не закрыт. " . $res;
		header("Location: ../operwork.php#close_deposit");
		return;
	}
	$stmt = $mysqli->prepare("UPDATE deposits SET closedate = ? WHERE id = ?");
	$stmt->bind_param("si", $operdate, $id);
	if (!$stmt->execute()) {
		$mysqli->query("ROLLBACK");
		$_SESSION["message-close_deposit"] = "Вклад не закрыт.";
		header("Location: ../operwork.php#close_deposit");
		return;
	}

	$mysqli->query("COMMIT");

	$_SESSION["message-close_deposit"] = "Вклад закрыт.";
	header("Location: ../operwork.php#close_deposit");
			
?>