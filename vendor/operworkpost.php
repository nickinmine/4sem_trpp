<?php
	session_start();
	require "lib.php";

	$operation = $_POST["op"];
	
	if ($operation == "create") {
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("INSERT INTO account (idclient, accountnum, currency, descript, closed) VALUES (?, ?, ?, ?, ?)");
		
		$idclient = $_POST["idclient"];
		$currency = $_POST["currency"];
		$accountnum = generate_accountnum("40800", $currency);
		$descript = $_POST["descript"];
		$closed = $_POST["closed"];
                $stmt->bind_param("isssi", $idclient, $accountnum, $currency, $descript, $closed);

		echo $stmt->execute();
	}

	if ($operation == "check") {
		$mysqli = get_sql_connection();

		$result = $mysqli->query('SELECT * FROM balance WHERE account = ' . $_POST["accountnum"]);
		$sum = 0.00;
		foreach ($result as $res) {
			$sum = $res["sum"];
		}

		#$result = $mysqli->query('SELECT * FROM operations WHERE db = ' . $_POST["accountnum"]);
		#foreach ($result as $res) {
		#	if ($res) {
		#		$sum -= $res["sum"];
		#	}
		#}
		 
		#$result = $mysqli->query('SELECT * FROM operations WHERE cr = ' . $_POST["accountnum"]);
		#foreach ($result as $res) {
		#	if ($res) {
		#		$sum += $res["sum"];
		#	}
		#}
		echo $sum;
	}

	if ($operation == "deposit") {
		$mysqli = get_sql_connection();

		// если эта операция не работает на счетах с новой валютой, то необходимо добавить в бд кассу с соответствующей валютой
	
		$currency = substr($_POST["accountnum"], 5, 3);
		$cash = $mysqli->query('SELECT * FROM account WHERE accountnum LIKE "20202' . $currency . '%"')->fetch_assoc()["accountnum"];
		$stmt = $mysqli->prepare('INSERT INTO operations(db, cr, operdate, sum) values (?, ?, concat(?, " ", current_time()) , ?)');
		
		$acc = $_POST["accountnum"];
		$date = $mysqli->query('SELECT * FROM operdays WHERE current = 1')->fetch_assoc()["operdate"];
		$sum = $_POST["sum"];		

		$stmt->bind_param("ssss", $cash, $acc, $date, $sum);
                	
		echo $stmt->execute();
	}

	if ($operation == "findphone") {
		$mysqli = get_sql_connection();
       		$result = $mysqli->query("SELECT * FROM clients WHERE phone = \"" . $_POST["phone"] . "\"");
    		foreach ($result as $res) {
			echo $res["name"];
			break;
		}
	}
?>