<?php
	require "lib.php";	

	$operation = $_POST["op"];
	
	if ($operation == "create") {
		$mysqli = get_connection();

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
		$passport = $_POST["passport"];
		
		if ($passport != "") {
			$mysqli = get_connection();
        		$result = $mysqli->query("select * from clients where passport like \"%" . $passport . "%\"");
        		foreach ($result as $res) {
				echo "Клиент с паспортом " . $res["passport"] . " имеет ID " . $res["id"];
			}
		}
	}
?>