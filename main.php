<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
</head>

<body>

<div>Создать профиль клиента</div>
<form name="create_client">
	<div><label>ФИО<input type="text" name="name" required="required"></label></div>
	<div><label>Электронная почта<input type="email" name="email" required="required"></label></div>
	<div><label>Дата рождения<input type="date" name="birthdate" required="required"></label></div>
	<div><label>Паспорт<input pattern="[0-9]{4}\s[0-9]{6}"
	<div><input type="submit" name="btn" value="Создать"><div>
</form>

<div>Создать счет клиента</div>
<form name="create_account">
	<div><label>Клиент???<input type="text" name="idclient" required="required"></label></div>
	<div><label>Валюта счета<input pattern="[0-9]{4}\s[0-9]{6}" name="email" required="required"></label></div>
	<div><label>Описание<input type="text" name="descript"></label></div>
	<div><input type="submit" name="btn" value="Открыть счет"><div>
</form>


</body>

</html>