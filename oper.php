<?php
    session_start();
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
    <title>Оператор</title>
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
    <div class="form">
        <div class="form-name">
            <p>Поиск клиента</p>
        </div>
        <form id="find_client_id" action="clientwork.php" method="post">
            <label>Номер паспорта</label>
            <label>
                <input pattern="^[0-9]{4} [0-9]{6}$" name="passport" required="required" id="find_client_id::passport" placeholder="Серия и номер">
            </label>
            <input class="button" type="submit" value="Начать" onclick="findClientId()">
            <label class="message"><?php
                echo $_SESSION['message-client'];
                unset($_SESSION['message-client']);
                ?></label>
        </form>
    </div>

    <div class="form">
        <div class="form-name">
            <p>Создать профиль клиента</p>
        </div>
        <form id="create_client" action="vendor/reg.php" method="post">
            <label>Фамилия, имя, отчество</label>
            <label>
                <input type="text" required="required" name="name" id="create_client::name" placeholder="Иванов Иван Иванович">
            </label>
            <label>Номер паспорта</label>
            <label>
                <input pattern="^[0-9]{4} [0-9]{6}$" name="idclient" required="required" id="create_client::passport" placeholder="Серия и номер">
            </label>
            <label>Кем выдан</label>
            <label>
                <input type="text" name="passgiven" required="required" id="create_client::passgiven" placeholder="Название подразделения">
            </label>
            <label>Код подразделения</label>
            <label>
                <input type="text" name="passcode" required="required" id="create_client::passcode" placeholder="000-000">
            </label>
            <label>Дата выдачи</label>
            <label>
                <input type="date" required="required" name="passdate" id="create_client::passdate">
            </label>
            <label>Пол</label>
            <label>
                <input type="text" name="sex" required="required" id="create_client::sex" placeholder="М/Ж">
            </label>
            <label>Дата рождения</label>
            <label>
                <input type="date" required="required" name="birthdate" id="create_client::birthdate">
            </label>
            <label>Место рождения</label>
            <label>
                <input type="text" name="birthplace" required="required" id="create_client::birthplace" placeholder="Регион, город">
            </label>
            <label>Адрес</label>
            <label>
                <input type="text" required="required" name="address" id="create_client::address" placeholder="Индекс, регион, город, улица, дом, квартира">
            </label>
            <label>Телефон</label>
            <label>
                <input type="tel" required="required" name="phone" id="create_client::phone" placeholder="Номер телефона">
            </label>
            <label>Электронная почта</label>
            <label>
                <input type="email" required="required" name="email" id="create_client::email" placeholder="example@email.com">
            </label>
            <input class="button" type="submit" value="Создать" onclick="createClient()">
        </form>
    </div>


</main>
<script src="js/script.js"></script>
</body>
</html>