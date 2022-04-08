<?php
	session_start();
	
	require "lib.php";	

	$login = $_POST['login'];
	$pass = md5($_POST['pass']);
	$connect = get_sql_connection();
	if (!$connect) {
		die('Error!');
	}
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
		//print_r($user);
		#rolename
		#$role = $_SESSION['user']['role'];
		#$temp = mysqli_query($connect, "SELECT * FROM `emproles` WHERE `role` = 'admin'");
		#$temp1 = mysqli_fetch_assoc($temp);
		#$_SESSION['user'] = [
  	  	#"descript" => $temp['role']
		#];
		header('Location: ../main.php');
	}
	else {
		header('Location: ../');
		$_SESSION['message'] = 'Неверный пароль!';
	}
?>