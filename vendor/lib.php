<?php   
	require_once "lib_account.php";
	require_once "lib_deposit.php";
	
	function safe_session_start() {
		if(!isset($_SESSION))
			session_start(); 
	}

	function get_sql_connection() {
		safe_session_start();
		//if (!array_key_exists("connection", $_SESSION)) {
			$_SESSION["connection"] = new mysqli("localhost", "root", "", "bankbase");
		//}
		return $_SESSION["connection"];
	}

	function addlog($str) {
		$logfile = "..\\!log.txt";
		$fd = fopen($logfile, 'a+');
		if ($fd) {
			fwrite($fd, date("Y-m-d H:i:s") . " " . $str . "\r\n");
			fclose($fd);
		}
	}

	function session_message($key) {
		safe_session_start();
		$str = "";
		if (array_key_exists($key, $_SESSION)) {
	        	$str = $_SESSION[$key];
		        unset($_SESSION[$key]);
		}
		return $str;
	}

	function out_account_box($idclient, $descript = "", $out_null = false) {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT accountnum, isocode, descript FROM account a LEFT JOIN currency c ON c.code = a.currency " . 
			"WHERE closed = '0000-00-00' AND idclient = ? AND accountnum LIKE '40800%'");
		if ($descript == "out_acc") {
			$stmt = $mysqli->prepare("SELECT accountnum, isocode, descript FROM account a LEFT JOIN currency c ON c.code = a.currency " . 
				"WHERE closed = '0000-00-00' AND idclient = ?");
		}
			
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$result = $stmt->get_result();
		$str = "";

		foreach ($result as $res) {
			$stmt = $mysqli->prepare("SELECT type FROM account WHERE accountnum = ?");
			$stmt->bind_param("s", $res["accountnum"]);
			$stmt->execute();
			$sign = ($stmt->get_result()->fetch_row()[0]) == "active" ? 1 : -1;
			$balance = $sign * check_balance($res["accountnum"]);
			if (!$out_null || ($out_null && $balance == 0))
				$str .= '<option value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"] . ': ' 
					. sprintf("%.2f", $balance) . ' ' . $res["isocode"] . ', ' . $res["descript"] . '</option>';	                                       	
		}
		return $str;
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
		$sum2 = str_replace(",", ".", $sum);
		return sprintf("%.2f", $sum2);
	}

	function out_value($data) {
		safe_session_start();
		$id = $_SESSION["client"]["id"];
		$mysqli = get_sql_connection();                 
		$stmt = $mysqli->prepare("SELECT " . $data . " FROM clients WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_row()[0];
		return 'value="' . $result . '"';
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

	function transaction($debit_accountnum, $credit_accountnum, $sum, $user) {
		if (check_balance($debit_accountnum) < $sum) 
			return "Недостаточно средств на счете";
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, (" .
			"SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
		$sum = standart_sum($_POST["sum"]);	
		$stmt->bind_param("ssss", $debit_accountnum, $credit_accountnum, $sum, $user);
		if (!$stmt->execute())
			return $mysqli->error;
		return "";
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