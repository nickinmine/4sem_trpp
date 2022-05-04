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
	<link rel="stylesheet" href="css/graph_credit.css">
	<link rel="icon" type="image/png" href="images/favicon.png">
	<title>Информация по кредиту</title>
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
		<div class="column">
		<form action="/operwork.php#graph_credit" method="POST">

		<div class="info-container">
			<div class="form-name"><p>Просмотр текущего состояния кредита</p></div>
		<?php
			$mysqli = get_sql_connection();
			$id = $_POST["idcred"]; // идентификатор кредита

			$curdate = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row()[0];

			$stmt = $mysqli->prepare(
				"SELECT c.id, c.opendate, t.descript, t.rate, t.ovdrate, cur.isocode, " .
				"  c.curacc, c.odacc, c.pcacc, c.prodacc, c.prpcacc, " .
				"  (SELECT `sum` FROM operations WHERE cr = c.odacc ORDER BY idoper LIMIT 1) sumcr " .
				"FROM credits c " .
				"  LEFT JOIN creditterms t ON t.`type` = c.`type` " .
				"  LEFT JOIN currency cur ON cur.`code` = 810 " .
				"WHERE c.id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$cred = $stmt->get_result()->fetch_assoc();
			if (!$cred)
				echo "<p class='infcp'>Информация по кредиту №$id не найдена!</p>\n";
			else {
				$z = NULL; // задолженность
				$res = credit_tail_sum($id, $z, $mysqli);
				verify($res == "", $res);

				echo "<p class='infcph'>Информация по кредиту №" . $cred["id"] . " от " . out_date($cred["opendate"]) . " по состоянию на " . out_date($curdate) . "</p>\n";
				echo "<p class='infcp'>\n";
				echo "Наименование продукта: " . $cred["descript"] . "<br>\n";
				echo "Сумма кредита: " . standart_sum($cred["sumcr"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Процентная ставка: " . sprintf("%.2f", $cred["rate"]) . "% годовых" . "<br>\n";
				echo "Штрафная ставка: " . sprintf("%.2f", $cred["ovdrate"]) . "% годовых" . "<br>\n";
				echo "Дата последнего пересчета: " . out_date($z["update"]) . "<br>\n";
				echo "Текущая сумма основного долга: " . standart_sum($z["od"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Текущая сумма начисленных процентов: " . standart_sum($z["pc"]) . " + " . standart_sum($z["pc2"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Текущая сумма просроченного долга: " . standart_sum($z["prod"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Текущая сумма просроченных процентов: " . standart_sum($z["prpc"]) . " + " . standart_sum($z["prpc2"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Задолженность итоговая: " . standart_sum($z["total"]) . " " . $cred["isocode"] . "<br>\n";
				echo "Остаток на текущем счете " . $cred["curacc"] . ": " . standart_sum($z["cur"]) . " " . $cred["isocode"] . "<br>\n";
				if ($z["cur"] < $z["total"])
					echo "Для немедленного погашения необходимо внести на текущий счет: " . standart_sum($z["total"] - $z["cur"]) . " " . $cred["isocode"] . "<br>\n";
				else
					echo "Средств на текущем счете достаточно для немедленного погашения<br>\n";
				echo "</p>\n";
			}
		?>
		<?php if ($cred) { ?>
		</div>
		<div class="table-container">
			<table>
				<div class="form-name"><p>График погашений</p></div>
				<tr>
					<th>Дата платежа</th>
					<th>Сумма осн. долга</th>
					<th>Сумма процентов</th>
					<th>Всего</th>
					<th>Обработано</th>
				</tr>
				<?php
					$id = $_POST["idcred"]; // идентификатор кредита

					$stmt = $mysqli->prepare( // номер последнего варианта графика
						"SELECT IFNULL(MAX(n), 1) FROM creditgraph WHERE id = ?");
					$stmt->bind_param("i", $id);
					$stmt->execute();
					$n = $stmt->get_result()->fetch_row()[0];

					$stmt = $mysqli->prepare(
						"SELECT dateplat, sumod, sumpc, sumod + sumpc AS sumpl, processed " .
						"FROM creditgraph WHERE id = ? AND n = ? ORDER BY dateplat");
					$stmt->bind_param("ii", $id, $n);
					$stmt->execute();
					$res = $stmt->get_result();
					$ttlod = 0;
					$ttlpc = 0;
					$ttlpl = 0;
					foreach ($res as $row) {
						echo "<tr>\n";
						echo "\t<td>" . out_date($row["dateplat"]) . "</td>\n";
						echo "\t<td>" . standart_sum($row["sumod"]) . "</td>\n";
						echo "\t<td>" . standart_sum($row["sumpc"]) . "</td>\n";
						echo "\t<td>" . standart_sum($row["sumpl"]) . "</td>\n";
						echo "\t<td>" . ($row["processed"] != 0 ? "&check;" : "") . "</td>\n";
						echo "</tr>\n";
						$ttlod += $row["sumod"];
						$ttlpc += $row["sumpc"];
						$ttlpl += $row["sumpl"];
					}
					echo "<tr>\n";
					echo "\t<td>Итого:</td>\n";
					echo "\t<td>" . standart_sum($ttlod) . "</td>\n";
					echo "\t<td>" . standart_sum($ttlpc) . "</td>\n";
					echo "\t<td>" . standart_sum($ttlpl) . "</td>\n";
					echo "\t<td></td>\n";
					echo "</tr>\n";
				?>
			</table>
		</div>
		<?php } ?>
		<input class="button" type="submit" value="Назад" title="Вернуться к работе с клиентом">
		</form>
		</div>
		<div class="air"></div>
	</div>
</main>
</body>
</html>