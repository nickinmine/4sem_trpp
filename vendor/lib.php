<?php   
	require_once "lib_account.php";
	require_once "lib_deposit.php";
	require_once "lib_credit.php";
	
	function safe_session_start() {
		if(!isset($_SESSION))
			session_start(); 
	}

	function addlog($str) {
		$tempdir = 'c:\temp'; // если есть такой каталог
		if (!file_exists($tempdir)) // иначе
			$tempdir = sys_get_temp_dir(); // системный - c:\windows\temp
		$logfile = $tempdir .'\!log.txt';
		$fd = fopen($logfile, 'a+');
		if ($fd) {
			date_default_timezone_set("Europe/Moscow");
			fwrite($fd, date("Y-m-d H:i:s") . " " . $str . "\r\n");
			fclose($fd);
		}
	}

	function get_sql_connection() {
		$mysqli = new mysqli("p:localhost", "root", "", "bankbase"); // исп. постоянные соединения
		return $mysqli;
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
		$stmt = $mysqli->prepare(
			"SELECT a.accountnum, c.isocode, a.descript, " .
			"  CASE WHEN t.`type` = 'active' THEN 'А' WHEN t.`type` = 'passive' THEN 'П' ELSE '?' END typemark " .
			"FROM account a " .
			"  LEFT JOIN currency c ON c.code = a.currency " . 
			"  LEFT JOIN accounttype t ON t.acc2p = SUBSTR(a.accountnum, 1, 5) " .
			"WHERE closed = '0000-00-00' AND idclient = ? AND accountnum LIKE '40817%'");
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

	function round_sum($sum) {
		return round($sum, 2);
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

	/*function convert_sum($sum, $in_currency, $out_currency) {
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
		
		return round_sum($out_sum);
	}*/

	function transaction($debit_accountnum, $credit_accountnum, $psum, $user, $pmysqli = NULL) { // перевод между счетами в одной валютой
		$mysqli = $pmysqli ?? get_sql_connection();
		$sum = round_sum($psum);		

		$dbacctype = "";
		$dbacccurr = "";
		$dbbal = check_balance2($debit_accountnum, $dbacccurr, $dbacctype, $mysqli);
		$cracctype = "";
		$cracccurr = "";
		$crbal = check_balance2($credit_accountnum, $cracccurr, $cracctype, $mysqli);

		//addlog("@@@");
		//addlog("dbacctype = $dbacctype; dbbal = $dbbal");
		//addlog("cracctype = $cracctype; crbal = $crbal");
		//addlog("sum = $sum");
		if ($debit_accountnum == $credit_accountnum)
			return "Выберите разные счета!";
		if ($dbacccurr != $cracccurr)
			return "Валюты счетов не совпадают: " . $dbacccurr . " <> " . $cracccurr;
		if (sign_acctype($dbacctype) > 0 && $dbbal < $sum) // дебет активный, остаток маловат
			return "Недостаточно средств на счете " . $debit_accountnum;
		if (sign_acctype($cracctype) < 0 && $crbal < $sum) // кредит пассивный, остаток маловат
			return "Недостаточно средств на счете " . $credit_accountnum;
		if ($sum < 0.00)
			return "Сумма проводки должна быть больше нуля ($debit_accountnum, $credit_accountnum): " . sprintf("%.2f", $sum);

		if ($sum > 0.00) { // проводку с суммой 0.00 просто не сохраняем в БД, ошибку не генерируем
			$stmt = $mysqli->prepare("INSERT INTO operations (db, cr, operdate, sum, employee) VALUES (?, ?, " .
				"(SELECT concat(operdate, ' ', current_time()) FROM operdays WHERE current = 1), ?, ?)");
			$stmt->bind_param("ssds", $debit_accountnum, $credit_accountnum, $sum, $user);
			if (!$stmt->execute())
				return $mysqli->error;
		}
		return "";
	}
	
	function conversion($src_accountnum, $dst_accountnum, $sum, $user, $pmysqli = NULL) { // перевод между счетами с разной валютой
		$mysqli = $pmysqli ?? get_sql_connection();

		$src_currency = get_account_currency($src_accountnum, $mysqli);
		$src_corr_accountnum = "";
		$res = find_bank_account($src_accountnum, "70601".$src_currency."%0001", $src_corr_accountnum, $mysqli);
		if ($res != "")
			return res;
		$dst_currency = get_account_currency($dst_accountnum, $mysqli);
		$dst_corr_accountnum = "";
		$res = find_bank_account($dst_accountnum, "70601".$dst_currency."%0001", $dst_corr_accountnum, $mysqli);
		if ($res != "")
			return res;

		// от клиента - банку
		$src_rur_sum = round_sum($sum);
		if ($src_currency == "810") { // рубли
			$rur_corr_accountnum = "";
			$res = find_bank_account($src_accountnum, "70601810%0001", $rur_corr_accountnum, $mysqli);
			if ($res != "")
				return $res;
			$res = transaction($rur_corr_accountnum, $src_accountnum, $src_rur_sum, $user, $mysqli);
			if ($res != "")
				return $res;
		}
		else { // валюта, нужно обменять по "низкому" курсу
			$stmt = $mysqli->prepare("SELECT buy FROM converter WHERE current = 1 AND currency = ?");
			$stmt->bind_param("s", $src_currency);
			$stmt->execute();
			$row = $stmt->get_result()->fetch_row();
			if (!$row)
				return "Не установлены курсы продажи " . $src_currency;
			$rate = $row[0];
			$src_rur_sum = round_sum($sum * $rate); // рублевый эквивалент по курсу продажи
			//addlog(111);
			$res = transaction($src_corr_accountnum, $src_accountnum, round_sum($sum), $user, $mysqli);
			//addlog(222);
			if ($res != "")
				return $res;
		}

		//addlog("src_rur_sum = $src_rur_sum");

		// от банка - клиенту
		if ($dst_currency == "810") { // рубли
			$rur_corr_accountnum = "";
			$res = find_bank_account($dst_accountnum, "70601810%0001", $rur_corr_accountnum, $mysqli);
			if ($res != "")
				return $res;
			$res = transaction($dst_accountnum, $rur_corr_accountnum, $src_rur_sum, $user, $mysqli);
			if ($res != "")
				return $res;
		}
		else { // валюта, нужно обменять по "высокому" курсу
			$stmt = $mysqli->prepare("SELECT sell FROM converter WHERE current = 1 AND currency = ?");
			$stmt->bind_param("s", $dst_currency);
			$stmt->execute();
			$row = $stmt->get_result()->fetch_row();
			if (!$row)
				return "Не установлены курсы покупки " . $src_currency;
			$rate = $row[0];
			$dst_sum = round_sum($src_rur_sum / $rate);
			//addlog("dst_sum = $dst_sum");
			$res = transaction($dst_accountnum, $dst_corr_accountnum, $dst_sum, $user, $mysqli);
			if ($res != "")
				return $res;
		}

		return "";
	}

	function add_months($date, $cntm) {
		$d = substr($date, 8, 2);  // день
		$m = substr($date, 5, 2);  // месяц
		$y = substr($date, 0, 4);  // год
 
		// Прибавить месяцы
		for ($i = 0; $i < $cntm; $i++) {
			$m++;
			if ($m > 12) { $y++; $m=1; }
		}
 
		// Это последний день месяца?
		if ($d == date('t', $time)) {
			$d=31;
		}
		// Открутить дату до последнего дня месяца
		if (!checkdate($m, $d, $y)) {
			$d = date('t', mktime(0, 0, 0, $m, 1, $y));
		}
		// Вернуть новую дату
		return sprintf("%04d-%02d-%02d", $y, $m, $d);
	}
	
	function out_date($date) {
		$datetime = new DateTime($date);    
		return $datetime->format('d.m.Y');     
	}
	
	function diff_date($date1, $date2) {
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		return ($date2 - $date1) / 60 / 60 / 24;
	}
?>