<?php
	require_once "lib.php";

	function generate_accountnum($acc2p, $currency) {             
		$mysqli = get_sql_connection();

		$stmt = $mysqli->prepare("SELECT cnt FROM accountcnt WHERE acc2p = ? AND currency = ?");
		$stmt->bind_param("ss", $acc2p, $currency);
		$stmt->execute();
		$cnt = $stmt->get_result()->fetch_row()[0];		

		echo $cnt;

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
			$stmt = $mysqli->prepare("INSERT INTO accountcnt (acc2p, currency, cnt) VALUES (?, ?, ?)");
			$stmt->bind_param("ssi", $acc2p, $currency, $cnt);
		}
		else {
			$stmt = $mysqli->prepare("UPDATE accountcnt SET cnt = ? WHERE acc2p = ? AND currency = ?");
			$stmt->bind_param("iss", $cnt, $acc2p, $currency);
		}
		$stmt->execute();

		return $accountnum;
	}

	function check_balance($accountnum) { // баланс считается на конец дня
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT `sum`, dt FROM balance WHERE account = ? ORDER BY dt DESC LIMIT 1");
		$stmt->bind_param("s", $accountnum);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_row();
		$sum = 0;
		$dt = "0000-00-00";

		$stmt = $mysqli->prepare("SELECT type FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		$stmt->execute();
		$sign = ($stmt->get_result()->fetch_row()[0] == "active" ? 1 : -1);	

		if ($res) {
			$sum = $res[0];
			$dt = $res[1];	
		}

		$stmt = $mysqli->prepare("SELECT IFNULL((SELECT -1 * SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND db = ?), 0)" .
			" + IFNULL((SELECT SUM(`sum`) FROM operations WHERE operdate > concat(?, ' 23:59:59') AND cr = ?), 0)");
		$stmt->bind_param("ssss", $dt, $accountnum, $dt, $accountnum);
		$stmt->execute();                          
		$sum += $sign * $stmt->get_result()->fetch_row()[0];	

		return $sum;   
	}

	function create_account($idclient, $currency, $acc2p, $descript, &$res_account = NULL) {
		$accountnum = generate_accountnum($acc2p, $currency);
		
		$mysqli = get_sql_connection();
	
		$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
		$stmt->bind_param("is", $idclient, $currency);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$cntaccount = $stmt->get_result()->fetch_row()[0];

		$default = 1; // счет по умолчанию для приема переводов
		if ($cntaccount > 0)
			$default = 0;

		$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript, `default`) VALUES (?, ?, ?, ?, ?)");
	        
        	$stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript, $default);
		
		if (!$stmt->execute()) {
			return $mysqli->error;
		}

		$res_account = $accountnum;
		return "";
	}

	function close_account($accountnum) {
		if (check_balance($accountnum) != 0) 
			return "Закрыть можно только пустой счет.";       
			
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT currency, `default`, idclient FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		$data = $stmt->get_result()->fetch_row();
		$currency = $data[0];
		$default = $data[1];	
		$idclient = $data[2];
	
		$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
		$stmt->bind_param("is", $idclient, $currency);
		if (!$stmt->execute()) {
			return $mysqli->error;
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
			return $mysqli->error;
		}
		$stmt = $mysqli->prepare("UPDATE account SET closed = (SELECT operdate FROM operdays WHERE current = 1) WHERE accountnum = ?");
		$stmt->bind_param("s", $accountnum);
		if (!$stmt->execute()) {
			return $mysqli->error;
		}
		return "";
	}
?>