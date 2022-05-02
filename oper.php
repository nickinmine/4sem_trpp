<?php
	require "vendor/lib.php";

	safe_session_start();
	
	if (!$_SESSION['user']) {
		header('Location: /');
	}
	if (($_SESSION['user']['role'] != 'admin') & ($_SESSION['user']['role'] != 'operator')) {
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
	<title>Оператор<?php
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
	<div class="main-container">
		<div class="air"></div>
		<div class="column1">
			<div class="form">
				<div class="form-name"><p>Поиск клиента</p></div>
				<form action="vendor/find_client_by_passport.php" method="POST">
					<?php //update_deposit(3, "2023-03-25", $_SESSION["user"]["login"]);
					//echo check_balance("42301810100010000003");
					//echo check_balance("42301810100010000002");
					/*$opendate = "2022-01-31";
					$capdate = []; // даты капитализации
					for ($i = 1; $i <= 12; $i++) {
						$capdate[$i] = add_months($opendate, $i);
					}
					for ($i = 1; $i <= 12; $i++) {
						echo $capdate[$i] . " ";
					}*/ 
					?>
					<label>Номер паспорта</label>
					<label><input pattern="^[0-9]{4} [0-9]{6}$" name="passport" required="required" placeholder="Серия и номер"></label>
					<input class="button" type="submit" value="Поиск" title="Начать поиск клиента по номеру паспорта">
					<label class="message"><?php echo session_message("message-client"); ?></label>
				</form>
			</div>
		</div>
		<div class="column2">
			<div class="form">
				<div class="form-name"><p>Создать профиль клиента</p></div>
				<form action="vendor/create_client.php" method="POST">
					<label>Фамилия, имя, отчество</label>
					<label><input type="text" required="required" name="name" placeholder="Иванов Иван Иванович"></label>

					<label>Телефон</label>
					<label><input type="tel" required="required" name="phone" placeholder="Номер телефона"></label>

					<label>Номер паспорта</label>
					<label><input pattern="^[0-9]{4} [0-9]{6}$" required="required" name="passport" placeholder="Серия и номер"></label>

					<label>Кем выдан</label>
					<label><input type="text" name="passgiven" placeholder="Название подразделения"></label>

					<label>Код подразделения</label>
					<label><input pattern="^([0-9]{3}\-[0-9]{3}|)$" name="passcode" placeholder="000-000"></label>

					<label>Дата выдачи</label>
					<label><input type="date" name="passdate"></label>

					<label>Пол</label>
					<label><input pattern="^[МЖ]$" name="sex" placeholder="М/Ж"></label>

					<label>Дата рождения</label>
					<label><input type="date" name="birthdate"></label>

					<label>Место рождения</label>
					<label><input type="text" name="birthplace" placeholder="Регион, город"></label>

					<label>Адрес регистрации</label>
					<label><input type="text" name="reg" placeholder="Индекс, регион, город, улица, дом, квартира"></label>

					<label>Адрес проживания</label>
					<label><input type="text" name="address" placeholder="Индекс, регион, город, улица, дом, квартира"></label>

					<label>Электронная почта</label>
					<label><input type="email" name="email" placeholder="example@email.com"></label>

					<input class="button" type="submit" value="Создать" title="Создать новый профиль клиента">
					<label class="message"><?php echo session_message("message-create-client"); ?></label>
				</form>
			</div>
		</div>
		<div class="air"></div>
	</div>

</main>
<script src="js/script.js"></script>
</body>
</html>