<?php
	require_once "lib.php";
	
	function out_deposit_box() {
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT descript, `type` FROM depositeterms WHERE `type` NOT IN('dv')");
		$str = "";
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["type"] . "\">" . $res["descript"] . "</option>\n";
		}
		return $str;
	}

	function create_deposit($type, $debit_accountnum, $sum, $user) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT idclient, currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $debit_accountnum);
		if (!$stmt->execute())
			return $mysqli->error;
		$data = $stmt->get_result()->fetch_row();
		$idclient = $data[0];
		$currency = $data[1];
		$mainacc = "";
		$res = create_account($idclient, $currency, "42301", "Основной счет вклада", $mainacc);
		if ($res != "") {
			return $res;
		}                                
		$percacc = "";
		$res = create_account($idclient, $currency, "47411", "Дополнительный счет вклада", $percacc);
		if ($res != "") {
			return $res;
		}
		$res = transaction($debit_accountnum, $mainacc, $sum, $user);
		if ($res != "") {
			return $res;
		}
		addlog($idclient . " " . $type . " " . $mainacc . " " . $percacc);
		$stmt = $mysqli->prepare("INSERT INTO deposits (idclient, `type`, opendate, closedate, mainacc, percacc, `update`) VALUES (?, ?, " . 
			"(SELECT operdate FROM operdays WHERE current = 1), '0000-00-00', ?, ?, (SELECT operdate FROM operdays WHERE current = 1))");
		$stmt->bind_param("isss", $idclient, $type, $mainacc, $percacc);
		if (!$stmt->execute())
			return $mysqli->error;
		return "";
	}

	function update_deposit($id, $newdate, $user) { // вклады только в рублях
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT * FROM deposits WHERE id = ?");
		$stmt->bind_param("i", $id);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$res = $stmt->get_result()->fetch_row();
		$idclient = $res[1];
		$type = $res[2];
		$opendate = $res[3];
		$closedate = $res[4];
		$mainacc = $res[5];
		$percacc = $res[6];
		$update = $res[7];
		$stmt = $mysqli->prepare("SELECT cap FROM depositeterms WHERE `type` = ?");
		$stmt->bind_param("s", $type);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$modify = $stmt->get_result()->fetch_row()[0];
		$stmt = $mysqli->prepare("SELECT monthcnt FROM depositeterms WHERE `type` = ?");
		$stmt->bind_param("s", $type);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$monthcnt = $stmt->get_result()->fetch_row()[0];
                $stmt = $mysqli->prepare("SELECT rate FROM depositeterms WHERE `type` = ?");
		$stmt->bind_param("s", $type);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$rate = $stmt->get_result()->fetch_row()[0];
		//addlog("opendate = " . $opendate);
		//addlog("update = " . $update);
		$capdate = []; // даты капитализации
		if ($modify == "") {
			$capdate[1] = add_months($opendate, $monthcnt);
		}
		if ($modify == "+1 month") {
			for ($i = 1; $i <= $monthcnt; $i++) {
				$capdate[$i] = add_months($opendate, $i);
				//addlog("capdate = " . $capdate[$i]);
			}
		}
		if ($modify == "+3 month") {
			for ($i = 1; $i <= $monthcnt; $i += 3) {
				$capdate[$i] = add_months($opendate, $i);
				//addlog("capdate = " . $capdate[$i]);
			}
		}		
		$i = 1;
		while ($capdate[$i] <= $update && $i <= count($capdate))
			$i++;
		addlog("next capdate = " . $capdate[$i]);
		while ($capdate[$i] <= $newdate && $i <= count($capdate)) {
			//addlog("diff_date at " . $update . " to " . $capdate[$i]);
			//addlog("diff_date = " . diff_date($update, $capdate[$i]));
			$sum = standart_sum(diff_date($update, $capdate[$i]) * $rate / 100 / 365 * check_balance($mainacc));
			//addlog("sum to " . $capdate[$i] . " = " . $sum);
			$res = transaction("70601810500000000001", $percacc, $sum, $user);
			if ($res != "") {
				return $mysqli->error;
			}
			$percsum = check_balance($percacc);
			$res = transaction($percacc, $mainacc, $percsum, $user);
			if ($res != "") {
				return $mysqli->error;
			}
			$update = $capdate[$i];
			//addlog("update = " . $update);
			$i++;                
		}
		if ($i <= count($capdate)) {
			$sum = standart_sum(diff_date($update, $newdate) * $rate / 100 / 365 * check_balance($mainacc));
			if ($sum != 0.00) {
				$res = transaction("70601810500000000001", $percacc, $sum, $user);
				if ($res != "") {
					return $mysqli->error;
				}
			}
		}
		/*else { // до востребования
			$rate = $mysqli->query("SELECT rate FROM depositeterms WHERE `type` = 'dv'")->fetch_row()[0];	
			$sum = standart_sum(diff_date($update, $newdate) * $rate / 100 / 365 * check_balance($mainacc));
			if ($sum != 0.00) {
				$res = transaction("70601810500000000001", $percacc, $sum, $user);
				if ($res != "") {
					return $mysqli->error;
				}
			}
		}*/
		
		$stmt = $mysqli->prepare("UPDATE deposits SET `update` = ? WHERE id = ?");
		$stmt->bind_param("si", $newdate, $id);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		return "";                            			
	}
	
?>