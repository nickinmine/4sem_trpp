<?php
	session_start();
	
	require "lib.php";	

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
		header('Location: ../main.php');
	}
	else {
		header('Location: ../');
		$_SESSION['message'] = 'Неверный пароль!';
	}
?>