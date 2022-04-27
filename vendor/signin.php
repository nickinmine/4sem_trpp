<?php
	require "lib.php";
	safe_session_start();	                   	

	$login = $_POST['login'];
	$pass = md5($_POST['pass']);
	$mysqli = get_sql_connection();
	$stmt = $mysqli->prepare("SELECT e.*, r.descript FROM employee e LEFT JOIN emproles r ON e.role = r.role " .  
					"WHERE login = ? AND password = ?");
	$stmt->bind_param("ss", $login, $pass);
	$stmt->execute();
	$check = $stmt->get_result();
	if (mysqli_num_rows($check) > 0) {	
		$user = $check->fetch_assoc();
		$_SESSION['user'] = [
			"login" => $user['login'],
			"name" => $user['name'],
			"role" => $user['role'],
			"descript" => $user['descript']
		];
		if ($_SESSION["user"]["role"] == "admin" || $_SESSION["user"]["role"] == "operator") {
			header('Location: ../oper.php');	
		}
		else if ($_SESSION["user"]["role"] == "accountant") {
			header('Location: ../acc.php');	
		}
	}
	else {
		header('Location: ../');
		$_SESSION['message-auth'] = 'Неверный пароль!';
	}
?>