<?php   
	function get_sql_connection() {
		session_start();
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  // отладочный вывод
		//if (!array_key_exists("connection", $_SESSION)) {
		$_SESSION["connection"] = new mysqli("localhost", "root", "", "bankbase");
		//}
		return $_SESSION["connection"];
	}
	
	/*function out_header_menu($role) {
		if ($role == "admin" || $role == "operator")
			echo '<div class="subbutton" onclick="document.location.href="oper.php"">Оператор</div>';
		if ($role == "admin" || $role == "accountant")
			echo '<div class="subbutton" onclick="document.location.href="acc.php"">Бухгалтер</div>';
	}*/

	function addlog($str) {
		$logfile = "..\\!log.txt";
		$fd = fopen($logfile, 'a+');
		if ($fd) {
			fwrite($fd, date("Y-m-d H:i:s") . " " . $str . "\r\n");
			fclose($fd);
		}
	}

	function out_account_box($idclient) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare('SELECT accountnum, isocode, descript FROM account a LEFT JOIN currency c ON c.code = a.currency ' . 
			'WHERE closed = "0000-00-00" AND idclient = ?');
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$result = $stmt->get_result();
		$str = "";

		foreach ($result as $res) {
			$stmt = $mysqli->prepare("SELECT type FROM account WHERE accountnum = ?");
			$stmt->bind_param("s", $res["accountnum"]);
			$stmt->execute();
			$type = $stmt->get_result()->fetch_row()[0];
			$sign = ($type == "active" ? 1 : -1);	
			$str .= '<option value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"] . ': ' 
				. sprintf("%.2f", $sign * check_balance($res["accountnum"])) . ' ' . $res["isocode"] . ', ' . $res["descript"] . '</option>';
		}
		return $str;
	}

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

	function standart_phone($phone) {
		$newphone = "";
		$flag = 0;
		for ($i = 0; $i < strlen($phone); $i++) {
			$num = $phone[$i];
			if ($num == '+') {
				$flag = 1;
				$newphone = "8";
				continue;
			}
			if ($flag == 1) {
				$flag = 0;
				continue;
			}
			if ($num >= "0" && $num <= "9") {
				$newphone = $newphone . $num;
				continue;
			}
		}
		return $newphone;
	}
	
	function standart_sum($sum) {
		return sprintf("%.2f", $sum);
	}

	function out_value($data) {
		session_start();
		$id = $_SESSION["client"]["id"];
		$mysqli = get_sql_connection();                 
		$stmt = $mysqli->prepare("SELECT " . $data . " FROM clients WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_row()[0];
		return 'value="' . $result . '"';
	}

	function create_account($idclient, $currency, $acc2p, $descript) {
		$accountnum = generate_accountnum($acc2p, $currency);
		
		$mysqli = get_sql_connection();
	
		$stmt = $mysqli->prepare("SELECT count(*) FROM account WHERE idclient = ? AND closed = '0000-00-00' AND currency = ?");
		$stmt->bind_param("is", $idclient, $currency);
		$stmt->execute();
		$cntaccount = $stmt->get_result()->fetch_row()[0];

		$default = 1; // счет по умолчанию для приема переводов
		if ($cntaccount > 0)
			$default = 0;

		$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript, `default`) VALUES (?, ?, ?, ?, ?)");
	        
        	$stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript, $default);
		
		if (!$stmt->execute()) {
			return $mysqli->error;
		}

		return "";
	}

	function convert_sum($sum, $in_currency, $out_currency) {
		$mysqli = get_sql_connection();

		$stmt = $mysqli->prepare("SELECT buy FROM converter WHERE current = 1 AND currency = ?");
		$stmt->bind_param("s", $in_currency);
		$stmt->execute();
		$sell_sum = $stmt->get_result()->fetch_row()[0];
		$in_sum = $sum * $sell_sum;
		
		$stmt = $mysqli->prepare("SELECT sell FROM converter WHERE current = 1 AND currency = ?");
		$stmt->bind_param("s", $out_currency);
		$stmt->execute();
		$buy_sum = $stmt->get_result()->fetch_row()[0];
		$out_sum = $in_sum / $buy_sum;
		
		return standart_sum($out_sum);
	}

	function transaction($debit_accountnum, $credit_accountnum, $sum) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
			"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
		$sum = standart_sum($_POST["sum"]);	
		$stmt->bind_param("ssss", $debit_accountnum, $credit_accountnum, $sum, $_SESSION["user"]["login"]);
		$stmt->execute();
	}
	
	function conversion($debit_accountnum, $credit_accountnum, $sum, $user) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT currency FROM account WHERE accountnum = ?");
		$stmt->bind_param("s", $debit_accountnum);
		$stmt->execute();
		$debit_currency = $stmt->get_result()->fetch_row()[0];
	        $stmt->bind_param("s", $credit_accountnum);
		$stmt->execute();
		$credit_currency = $stmt->get_result()->fetch_row()[0];

		$stmt->prepare("SELECT accountnum FROM account WHERE accountnum LIKE '30303%' AND currency = ?");
		$stmt->bind_param("s", $debit_currency);
		$stmt->execute();
		$convert_debit_accountnum = $stmt->get_result()->fetch_row()[0];
		$stmt->bind_param("s", $credit_currency);
		$stmt->execute();
		$convert_credit_accountnum = $stmt->get_result()->fetch_row()[0];
		$rub_currency = "810";
		$stmt->bind_param("s", $rub_currency);
		$stmt->execute();
		$convert_rub_accountnum = $stmt->get_result()->fetch_row()[0]; 

		$mysqli->query("BEGIN");
	
	 	$sum = standart_sum($sum);	
		
		$stmt = $mysqli->prepare("SELECT cost, buy FROM converter WHERE current = 1 AND currency = ?");
		$stmt->bind_param("s", $debit_currency);
		$stmt->execute();	
		$data = $stmt->get_result()->fetch_row();
		$debit_sum = $sum * $data[0] - $sum * $data[1];
		$stmt = $mysqli->prepare("SELECT cost, sell FROM converter WHERE current = 1 AND currency = ?");
		$stmt->bind_param("s", $credit_currency);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_row();
		$credit_sum = $sum * $data[1] - $sum * $data[0];
		$bank_income_accountnum = "70601810500000000001";
		$bank_income_sum = $debit_sum + $credit_sum;	
                
		$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
			"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
		$temp_sum = $sum + $debit_sum;
		$stmt->bind_param("ssss", $debit_accountnum, $convert_debit_accountnum, $sum, $user);
		$stmt->execute();

		$conv_sum = convert_sum($sum, $debit_currency, $credit_currency);
		$stmt->bind_param("ssss", $convert_credit_accountnum, $credit_accountnum, $conv_sum, $user);
		$stmt->execute();

		$stmt->bind_param("ssss", $convert_rub_accountnum, $bank_income_accountnum, $bank_income_sum, $user);
		$stmt->execute();
	
                $mysqli->query("COMMIT");
		
		return;
	}
?>