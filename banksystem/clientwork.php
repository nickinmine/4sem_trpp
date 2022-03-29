<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Главная</title>
	<script src="account.js"></script>
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
                           
       	$result = $mysqli->query("SELECT * FROM clients WHERE id = " . $_GET["idclient"]);
    	foreach ($result as $res) {
		echo "<div>Работа с клиентом: " . $res["name"] . ", паспорт " . $passport = $res["passport"];
	}
?>

<div>Создать новый счет</div>
<form id="create_account">
	<div>Валюта нового счета
		<?php   
		$mysqli = get_sql_connection();
       		$result = $mysqli->query("SELECT * FROM currency");
		$cnt = 0;
    		foreach ($result as $res) {
			echo '<div><input type="radio" id="create_account::radio_' . $cnt . '" name="currency" value="' . $res["code"] . 
				'"' . ($cnt == 0 ? 'checked=1' : '') . '><label for="create_account::radio_' . $cnt++ . '">' . $res["isocode"] . 
				" (" . $res["name"] . ')</label></div>';
		} 
		?>
	</div>
	<div><input type="button" value="Создать" onclick="createAccount('Счет физ. лица')"></div>
</form>

<div>Проверить баланс счета</div>
<form id = "check_balance">
	<div>Выберите счет<select form="check_balance" required>
		<option selected id="check_balance::accountnum_0"></option>
		<?php           
		$mysqli = get_sql_connection();
		$result = $mysqli->query('SELECT * FROM account WHERE idclient = "' . $_GET["idclient"] . '"');
		$cnt = 1;
		foreach ($result as $res) {
			if (!$res["closed"]) {
				echo '<option id="check_balance::accountnum_' . $cnt . 
					'" value="' . $res["accountnum"] . '">Счет №' . $res["accountnum"];
				$currency = $mysqli->query('SELECT * FROM currency WHERE code = ' . $res["currency"]);
				foreach ($currency as $cur) {
					echo ': валюта ' . $cur["name"] . '</option>';	
				}
				$cnt++;
			}			
		}                
		?>
	</select></div>
	<div><input type="button" value="Проверить" onclick="checkBalance()"></div>
</form>

<div>Пополнить счет</div>
<form id = "deposit_account">
	<div>Выберите счет<select form="deposit_account" required>
		<option id = "deposit_account::accountnum_0" selected></option>
		<?php           
		$mysqli = get_sql_connection();
		$result = $mysqli->query('SELECT * FROM account WHERE idclient = "' . $_GET["idclient"] . '"');
		$cnt = 1;
		foreach ($result as $res) {
			if (!$res["closed"]) {
				echo '<option id="deposit_account::accountnum_' . $cnt++ . 
					'" value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"];
				$currency = $mysqli->query('SELECT * FROM currency WHERE code = ' . $res["currency"]);
				foreach ($currency as $cur) {
					echo ': валюта ' . $cur["name"];	
				}
			}			
		}                
		?>
	</select></div>
	<div><label>Сумма пополнения<input type="number" required="required" id="deposit_account::sum"></label></div>
	<div><input type="button" value="Пополнить" onclick="depositAccount()"></div>
</form>

<div>Перевод средств со счета на счет (в пределах одной валюты)</div>
<form id = "transaction">
	<div>Счет перевода<select form="transaction" required>
		<option id = "transaction::credit_accountnum_0" selected></option>
		<?php           
		$mysqli = get_sql_connection();
		$result = $mysqli->query('SELECT * FROM account WHERE idclient = "' . $_GET["idclient"] . '"');
		$cnt = 1;
		foreach ($result as $res) {
			if (!$res["closed"]) {
				echo '<option id=transaction::credit_accountnum_"' . $cnt++ . 
					'" value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"];
				$currency = $mysqli->query('SELECT * FROM currency WHERE code = ' . $res["currency"]);
				foreach ($currency as $cur) {
					echo ': валюта ' . $cur["name"];	
				}
			}			
		}                
		?>
	</select></div>
	<div>
		<label>Телефон<input type="text" required="required" id="transaction::phone"></label>
		<input type="button" value="Поиск" onclick="findClientIdByPhone()">
		<span id="transaction::credit_client_info" value="12345"></span>
		
	</div>
	<div><label>Сумма пополнения<input type="number" required="required" id="deposit_account::sum"></label></div>
	<div><input type="button" value="Перевести" onclick="transaction()"></div>
</form>	

</body>

</html>