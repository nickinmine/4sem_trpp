<?php
	session_start();
	if (!$_SESSION['user']) {
		header('Location: /');
	}
	if ($_SESSION['user']['role'] != 'admin' & $_SESSION['user']['role'] != 'operator') {
		header('Location: /main.php');
		$_SESSION['message'] = 'Отказано в доступе: несоответствие уровня доступа.';
	}
	$passport = $_POST['passport'];
	print_r($_POST);
	if (!$passport || !$_SESSION['client']['id']) {
		$_SESSION['message-client'] = 'Ошибка: не выбран клиент.';
		header('Location: /oper.php');
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
	<?php
	require "vendor/lib.php";
		$mysqli = get_sql_connection();
		$passport = $_POST['passport'];
		$result = mysqli_query($mysqli, "SELECT * FROM clients WHERE passport = '$passport'");
		$client = mysqli_fetch_assoc($result);
		$_SESSION['client'] = [
			"id" => $client['id'],
			"name" => $client['name'],
			"passport" => $client['passport'],
			"passgiven" => $client['passgiven'],
			"passcode" => $client['passcode'],
			"passdate" => $client['passdate'],
			"sex" => $client['sex'],
			"birthdate" => $client['birthdate'],
			"birthplace" => $client['birthplace'],
			"address" => $client['address'],
			"phone" => $client['phone'],
			"email" => $client['email']
		];
		//print_r($idclient);
		#echo "<div class='form-name'>Работа с клиентом: " . $_SESSION['client']["name"] . ", паспорт " . $_SESSION['client']["passport"] . "</div>";
		if (!$_SESSION['client']['passport']) {
			//header('Location: /oper.php');
			$_SESSION['message-client'] = 'Ошибка: клиент не найден.';
		}
	?>
	<div class="client-info">
		<p>
			<?php
				echo "Работа с клиентом: " . $_SESSION['client']["name"] . ", паспорт № " . $_SESSION['client']["passport"]
			?>
		</p>
	</div>
	<div class="form">
		<div class="form-name">
			<p>Создать новый счет</p>
		</div>
		<form id="create_account">
			<div class="form-content">
				<p>Выберите валюту нового счёта</p>
				<?php
				$mysqli = get_sql_connection();
				$result = $mysqli->query("SELECT * FROM currency");
				$cnt = 0;
				foreach ($result as $res) {
					echo "\n" . '<input type="radio" id="create_account::radio_' . $cnt . '" name="currency" value="' . $res["code"] .
						'"' . ($cnt == 0 ? 'checked=1' : '') . '><label for="create_account::radio_' . $cnt++ . '">' . $res["isocode"] .
						" (" . $res["name"] . ')</label>';
				}
				?>
			</div>
			<div>
				<input class="button" type="submit" value="Создать" onclick="createAccount('Счет физ. лица')">
			</div>
		</form>
	</div>
	<div class="form">
		<div class="form-name">
			<p>Проверить баланс счета (не работает)</p>
		</div>
		<form id="check_balance">
			<div class="form-content">
				<p>Выберите счет</p>
				<label>
					<div class="select-block">
						<select form="check_balance" required>
							<option selected id="check_balance::accountnum_0"></option>
							<?php
							$mysqli = get_sql_connection();
							$result = $mysqli->query('SELECT * FROM account WHERE idclient = ' . $_SESSION['client']['id']);
							$cnt = 1;
							foreach ($result as $res) {
								if (!$res["closed"]) {
									echo '<option id="check_balance::accountnum_' . $cnt .
										'" value="' . $res["accountnum"] . '">Счет №' . $res["accountnum"];
									$currency = $mysqli->query('SELECT * FROM currency WHERE code = ' . $res["currency"]);
									foreach ($currency as $cur) {
										echo ': валюта - ' . $cur["name"] . '</option>';
									}
									$cnt++;
								}
							}
							?>
						</select>
					</div>
				</label>
			</div>
			<div>
				<input class="button" type="submit" value="Проверить" onclick="checkBalance()">
			</div>
		</form>
	</div>
	<div class="form">
		<div class="form-name">
			<p>Пополнить счет (не работает)</p>
		</div>
		<form id="deposit_account">
			<div class="form-content">
				<p>Выберите счет</p>
				<label>
					<div class="select-block">
						<select form="deposit_account" required>
							<option id = "deposit_account::accountnum_0" selected></option>
							<?php
							$mysqli = get_sql_connection();
							$result = $mysqli->query('SELECT * FROM account WHERE idclient = ' . $_SESSION['client']['id']);
							$cnt = 1;
							foreach ($result as $res) {
								if (!$res["closed"]) {
									echo '<option id="deposit_account::accountnum_' . $cnt++ .
										'" value = "' . $res["accountnum"] . '">Счет №' . $res["accountnum"];
									$currency = $mysqli->query('SELECT * FROM currency WHERE code = ' . $res["currency"]);
									foreach ($currency as $cur) {
										echo ': валюта - ' . $cur["name"];
									}
								}
							}
							?>
						</select>
					</div>
				</label>
			</div>
			<div>
				<label>Сумма пополнения<input type="number" required="required" id="deposit_account::sum"></label>
			</div>
			<div>
				<input class="button" type="submit" value="Пополнить" onclick="depositAccount()">
			</div>
		</form>
	</div>
	<div class="form">
		<div class="form-name">
			<p>Перевод средств со счета на счет (в пределах одной валюты) (не работает)</p>
		</div>
		<form id="transaction">
			<div class="form-content">
				<p>Счет перевода</p>
				<label>
					<div class="select-block">
						<select form="transaction" required>
							<option id = "transaction::credit_accountnum_0" selected></option>
							<?php
							$mysqli = get_sql_connection();
							$result = $mysqli->query('SELECT * FROM account WHERE idclient = ' . $_SESSION['client']['id']);
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
						</select>
					</div>
				</label>
			</div>
			<div>
				<label>Телефон<input type="text" required="required" id="transaction::phone"></label>
				<input class="button" type="submit" value="Поиск" onclick="findClientIdByPhone()">
				<span id="transaction::credit_client_info" value="12345"></span>
			</div>
			<div>
				<label>Сумма пополнения<input type="number" required="required" id="deposit_account::sum"></label>
			</div>
			<div>
				<input class="button" type="submit" value="Перевести" onclick="transaction()">
			</div>
		</form>
	</div>

</main>
</body>
</html>