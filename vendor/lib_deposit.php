<?php
	require_once "lib.php";
	
	function out_deposit_terms_box() {
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT descript, `type` FROM depositeterms WHERE `type` NOT IN('dv')");
		$str = "";
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["type"] . "\">" . $res["descript"] . "</option>\n";
		}
		return $str;
	}

	function create_deposit($type, $src_accountnum, $sum, $user, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare("SELECT idclient, currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $src_accountnum);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$data = $stmt->get_result()->fetch_row();
		$idclient = $data[0];
		$currency = $data[1];
		$mainacc = "";
		//addlog($idclient);
		$res = create_account($idclient, $currency, "42301", "Основной счет вклада", $mainacc, $mysqli);
		if ($res != "")
			return "Не удалось открыть счет 42301. $res";
		$percacc = "";
		$res = create_account($idclient, $currency, "47411", "Дополнительный счет вклада", $percacc, $mysqli);
		if ($res != "") {    
			return "Не удалось открыть счет 47411. $res";
		}
		$res = transaction($mainacc, $src_accountnum, $sum, $user, $mysqli);
		if ($res != "") {    
			return $res;
		}
		//addlog($idclient . " " . $type . " " . $mainacc . " " . $percacc);
		$stmt = $mysqli->prepare(
			"INSERT INTO deposits (idclient, `type`, opendate, closedate, mainacc, percacc, `update`) " .
			"VALUES (?, ?, (SELECT operdate FROM operdays WHERE current = 1), '0000-00-00', ?, ?, (SELECT operdate FROM operdays WHERE current = 1))");
		$stmt->bind_param("isss", $idclient, $type, $mainacc, $percacc);
		if (!$stmt->execute()) {       
			return "MySQL error: " . $mysqli->error;
		}
		return "";
	}

	function update_deposit($id, $newdate, $user, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare("SELECT * FROM deposits WHERE id = ?");
		$stmt->bind_param("i", $id);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		$res = $stmt->get_result()->fetch_assoc();
		$type = $res["type"];
		$opendate = $res["opendate"];
		$mainacc = $res["mainacc"];
		$percacc = $res["percacc"];
		$update = $res["update"];

		$stmt = $mysqli->prepare("SELECT cap, monthcnt, rate, currency FROM depositeterms WHERE `type` = ?");
		$stmt->bind_param("s", $type);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$res = $stmt->get_result()->fetch_assoc();
		$modify = $res["cap"];
		$monthcnt = $res["monthcnt"];
		$rate = $res["rate"];
		$currency = $res["currency"];

		$src_bank_accountnum = "";
		$res = find_bank_account($mainacc, "70601".$currency."%0001", $src_bank_accountnum, $mysqli);
		if ($res != "")
			return res;

		$capdate = []; // даты капитализации
		if ($modify == "") {
			$capdate[1] = add_months($opendate, $monthcnt);
		}
		if ($modify == "+1 month") {
			for ($i = 0; $i < $monthcnt; $i++)
				$capdate[count($capdate)+1] = add_months($opendate, $i + 1);
		}
		if ($modify == "+3 month") {
			for ($i = 0; $i < $monthcnt; $i += 3)
				$capdate[count($capdate)+1] = add_months($opendate, $i + 3);
		}
		//addlog("\r\n\r\nid=$id; type=$type; newdate=$newdate; " . print_r($capdate, true));
		$i = 1;
		//addlog("update = $update; i = $i");
		while ($capdate[$i] <= $update && $i <= count($capdate))
			$i++;
		//addlog("update = $update; i = $i");
		$lastcapdate = NULL;
		//addlog("next capdate = " . $capdate[$i]);
		//addlog("capitalization??? " . $capdate[$i] . " <= " . $newdate . " && $i <= " . count($capdate) );
		while ($capdate[$i] <= $newdate && $i <= count($capdate)) {
			//addlog("capitalization!!! " . $capdate[$i] . " <= " . $newdate . " && $i <= " . count($capdate) );
			$sum = round_sum(diff_date($update, $capdate[$i]) * $rate / 100 / 365 * check_balance($mainacc, $mysqli));
			//addlog("нач. %%: sum = $sum; period = [" . $update . " - " . $capdate[$i] . "]; mainaccbalance = " . check_balance($mainacc, $mysqli));
			$res = transaction($percacc, $src_bank_accountnum, $sum, $user, $mysqli);
			if ($res != "")
				return "MySQL error: " . $mysqli->error;
			$percsum = check_balance($percacc, $mysqli);
			//addlog("капитализация %%: sum = $percsum;");
			$res = transaction($mainacc, $percacc, $percsum, $user, $mysqli);
			if ($res != "")
				return $res;
			$update = $capdate[$i];
			$lastcapdate = $capdate[$i];
			$i++;                
			//addlog("i = $i");
		}
		
		if ($i <= count($capdate)) {
			$sum = round_sum(diff_date($update, $newdate) * $rate / 100 / 365 * check_balance($mainacc));
			if ($sum != 0.00) {
				$res = transaction($percacc, $src_bank_accountnum, $sum, $user, $mysqli);
				if ($res != "")
					return "Ошибка MySQL " . $mysqli->error;
			}
		}
		$stmt = $mysqli->prepare("UPDATE deposits SET `update` = ?, capdate = IFNULL(?, capdate) WHERE id = ?");
		$stmt->bind_param("ssi", $newdate, $lastcapdate, $id);
		if (!$stmt->execute())
			return "Ошибка MySQL " . $mysqli->error;
		//addlog("\r\n\r\n");
		return "";                            			
	}

	function out_client_deposit_box($idclient) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare(
			"SELECT d.id, d.mainacc, t.descript, c.isocode, DATE_ADD(d.opendate , interval t.monthcnt MONTH) enddate " . 
			"FROM deposits d " .
			"  LEFT JOIN depositeterms t ON t.`type` = d.`type` " .
			"  LEFT JOIN currency c ON c.`code` = t.currency " .
			"WHERE d.idclient = ? AND d.closedate = '0000-00-00'");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$str = "";
		$result = $stmt->get_result();
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["id"] . "\">" . $res["descript"] .
				", сумма " . standart_sum(check_balance($res["mainacc"])) . " " . $res["isocode"] .
				", окончание " . out_date($res["enddate"]) . "</option>\n";
		}
		return $str;
	}

	function client_deposit_count($idclient) { // кол-во действующих вкладов у клиента
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT COUNT(*) FROM deposits d WHERE d.idclient = ? AND d.closedate = '0000-00-00'");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$cnt = $stmt->get_result()->fetch_row()[0];
		return $cnt;
	}
	
?>