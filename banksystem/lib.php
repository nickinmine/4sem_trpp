<?php
	function get_sql_connection() { 
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$mysqli = new mysqli("localhost", "root", "", "bankbase");
		return $mysqli;
	}
	
	function generate_accountnum($acc2p, $currency) {
		$mysqli = get_sql_connection();
                $stmt = $mysqli->prepare("SELECT cnt FROM accountcnt WHERE acc2p = ? AND currency = ?");
		$stmt->bind_param("ss", $acc2p, $currency);
		$stmt->execute();
                $result = $stmt->get_result();
		$cnt = 0;
		foreach ($result as $res) {
			$cnt = $res["cnt"];
		}
		
		// Структура банковского счета:
		// 408 - счет физ.лица
		// 00 - род деятельности держателя счета
		// XXX - валюта
		// 1 - проверочный код
		// XXXX - отделение банка (0000 - головной офис)
		// XXXXXX - порядковый номер счета банка

		$cnt += 1;
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
?>