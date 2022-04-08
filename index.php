<?php
    session_start();
    if ($_SESSION['user']) {
        header('Location: main.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
    <title>Авторизация</title>
</head>
<body>
    <div class="auth-form">
        <form action="vendor/signin.php" method="post">
            <label>Логин</label>
            <label>
                <input type="text" name="login" placeholder="Введите логин">
            </label>
            <label>Пароль</label>
            <label>
                <input type="password" name="pass" placeholder="Введите пароль">
            </label>
            <button type="submit">Войти</button>
            <label class="message"><?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?></label>
        </form>
    </div>
</body>
</html>