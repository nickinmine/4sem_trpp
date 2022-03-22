<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
</head>

<body>

<div>Создать профиль клиента</div>
<form method="POST" name="create_client">
	<div><label>ФИО<input type="text" name="name" required="required"></label></div>
	<div><label>Электронная почта<input type="email" name="email" required="required"></label></div>
	<div><label>Дата рождения<input type="date" name="birthdate" required="required"></label></div>
	<div><label>Паспорт<input pattern="[0-9]{4}\s[0-9]{6}" name="passport" required="required"></label></div> 
	<div><input type="submit" name="btn" value="Создать"><div>

</form>

<?php
	$name = $_POST["name"];
	$email = $_POST["email"];
	$date =	$_POST["bithdate"];
	$passport = $_POST["passport"];

	$db_host = "localhost"; 
	$db_user = "root"; 
	$db_password = "";
	$db_base = "bankbase";
	$db_table = "clients";

	$db = new PDO("mysql:host=$db_host;dbname=$db_base", $db_user, $db_password);

	$data = array("name" => $name, "email" => $email, "birthdate" => $birthdate, "passport" => $passport);

	$query->execute($data);
?>

<div>Создать счет клиента</div>
<form method="POST" name="create_account">
	<div><label>Клиент???<input type="text" name="idclient" required="required"></label></div>
	<div><label>Валюта счета<input pattern="[0-9]{4}\s[0-9]{6}" name="email" required="required"></label></div>
	<div><label>Описание<input type="text" name="descript"></label></div>
	<div><input type="submit" name="btn" value="Открыть счет"><div>
</form>


</body>

</html>