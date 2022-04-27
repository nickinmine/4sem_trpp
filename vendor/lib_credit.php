<?php
	require_once "lib.php";
	
	function out_credit_box() {
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT descript, monthcnt,`type`, rate FROM creditterms ORDER BY monthcnt, rate");
		$str = "";
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["type"] . "\">" . $res["descript"] . " (". $res["monthcnt"] . " мес., ". $res["rate"] . "% годовых)" . "</option>\n";
		}
		return $str;
	}

	function create_credit($type, $sum, $user, $idclient, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();

		$stmt = $mysqli->prepare("SELECT * FROM creditterms WHERE `type` = ?");
		$stmt->bind_param("s", $type);
		if (!$stmt->execute())
			return $mysqli->error;
		$terms = $stmt->get_result()->fetch_row();

		// создаем счета для КД
		$curacc = ""; // текущий счет
		$odacc = ""; // осн. долг
		$pcacc = ""; // осн. проценты
		$prodacc = ""; // проср. долг
		$prpcacc = ""; // проср. проценты
		$res = create_account($idclient, "810", "40817", "текущий счет", 'active', $curacc, $mysqli);
		if ($res != "")
			return "Не создан текущий счет. " . $res;
		$res = create_account($idclient, "810", "47427", "учет основного долга по КД", 'passive', $odacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета основного долга по КД.";
		$res = create_account($idclient, "810", "45510", "учет процентов по КД", 'passive', $pcacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";
		$res = create_account($idclient, "810", "45815", "учет просроченного долга по КД", 'passive', $prodacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";
		$res = create_account($idclient, "810", "45915", "учет просроченных процентов по КД", 'passive', $prpcacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";

		// создаем запись в таблице кредитов (сам кредит)
		$stmt = $mysqli->prepare("INSERT INTO credits (idclient, type, opendate, closedate, curacc, odacc, pcacc, prodacc, prpcacc, `update`) " .
			"VALUES (?, ?, (SELECT operdate FROM operdays WHERE current = 1), NULL, ?, ?, ?, ?, ?, NULL)");
		$stmt->bind_param("issssss", $idclient, $type, $curacc, $odacc, $pcacc, $prodacc, $prpcacc);	
		if (!$stmt->execute())
			return $mysqli->error;

		// строим график погашения
		// !!!

		// создаем проводку по выдаче кредита
		$res = transaction($odacc, $curacc, $sum, $user, $mysqli);
		if ($res != "")
			return $res;

		return "дошли до этого момента";		
		

		/*$stmt = $mysqli->prepare("SELECT idclient, currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $debit_accountnum);
		if (!$stmt->execute())
			return $mysqli->error;
		$data = $stmt->get_result()->fetch_row();
		$idclient = $data[0];
		$currency = $data[1];
		$mainacc = "";
		//addlog($idclient);
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
		//addlog($idclient . " " . $type . " " . $mainacc . " " . $percacc);
		$stmt = $mysqli->prepare("INSERT INTO deposits (idclient, `type`, opendate, closedate, mainacc, percacc, `update`, capdate) VALUES (?, ?, " . 
			"(SELECT operdate FROM operdays WHERE current = 1), '0000-00-00', ?, ?, (SELECT operdate FROM operdays WHERE current = 1), " . 
			"(SELECT operdate FROM operdays WHERE current = 1))");
		$stmt->bind_param("isss", $idclient, $type, $mainacc, $percacc);
		if (!$stmt->execute()) {       
			return $mysqli->error;
		}
		*/
		return "";
	}

	/*function update_deposit($id, $newdate, $user) { // вклады только в рублях
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
		$capdate = []; // даты капитализации
		if ($modify == "") {
			$capdate[1] = add_months($opendate, $monthcnt);
		}
		if ($modify == "+1 month") {
			for ($i = 1; $i <= $monthcnt; $i++) {
				$capdate[$i] = add_months($opendate, $i);    
			}
		}
		if ($modify == "+3 month") {
			for ($i = 1; $i <= $monthcnt; $i += 3) {
				$capdate[$i] = add_months($opendate, $i);
			}
		}		
		$i = 1;
		while ($capdate[$i] <= $update && $i <= count($capdate))
			$i++;
		//addlog("next capdate = " . $capdate[$i]);
		while ($capdate[$i] <= $newdate && $i <= count($capdate)) {
			$sum = standart_sum(diff_date($update, $capdate[$i]) * $rate / 100 / 365 * check_balance($mainacc));
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
		$stmt = $mysqli->prepare("UPDATE deposits SET `update` = ?, capdate = ? WHERE id = ?");
		$stmt->bind_param("ssi", $newdate, $capdate[$i], $id);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		return "";                            			
	}
	
	function out_client_deposit_box($idclient) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT d.id, d.mainacc, t.descript, DATE_ADD(d.opendate , interval t.monthcnt MONTH) enddate " . 
			"FROM deposits d LEFT JOIN depositeterms t ON t.`type` = d.`type` WHERE d.idclient = ? AND d.closedate = '0000-00-00'");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$str = "";
		$result = $stmt->get_result();
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["id"] . "\">" . $res["descript"] . ", сумма " . standart_sum(check_balance($res["mainacc"])) .
				", окончание " . out_date($res["enddate"]) . "</option>\n";
		}
		return $str;
	}*/
	
?>