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

	function update_deposit($id, $newdate, $user) {
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
		if ($closedate != "0000-00-00")	{
			return "Этот вклад уже закрыт.";
		}
		$stmt = $mysqli->prepare("SELECT `modify` FROM capterms WHERE cap = (SELECT cap FROM depositeterms WHERE `type` = ?)");
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
		if ($type == "save1y") {
			$cnt = 0;
			while (modify_date($update, $modify) <= $newdate && modify_date($update, $modify) <= modify_date($opendate, "+" . $monthcnt . " month")) {
				$update = modify_date($update, $modify);
				$cnt++;
				$sum = standart_sum((check_balance($mainacc) + check_balance($percacc)) * $rate / 100 / 12);
				$res = transaction("70601810500000000001", $percacc, $sum, $user);
				if ($res != "") {
					return $mysqli->error;
				}
				
			}
			return "";
		}
		if ($type == "ben1y") {
			if ($update < modify_date($opendate, "+" . $monthcnt . " month")) {
				return "";
			}
			$sum = standart_sum(check_balance($mainacc) * $rate / 100 / 12);
			$res = transaction("70601810500000000001", $percacc, $sum, $user);
			if ($res != "") {
				return $mysqli->error;
			}
			return "";
		} 
		return $type;                            			
	}
	
?>