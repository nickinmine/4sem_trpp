<?php
    session_start();
	require "lib.php";
	#$operation = $_POST["op"];
    $name = $_POST["name"];
    $passport = $_POST["idclient"];
    $passgiven = $_POST["passgiven"];
    $passcode = $_POST["passcode"];
    $passdate = $_POST["passdate"];
    $sex = $_POST["sex"];
    $birthdate = $_POST["birthdate"];
    $birthplace = $_POST["birthplace"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    echo $name;
    echo $passport;
    echo $passgiven;
    echo $passcode;
    echo $passdate;
    echo $sex;
    echo $birthdate;
    echo $birthplace;
    echo $address;
    echo $phone;
    echo $email;
	#if ($operation == "create") {
    $mysqli = get_sql_connection();
    #INSERT INTO clients(name, email, birthdate, passport, address, phone, passgiven, passcode, passdate, sex, birthplace) values ("eth", "erh", "20.02.2022", "fgj", "sw", "r", "dgh", "dh", "21.02.2022", "h", "dh");
    mysqli_query($mysqli,"INSERT INTO clients (name, 
                                                    email, 
                                                    birthdate, 
                                                    passport, 
                                                    address, 
                                                    phone, 
                                                    passgiven, 
                                                    passcode, 
                                                    passdate, 
                                                    sex, 
                                                    birthplace)
                                            VALUES ('$name',
                                                    '$email', 
                                                    '$birthdate',
                                                    '$passport', 
                                                    '$address', 
                                                    '$phone', 
                                                    '$passgiven', 
                                                    '$passcode', 
                                                    '$passdate', 
                                                    '$sex',
                                                    '$birthplace')");
    #$stmt->bind_param("sssssssssss", $name, $email, $birthdate, $passport, $address, $phone, $passgiven, $passcode, $passdate, $sex, $birthplace);
    #echo $stmt->execute();
    #header('Location: ../clientwork.php');
	#}
	/*if ($operation == "find") {
		$mysqli = get_sql_connection();
       		$result = $mysqli->query("SELECT * FROM clients WHERE passport = \"" . $_POST["passport"] . "\"");
    		foreach ($result as $res) {
			echo $res["id"];
			break;
		}
	}*/
?>