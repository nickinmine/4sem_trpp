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
		addlog($idclient . " " . $type . " " . $mainacc . " " . $percacc);
		$stmt = $mysqli->prepare("INSERT INTO deposits (idclient, `type`, opendate, closedate, mainacc, percacc, `update`) VALUES (?, ?, " . 
			"(SELECT operdate FROM operdays WHERE current = 1), '0000-00-00', ?, ?, (SELECT operdate FROM operdays WHERE current = 1))");
		$stmt->bind_param("isss", $idclient, $type, $mainacc, $percacc);
		if (!$stmt->execute())
			return $mysqli->error;
		return "";
	}

	function update_deposit($id, ) {
		
	}
	
?>