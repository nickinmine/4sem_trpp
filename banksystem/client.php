<?php
	require "lib.php";	

	$operation = $_POST["op"];
	
	if ($operation == "create") {
		$mysqli = get_sql_connection();

		$stmt = $mysqli->prepare("INSERT INTO clients(name, email, birthdate, passport, address) values (?, ?, ?, ?, ?)");
		
		$name = $_POST["name"];
		$mail = $_POST["email"];
		$date = $_POST["birthdate"];
		$pass = $_POST["passport"];
		$addr = $_POST["address"];
                $stmt->bind_param("sssss", $name, $mail, $date, $pass, $addr);
		
		echo $stmt->execute();
	}

	if ($operation == "find") {
		$mysqli = get_sql_connection();
       		$result = $mysqli->query("SELECT * FROM clients WHERE passport = \"" . $_POST["passport"] . "\"");
    		foreach ($result as $res) {
			echo $res["id"];
			break;
		}
	}
?>