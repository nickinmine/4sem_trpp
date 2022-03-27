<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
	<script src="client.js"></script>
</head>

<body>

<?php
	$idclient = $_GET["idcl"];
	
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$mysqli = new mysqli("localhost", "root", "", "bankbase");

	$result = $mysqli->query("select * from clients where passport like \"%" . $passport . "%\"");

	foreach ($result as $res) {
		echo "Клиент с паспортом " . $res["passport"] . " имеет ID " . $res["id"];
	}
?>

<div>Создать счет клиента</div>
<form method="POST" name="create_account">
	<div><label>ID клиента<input type="text" name="idclient" required="required"></label></div>
	<div><label>Валюта счета<input pattern="[0-9]{4}\s[0-9]{6}" name="email" required="required"></label></div>
	<div><label>Описание<input type="text" name="descript"></label></div>
	<div><input type="submit" name="btn" value="Открыть счет"><div>
</form>


</body>

</html>