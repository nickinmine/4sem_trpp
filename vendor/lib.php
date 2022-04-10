<?php   
	function get_sql_connection() {
		session_start();
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  // отладочный вывод
		//if (!array_key_exists("connection", $_SESSION)) {
			$_SESSION["connection"] = new mysqli("localhost", "root", "", "bankbase");
		//}
		return $_SESSION["connection"];
	}
	
	function out_account_box($idclient) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare('SELECT accountnum, isocode FROM account a LEFT JOIN currency c ON c.code = a.currency ' . 
			'WHERE closed = "0000-00-00" AND idclient = ?');
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$result = $stmt->get_result();
		$str = "";
		foreach ($result as $res)
			$str .= '<option value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"] . ': ' 
				. sprintf("%.2f", check_balance($res["accountnum"])) . ' ' . $res["isocode"] . '</option>';
		return $str;
	}

	function generate_accountnum($acc2p, $currency) {
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT cnt FROM accountcnt WHERE acc2p = '$acc2p' AND currency = '$currency'");
		$cnt = $result->fetch_row()[0] + 1;

		// Структура банковского счета:
		// 408 - счет физ.лица
		// 00 - род деятельности держателя счета
		// XXX - валюта
		// 1 - проверочный код
		// XXXX - отделение банка (0000 - головной офис)
		// XXXXXX - порядковый номер счета банка
          
		$accountnum = $acc2p . $currency . "10001" . sprintf("%'.07d", $cnt);
		if ($cnt == 1) {
			$stmt = $mysqli->prepare("INSERT INTO accountcnt(acc2p, currency, cnt) VALUES (?, ?, ?)");
			$stmt->bind_param("ssi", $acc2p, $currency, $cnt);
		}
		else {
			$stmt = $mysqli->prepare("UPDATE accountcnt SET cnt = ? WHERE acc2p = ? AND currency = ?");
			$stmt->bind_param("iss", $cnt, $acc2p, $currency);
		}
		$stmt->execute();

		return $accountnum;
	}

	function check_balance($accountnum) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT `sum`, dt FROM balance WHERE account = ? ORDER BY dt DESC LIMIT 1");
		$stmt->bind_param("s", $accountnum);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_row();
		$sum = 0;
		$dt = "0000-00-00";
		if ($res) {
			$sum = $res[0];
			$dt = $res[1];	
		}
		$stmt = $mysqli->prepare("SELECT IFNULL((SELECT -1 * SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND db = ?), 0)" .
			" + IFNULL((SELECT SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND cr = ?), 0)");
		$stmt->bind_param("ssss", $dt, $accountnum, $dt, $accountnum);
		$stmt->execute();                          
		$sum += $stmt->get_result()->fetch_row()[0];
		return $sum;
	}
?>