<?php
	session_start();

	require "vendor/lib.php";
	
	if (!$_SESSION['user']) {
		header('Location: /');
	}
	if ($_SESSION['user']['role'] != 'admin' & $_SESSION['user']['role'] != 'accountant') {
		header('Location: /oper.php');
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
	<title>Бухгалтер<?php
		echo " - " . $_SESSION['user']['name']
		?></title>
</head>
<body>
<header class="header">
	<div class="header-container">
		<div class="header-menu">
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
		$date = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row()[0];
		echo "Текущая дата: " . date("d.m.Y", strtotime($date));
	?></p></div>
	<div class="form">
		<a class="anchor" id="change_operdate"></a>
		<div class="form-name"><p>Закрытие текущего рабочего дня и открытие следующего</p></div>
		<form action="vendor/change_operdate.php" method="POST">
			<label>Дата нового открытого дня</label>
			<label><input type="date" name="date"></label>

			<input class="button" type="submit" value="Установить">	 
			<label class="message"><?php
				echo $_SESSION['message-operdate'];
				unset($_SESSION['message-operdate']);
			?></label>
		</form>
	</div>
	<div class="form">
		<a class="anchor" id="transaction_acc"></a>
		<div class="form-name"><p>Перевод средств между кассами и счетами банка</p></div>
		<form action="vendor/transaction_acc.php" method="POST">
			<div class="form-content"><p>Счет отправки перевода</p>
				<label><div class="select-block"><select name="debit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box(1); ?>
				</select></div></label>
			</div>
			<div class="form-content"><p>Счет приема перевода</p>
				<label><div class="select-block"><select name="credit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box(1); ?>
				</select></div></label>
			</div>
			<div>
				<label>Сумма перевода<input pattern="^\d+([\.,]\d{1,2}|)$" name="sum" required placeholder="100.00"></label>
			</div>
			<div>
				<input class="button" type="submit" value="Перевести">
			</div>
			<label class="message"><?php
				echo $_SESSION["message-transaction_acc"];
				unset($_SESSION["message-transaction_acc"]);
			?></label>
		</form>
	</div>



</main>
<script src="js/script.js"></script>
</body>
</html>