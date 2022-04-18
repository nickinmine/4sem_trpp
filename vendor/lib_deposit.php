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
		create_account($idclient, $currency, "42301", "Основной счет вклада", $mainacc);                                
		$percacc = "";
		create_account($idclient, $currency, "47411", "Дополнительный счет вклада", $percacc);
		$res = transaction($debit_accountnum, $mainacc, $sum, $user);
		/// !!!
		$stmt = $mysqli->prepare("INSERT INTO deposits (idclient, type, opendate, closedate, sum, mainacc, percacc) " . 
			"VALUES (?, ?, (SELECT operdate FROM operdays WHERE current = 1), '0000-00-00', ?, ?, ?)");
		$stmt->bind_param("issss", $idclient, $type, $sum, $mainacc, $percacc);
		if (!$stmt->execute())
			return $mysqli->error;
		return "";
	}
	
?>