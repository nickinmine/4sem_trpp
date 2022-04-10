<?php
	session_start();
	
	require "vendor/lib.php";
	
	if (!$_SESSION['user']) {
		header('Location: /');
	}
	if ($_SESSION['user']['role'] != 'admin' & $_SESSION['user']['role'] != 'operator') {
		header('Location: /main.php');
		$_SESSION['message'] = 'Отказано в доступе: несоответствие уровня доступа.';
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="css/base.css">
	<link rel="stylesheet" href="css/navbar.css">
	<title>Работа с клиентом</title>
	<script src="js/script.js"></script>
</head>
<body>
<header class="header">
	<div class="header-container">
		<div class="header-menu">
			<div class="subbutton" onclick="document.location.href='main.php'">Главная</div>
			<div class="subbutton" onclick="document.location.href='oper.php'">Оператор</div>
			<div class="subbutton" onclick="document.location.href='acc.php'">Бухгалтер</div>
		</div>
		<div class="role-name">
			<span>Сотрудник: <?php
				print_r($_SESSION['user']['name'] . ', ' . $_SESSION['user']['descript']);
			?></span>
		</div>
		<div class="exit-button subbutton">
			<div onclick="document.location.href='vendor/signout.php'">Выход</div>
		</div>
	</div>
</header>
<main>
	<div class="client-info"><p><?php                                  
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT name, passport FROM clients WHERE id = '" . $_SESSION["client"]["id"] . "'");
		$clientinfo = $result->fetch_row();	
		echo "Работа с клиентом: " . $clientinfo[0] . ", паспорт № " . $clientinfo[1];
	?></p></div>
	<a class="anchor" id="create_account"></a>
	<div class="form">
		<div class="form-name">
			<p>Создать новый счет</p>
		</div>
		<form action="vendor/create_account.php" method="POST">
			<div class="form-content">
				<p>Выберите валюту нового счёта</p>
				<?php
				$mysqli = get_sql_connection();
				$result = $mysqli->query("SELECT * FROM currency");
				$cnt = 0;
				foreach ($result as $res) {
					echo '<input type="radio" name="currency" value="' . $res["code"] . '"' . ($cnt == 0 ? 'checked=1' : '') . '>' . 
					'<label>' . $res["isocode"] . " (" . $res["name"] . ')</label>';
					$cnt++;
				}
				?>
			</div>
			<div><input class="button" type="submit" value="Создать"></div>
			<label class="message"><?php
				echo $_SESSION["message-client"];
				unset($_SESSION["message-client"]);
			?></label>
		</form>
	</div>
	<a class="anchor" id="check_balance"></a>
	<div class="form">
		<div class="form-name"><p>Проверить баланс счета</p></div>
		<form action="vendor/check_balance.php" method="POST">
			<div class="form-content">
				<p>Выберите счет</p>
				<label><div class="select-block"><select name="check_accountnum" required>
					<option selected></option>
					<?php echo out_account_box($_SESSION["client"]["id"]); ?>
				</select></div></label>
			</div>
			<div>
				<input class="button" type="submit" value="Проверить">
			</div>
			<label class="message"><?php
				echo $_SESSION["message-check"];
				unset($_SESSION["message-check"]);
			?></label>
		</form>
	</div>
	<a class="anchor" id="transaction"></a>
	<div class="form">
		<div class="form-name"><p>Перевод средств со счета на счет в пределах одной валюты</p></div>
		<form action="vendor/transaction.php" method="POST">
			<div class="form-content"><p>Счет перевода</p>
				<label><div class="select-block"><select name="debit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box($_SESSION["client"]["id"]); ?>
				</select></div></label>
			</div>
			<div>
				<label>Перевод клиенту с номером телефона:<input type="tel" name="credit_phone" required></label>
			</div>
			<div>
				<label>Сумма перевода<input type="number" name="sum" required></label>
			</div>
			<div>
				<input class="button" type="submit" value="Перевести">
			</div>
			<label class="message"><?php
				echo $_SESSION["message-transaction"];
				unset($_SESSION["message-transaction"]);
			?></label>
		</form>
	</div>
	<a class="anchor" id="push_account"></a>
	<div class="form">
		<div class="form-name"><p>Пополнить счет</p></div>
		<form action="vendor/push_account.php" method="POST">
			<div class="form-content">
				<p>Выберите счет</p>
				<label><div class="select-block"><select name="credit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box($_SESSION["client"]["id"]); ?>
				</select></div></label>
			</div>
			<div>
				<label>Сумма пополнения<input type="number" name="sum" required></label>
			</div>
			<div>
				<input class="button" type="submit" value="Пополнить">
			</div>
			<label class="message"><?php
				echo $_SESSION["message-push"];
				unset($_SESSION["message-push"]);
			?></label>
		</form>
	</div>
</main>
</body>
</html>