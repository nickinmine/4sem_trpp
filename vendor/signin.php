<?php
	session_start();
	
	require "lib.php";	

	$login = $_POST['login'];
	$pass = md5($_POST['pass']);
	$connect = get_sql_connection();
	$check = mysqli_query($connect, "SELECT e.*, r.descript FROM employee e LEFT JOIN emproles r ON e.role = r.role " .  
					"WHERE login = '$login' AND password = '$pass'");
	if (mysqli_num_rows($check) > 0) {	
		$user = mysqli_fetch_assoc($check);
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