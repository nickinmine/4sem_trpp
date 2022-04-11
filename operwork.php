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
		$stmt = $mysqli->prepare("SELECT name, passport FROM clients WHERE id = ?");
		$clientid = $_SESSION["client"]["id"];
		$stmt->bind_param("i", $clientid);
		$stmt->execute();
		$clientinfo = $stmt->get_result()->fetch_row();	
		echo "Работа с клиентом: " . $clientinfo[0] . ", паспорт № " . $clientinfo[1];
	?></p></div>
	<div class="form">
		<a class="anchor" id="create_account"></a>
		<div class="form-name"><p>Создать новый счет</p></div>
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
	<div class="form">
		<a class="anchor" id="push_account"></a>
		<div class="form-name"><p>Пополнить счет</p></div>
		<form action="vendor/push_account.php" method="POST">
			<div class="form-content">
				<p>Выберите счет</p>
				<label><div class="select-block"><select name="credit_accountnum" required>
					<option selected> - Выберите счет - </option>
					<?php echo out_account_box($_SESSION["client"]["id"]); ?>
				</select></div></label>
			</div>
			<div>
				<label>Сумма пополнения<input pattern="^[0-9]+\.[0-9]{2}$" name="sum" required placeholder="100.00"></label>
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
	<div class="form">
		<a class="anchor" id="transaction"></a>
		<div class="form-name"><p>Перевод средств со счета на счет в пределах одной валюты</p></div>
		<form action="vendor/transaction.php" method="POST">
			<div class="form-content"><p>Счет перевода</p>
				<label><div class="select-block"><select name="debit_accountnum" required>
					<option selected> - Выберите счет - </option>
					<?php echo out_account_box($_SESSION["client"]["id"]); ?>
				</select></div></label>
			</div>
			<div>
				<label>Перевод клиенту с номером телефона:<input type="tel" name="credit_phone" required placeholder="+78005553535"></label>
			</div>
			<div>
				<label>Сумма перевода<input pattern="^[0-9]+\.[0-9]{2}$" name="sum" required placeholder="100.00"></label>
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
       	<div class="form">
		<a class="anchor" id="edit_client"></a>
		<div class="form-name"><p>Редактировать профиль клиента</p></div>
		<form action="vendor/edit_client.php" method="POST">
			<label>Фамилия, имя, отчество</label>
			<label><input type="text" required="required" name="name" placeholder="Иванов Иван Иванович" <?php echo out_value("name"); ?>></label>

			<label>Телефон</label>
			<label><input type="tel" required="required" name="phone" placeholder="+78005553535" <?php echo out_value("phone"); ?>></label>

			<label>Номер паспорта</label>
			<label><input pattern="^[0-9]{4} [0-9]{6}$" required="required" name="passport" placeholder="Серия и номер" <?php echo out_value("passport"); ?>></label>
			
			<label>Кем выдан</label>
			<label><input type="text" name="passgiven" placeholder="Название подразделения" <?php echo out_value("passgiven"); ?>></label>

			<label>Код подразделения</label>
			<label><input pattern="^([0-9]{3}\-[0-9]{3}|)$" name="passcode" placeholder="000-000" <?php echo out_value("passcode"); ?>></label>

			<label>Дата выдачи</label>
			<label><input type="date" name="passdate" <?php echo out_value("passdate"); ?>></label>

			<label>Пол</label>
			<label><input pattern="^[МЖ]$" name="sex" placeholder="М/Ж" <?php echo out_value("sex"); ?>></label>

			<label>Дата рождения</label>
			<label><input type="date" name="birthdate" <?php echo out_value("birthdate"); ?>></label>

			<label>Место рождения</label>
			<label><input type="text" name="birthplace" placeholder="Регион, город" <?php echo out_value("birthplace"); ?>></label> 

			<label>Адрес</label>
			<label><input type="text" name="address" placeholder="Индекс, регион, город, улица, дом, квартира" <?php echo out_value("address"); ?>></label> 
   			
			<label>Электронная почта</label>
			<label><input type="email" name="email" placeholder="example@email.com" <?php echo out_value("email"); ?>></label>  

			<input class="button" type="submit" value="Сохранить">     
			<label class="message"><?php
                		echo $_SESSION['message-edit'];
                		unset($_SESSION['message-edit']);
                	?></label>
		</form>
	</div>

</main>
</body>
</html>