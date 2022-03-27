<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
	<script src="client.js"></script>
</head>

<body>

<div>Создать профиль клиента</div>
<form name="create_client">
	<div><label>ФИО<input type="text" name="name" required="required" id="create_client::name"></label></div>
	<div><label>Электронная почта<input type="email" name="email" required="required" id="create_client::email"></label></div>
	<div><label>Дата рождения<input type="date" name="birthdate" required="required" id="create_client::birthdate"></label></div>
	<div><label>Паспорт<input pattern="^[0-9]{4} [0-9]{6}$" name="passport" required="required" id="create_client::passport"></label></div>
        <div><label>Адрес<input type="text" name="address" required="required" id="create_client::address"></label></div>
	<div><input type="button" name="btn" value="Создать" onclick="createClient()"></div>
</form>

<div>Работа с клиентом</div>
<form name="find_client_id" id="find_client_id">
	<div><label>Паспорт<input pattern="^[0-9]{4} [0-9]{6}$" name="passport" required="required" id="find_client_id::passport"></label></div>
	<div><input type="button" name="btn" value="Начать" onclick="findClientId()"><div>
</form>

<div>Создать счет клиента</div>
<form method="POST" name="create_account">
	<div><label>ID клиента<input type="text" name="idclient" required="required"></label></div>
	<div><label>Валюта счета<input pattern="[0-9]{4}\s[0-9]{6}" name="email" required="required"></label></div>
	<div><label>Описание<input type="text" name="descript"></label></div>
	<div><input type="submit" name="btn" value="Открыть счет"><div>
</form>


</body>

</html>