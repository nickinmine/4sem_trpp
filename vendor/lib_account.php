<?php
	require_once "lib.php";

	function generate_accountnum($acc2p, $currency, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();

		$stmt = $mysqli->prepare("SELECT cnt FROM accountcnt WHERE acc2p = ? AND currency = ?");
		$stmt->bind_param("ss", $acc2p, $currency);
		$stmt->execute();
		$res = $stmt->get_result();
		//addlog(var_dump($res, true));
		$row = $res->fetch_row();
		$cnt = $row ? $row[0] : 0;		
		//addlog("cnt = $cnt");

		//addlog("generate_accountnum( acc2p = " . $acc2p . ", currency = " . $currency . "): cnt = " . $cnt);

		// Структура банковского счета:
		// 408 - счет физ.лица
		// 00 - род деятельности держателя счета
		// XXX - валюта
		// 1 - проверочный код
		// XXXX - отделение банка (0000 - головной офис)
		// XXXXXX - порядковый номер счета банка
          
		$cnt++;
		$accountnum = $acc2p . $currency . "10001" . sprintf("%'.07d", $cnt);
		if ($cnt == 1) {
			//addlog("INSERT INTO accountcnt - start");
			$stmt = $mysqli->prepare("INSERT INTO accountcnt (acc2p, currency, cnt) VALUES (?, ?, ?)");
			$stmt->bind_param("ssi", $acc2p, $currency, $cnt);
			//addlog("INSERT INTO accountcnt - finish");
		}
		else {
			//addlog("UPDATE accountcnt - start");
			$stmt = $mysqli->prepare("UPDATE accountcnt SET cnt = ? WHERE acc2p = ? AND currency = ?");
			$stmt->bind_param("iss", $cnt, $acc2p, $currency);
			//addlog("UPDATE accountcnt - finish");
		}
		//addlog("stmt: " . var_dump($stmt, true));
		$execres = $stmt->execute();
		//addlog("stmt->execute(): " . var_dump($execres, true));
		//addlog("счет $accountnum создан");

		return $accountnum;
	}

	function sign_acctype($acctype) {
		$sign = 0;
		if ($acctype == "active")
			$sign = 1;
		elseif ($acctype == "passive")
			$sign = -1;
		return $sign;
	}

	function check_balance2($accountnum, &$acccurr, &$acctype, $pmysqli = NULL) { // аналог check_balance(), возвращает дополнительно тип счета (А/П)
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare("SELECT `sum`, dt FROM balance WHERE account = ? ORDER BY dt DESC LIMIT 1");
		$stmt->bind_param("s", $accountnum);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_row();
		$sum = 0;
		$dt = "0000-00-00";

		//addlog("check_balance(): accountnum = $accountnum; START");

		$stmt = $mysqli->prepare(
			"SELECT a.currency, t.`type` " .
			"FROM account a " .
			"  LEFT JOIN accounttype t on t.acc2p = substr(a.accountnum, 1, 5) " .
			"WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_row();
		$acccurr = $row[0];
		$acctype = $row[1];
		$sign = sign_acctype($acctype);	

		//addlog("acctype = " . $acctype . "; sign = " . $sign);

		if ($res) {
			$sum = $res[0] * $sign;
			$dt = $res[1];	
			//addlog("Last balance sum = $sum; dt = " . $dt);
		}

		$stmt = $mysqli->prepare("SELECT IFNULL((SELECT -1 * SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND db = ?), 0)" .
			" + IFNULL((SELECT SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND cr = ?), 0)");
		$stmt->bind_param("ssss", $dt, $accountnum, $dt, $accountnum);
		$stmt->execute();
		$opersum = $stmt->get_result()->fetch_row()[0];
		//addlog("opersum = $opersum; sign = $sign");
		$sum += $sign * $opersum;

		//addlog("check_balance(): accountnum = $accountnum; STOP; sum(total) = $sum");
		return round_sum($sum);
	}


	function check_balance($accountnum, $pmysqli = NULL) { // баланс считается на конец дня
		$acctype = "";
		$acccurr = "";
		return check_balance2($accountnum, $acccurr, $acctype, $pmysqli);
	}

	function get_account_currency($accountnum, $pmysqli = NULL) { // получить валюту счета
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute())
			return NULL;
		$row = $stmt->get_result()->fetch_row();
		if (!$row)
			return NULL;
		return $row[0];
	}

	function is_account_ready($accountnum, $pmysqli = NULL) { // счет готов к операциям?
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare("SELECT COUNT(*) FROM account WHERE accountnum = ? AND (closed = '0000-00-00' OR closed IS NULL)");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute())
			return NULL;
		$cnt = $stmt->get_result()->fetch_row()[0];
		return ($cnt == 1);
	}

	function create_account($idclient, $currency, $acc2p, $descript, &$res_account = NULL, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();

		$accountnum = generate_accountnum($acc2p, $currency, $mysqli);
	
		$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
		$stmt->bind_param("is", $idclient, $currency);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		$cntaccount = $stmt->get_result()->fetch_row()[0];

		$default = 1; // счет по умолчанию для приема переводов
		if ($cntaccount > 0)
			$default = 0;

		$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript, `default`) VALUES (?, ?, ?, ?, ?)");
        	$stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript, $default);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;

		$res_account = $accountnum;
		return "";
	}

	function close_account($accountnum, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();
		if (check_balance($accountnum, $mysqli) != 0.00) 
			return "Закрыть можно только пустой счет.";       
			
		$stmt = $mysqli->prepare("SELECT currency, `default`, idclient FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		$data = $stmt->get_result()->fetch_row();
		$currency = $data[0];
		$default = $data[1];	
		$idclient = $data[2];
	
		$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
		$stmt->bind_param("is", $idclient, $currency);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		$cntaccount = $stmt->get_result()->fetch_row()[0];
                 
		if ($default == 1 && $cntaccount > 1) {
			$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND `default` = 0 AND currency = ? LIMIT 1");
			$stmt->bind_param("is", $idlient, $currency);
			$stmt->execute();
			$new_default = $stmt->get_result()->fetch_row()[0];
	
			$stmt = $mysqli->prepare("UPDATE account SET `default` = 1 WHERE accountnum = ?");
			$stmt->bind_param("s", $new_default);
			$stmt->execute();	
		}
		$stmt = $mysqli->prepare("UPDATE account SET `default` = 0 WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		$stmt = $mysqli->prepare("UPDATE account SET closed = (SELECT operdate FROM operdays WHERE current = 1) WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute()) {
			return "MySQL error: " . $mysqli->error;
		}
		return "";
	}

	function find_bank_account($accountnum, $mask, &$corraccountnum, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();
		$stmt = $mysqli->prepare(
			"SELECT accountnum " .
			"FROM account " .
			"WHERE idclient = 1 AND accountnum LIKE ? AND closed = '0000-00-00' " .
			"  AND currency = (SELECT currency FROM account WHERE accountnum = ?)");
		$stmt->bind_param("ss", $mask, $accountnum);
		if (!$stmt->execute())
			return "Не найден счет '$mask' для $accountnum: запрос не выполнен.";
		$row = $stmt->get_result()->fetch_row();
		if (!$row)
			return "Не найден счет '$mask' для $accountnum.";
		$corraccountnum = $row[0];
		return "";
	}

	function out_account_box($idclient, $descript = "", $out_null = false) { // вывод списка счетов 40817 клиента
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare(
			"SELECT a.accountnum, c.isocode, a.descript, " .
			"  CASE WHEN t.`type` = 'active' THEN 'А' WHEN t.`type` = 'passive' THEN 'П' ELSE '?' END typemark " . //, cr.closedate " .
			"FROM account a " .
			"  LEFT JOIN currency c ON c.code = a.currency " . 
			"  LEFT JOIN accounttype t ON t.acc2p = SUBSTR(a.accountnum, 1, 5) " .
			//"  LEFT JOIN credits cr ON cr.curacc = a.accountnum " .
			"WHERE closed = '0000-00-00' AND a.idclient = ? AND accountnum LIKE '40817%'");
			//"  AND (cr.`type` IS NULL OR cr.closedate IS NOT NULL)";
		if ($descript == "out_acc") {
			$stmt = $mysqli->prepare(
				"SELECT accountnum, isocode, descript, " .
				"  CASE WHEN t.`type` = 'active' THEN 'А' WHEN t.`type` = 'passive' THEN 'П' ELSE '?' END typemark " .
				"FROM account a " .
				"  LEFT JOIN currency c ON c.code = a.currency " . 
				"  LEFT JOIN accounttype t ON t.acc2p = SUBSTR(a.accountnum, 1, 5) " .
				"WHERE closed = '0000-00-00' AND idclient = ?");
		}
			
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$result = $stmt->get_result();
		$str = "";

		foreach ($result as $res) {
			/*$stmt = $mysqli->prepare("SELECT type FROM account WHERE accountnum = ?");
			$stmt->bind_param("s", $res["accountnum"]);
			$stmt->execute();
			$sign = ($stmt->get_result()->fetch_row()[0]) == "active" ? 1 : -1;*/
			$balance = /*$sign * */check_balance($res["accountnum"]);
			if (!$out_null || ($out_null && $balance == 0))
				$str .= '<option value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"] . ': ' 
					. sprintf("%.2f", $balance) . ' ' . $res["isocode"] . ', ' . $res["descript"] . ' (' . $res["typemark"] . ')' . '</option>';	                                       	
		}
		return $str;
	}

	function client_account_count_any($idclient) { // кол-во действующих счетов 40817 (c любым остатком) у клиента
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT COUNT(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND accountnum LIKE '40817%'");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		return $stmt->get_result()->fetch_row()[0];
	}

	function client_account_count($idclient, $zeroonly) { // кол-во действующих счетов 40817 [с нулевым остатком] у клиента
		if (!$zeroonly) // любой остаток
			return client_account_count_any($idclient);

		// только без остатка
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT accountnum FROM account WHERE idclient = ? AND closed = '0000-00-00' AND accountnum LIKE '40817%'");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$res = $stmt->get_result();
		$accounts = array();
		foreach ($res as $row)
			$accounts[] = $row["accountnum"];
		$cnt = 0;
		foreach ($accounts as $acc) {
			$balance = check_balance($acc, $mysqli);
			$cnt += ($balance == 0.00 ? 1 : 0);
		}
		return $cnt;
	}

?>