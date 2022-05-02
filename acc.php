<?php
	require "vendor/lib.php";

	safe_session_start();

	if (!$_SESSION['user']) {
		header('Location: /');
	}
	if (($_SESSION['user']['role'] != 'admin') & ($_SESSION['user']['role'] != 'accountant')) {
		header('Location: /');
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
	<link rel="stylesheet" href="css/main-container.css">
	<link rel="icon" type="image/png" href="images/favicon.png">
	<title>Бухгалтер<?php echo " - " . $_SESSION['user']['name']; ?></title>
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
	<div class="main-container">
		<div class="air"></div>
		<div class="column1">
			<div class="form">
				<a class="anchor" id="change_operdate"></a>
				<div class="form-name"><p>Закрытие текущего рабочего дня и открытие следующего</p></div>
				<form action="vendor/change_operdate.php" method="POST">
				<label>Дата нового открытого дня</label>
				<label><input type="date" name="date"></label>
             			<input class="button" type="submit" value="Установить" title="Установить указанный день как текущий">
			<label class="report"><?php echo session_message("message-operdate"); ?></label>
		</form>
	</div>
	<div class="form">
		<a class="anchor" id="transaction_acc"></a>
		<div class="form-name"><p>Перевод средств между кассами и счетами банка</p></div>
		<form action="vendor/transaction_acc.php" method="POST">
			<div class="form-content"><p>Счет отправки перевода (дебета)</p>
				<label><div class="select-block"><select name="debit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box(1, "out_acc"); ?>
				</select></div></label>
			</div>
			<div class="form-content"><p>Счет приема перевода (кредита)</p>
				<label><div class="select-block"><select name="credit_accountnum" required>
					<option selected></option>
					<?php echo out_account_box(1, "out_acc"); ?>
				</select></div></label>
			</div>

			<div>
				<label>Сумма перевода<input pattern="^\d+(\.\d{1,2}|)$" name="sum" required placeholder="100.00"></label>
			</div>
			<div>
				<input class="button" type="submit" value="Перевести" title="Перевести указанную сумму между счетами банка">
			</div>
				<label class="report"><?php echo session_message("message-transaction_acc"); ?></label>
		</form>
	</div>
	</div>
		<div class="column2">
			<div class="form">
				<a class="anchor" id="change_currency_cost"></a>
				<div class="form-name"><p>Обновить курс валют</p></div>
				<form action="vendor/change_currency_cost.php" method="POST">
					<div class="form-content">
						<p>Валюта</p>
						<?php
							$mysqli = get_sql_connection();
							$result = $mysqli->query("SELECT * FROM currency WHERE code != '810'");
							$cnt = 0;
							foreach ($result as $res) {
								echo '<div class="radio-currency"><input type="radio" name="currency" value="' . $res["code"] . '"' .
									($cnt == 0 ? 'checked=1' : '') . '>' . '<label>' . $res["isocode"] . " (" . $res["name"] . ')</label></div>';
								$cnt++;
							}
						?>
					</div>
					<div><label>Стоимость покупки в рублях<input pattern="^\d+(\.\d{1,2}|)$" name="buy_sum" required placeholder="100.00"></label></div>
					<div><label>Стоимость, установленная ЦБ в рублях<input pattern="^\d+(\.\d{1,2}|)$" name="cost_sum" required placeholder="100.00"></label></div>
					<div><label>Стоимость продажи в рублях<input pattern="^\d+(\.\d{1,2}|)$" name="sell_sum" required placeholder="100.00"></label></div>
					<div><input class="button" type="submit" value="Обновить" title="Обновить текущий курс валют"></div>
					<label class="report"><?php echo session_message("message-currency_cost"); ?></label>
				</form>
			</div>
		</div>
		<div class="air"></div>
	</div>
</main>
<script src="js/script.js"></script>
</body>
</html>