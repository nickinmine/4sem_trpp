<?php
	session_start();
	if (!$_SESSION['user']) {
		header('Location: /');
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
    <title>Главная<?php
        echo " - " . $_SESSION['user']['name']
    ?></title>
</head>
<body>
<header>
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
<div class="access-error message">
    <?php
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    ?>
</div>
<main>
    <p>Здесь пока ничего нет...</p>



</main>
<script src="js/script.js"></script>
</body>
</html>