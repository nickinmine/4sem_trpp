<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
	<script src="client.js"></script>
</head>

<body>

<?php   
	require "lib.php";
	                           
	$mysqli = get_sql_connection();
       	$result = $mysqli->query("SELECT * FROM employee WHERE login = \"" . $_GET["login"] . "\"");
    	foreach ($result as $res) {
		echo "<div>Авторизован пользователь " . $res["name"];
		$role = $res["role"];
	}
	$result = $mysqli->query("SELECT * FROM emproles WHERE role = \"" . $role . "\"");
	foreach ($result as $res) {
		echo ", " . $res["descript"];
	}
?>

<div>Создать профиль клиента</div>
<form id="create_client">
	<div><label>ФИО<input type="text" required="required" id="create_client::name"></label></div>
	<div><label>Электронная почта<input type="email" required="required" id="create_client::email"></label></div>
	<div><label>Дата рождения<input type="date" required="required" id="create_client::birthdate"></label></div>
	<div><label>Паспорт<input pattern="^[0-9]{4} [0-9]{6}$" required="required" id="create_client::passport"></label></div>
        <div><label>Адрес<input type="text" required="required" id="create_client::address"></label></div>
	<div><input type="button" value="Создать" onclick="createClient()"></div>
</form>

<div>Работа с клиентом</div>
<form id="find_client_id">
	<div><label>Паспорт<input pattern="^[0-9]{4} [0-9]{6}$" required="required" id="find_client_id::passport"></label></div>
	<div><input type="button" value="Начать" onclick="findClientId()"><div>
</form>

</body>

</html>