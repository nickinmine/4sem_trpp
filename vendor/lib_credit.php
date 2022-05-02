<?php
	require_once "lib.php";
	
	function out_credit_terms_box() {
		$mysqli = get_sql_connection();
		$result = $mysqli->query("SELECT descript, monthcnt,`type`, rate FROM creditterms ORDER BY monthcnt, rate");
		$str = "";
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["type"] . "\">" . $res["descript"] . " (". $res["monthcnt"] . " мес., ". $res["rate"] . "% годовых)" . "</option>\n";
		}
		return $str;
	}

	function create_credit($type, $sum, $user, $idclient, $pmysqli = NULL) {
		$mysqli = $pmysqli ?? get_sql_connection();

		// создаем счета для КД
		$curacc = ""; // текущий счет
		$odacc = ""; // осн. долг
		$pcacc = ""; // осн. проценты
		$prodacc = ""; // проср. долг
		$prpcacc = ""; // проср. проценты
		$res = create_account($idclient, "810", "40817", "Текущий счет кредита", $curacc, $mysqli);
		if ($res != "")
			return "Не создан текущий счет. " . $res;
		$res = create_account($idclient, "810", "45505", "Учет основного долга по КД", $odacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета основного долга по КД.";
		$res = create_account($idclient, "810", "47427", "Учет процентов по КД", $pcacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";
		$res = create_account($idclient, "810", "45815", "Учет просроченного долга по КД", $prodacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";
		$res = create_account($idclient, "810", "45915", "Учет просроченных процентов по КД", $prpcacc, $mysqli);
		if ($res != "")
			return "Не создан счет учета процентов по КД.";

		// создаем запись в таблице кредитов (сам кредит)
		$stmt = $mysqli->prepare("INSERT INTO credits (idclient, type, opendate, closedate, curacc, odacc, pcacc, prodacc, prpcacc, `update`) " .
			"VALUES (?, ?, (SELECT operdate FROM operdays WHERE current = 1), NULL, ?, ?, ?, ?, ?, NULL)");
		$stmt->bind_param("issssss", $idclient, $type, $curacc, $odacc, $pcacc, $prodacc, $prpcacc);	
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$id = $mysqli->insert_id; // id только что созданной записи в таблице credits

		// переименуем текущий счет кредита (добавим идентификатор договора)
		$stmt = $mysqli->prepare("UPDATE account SET descript = CONCAT(descript, ' №', ?) WHERE accountnum = ?");
		$stmt->bind_param("ss", $id, $curacc);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;

		// создаем проводку по выдаче кредита
		$res = transaction($curacc, $odacc, $sum, $user, $mysqli);
		if ($res != "")
			return $res;

		// строим график погашения
		$res = build_credit_graphic($id, $mysqli);
		if ($res != "")
			return $res;

		return "";
	}

	function build_credit_graphic($id, $pmysqli) {
		$mysqli = $pmysqli ?? get_sql_connection();

		$stmt = $mysqli->prepare("SELECT * FROM credits WHERE id = ?"); // информация по выданному кредиту
		$stmt->bind_param("i", $id);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$cred = $stmt->get_result()->fetch_assoc();
		//addlog("cred = " . var_export($cred, true));

		$stmt = $mysqli->prepare("SELECT * FROM creditterms WHERE `type` = ?"); // условия кредита
		$stmt->bind_param("s", $cred["type"]);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$terms = $stmt->get_result()->fetch_assoc();
		//addlog("terms = " . var_export($terms, true));

		$credsum = check_balance($cred["odacc"], $mysqli); // сумма кредита
		//addlog("credsum = $credsum");

		$stmt = $mysqli->prepare("SELECT IFNULL(MAX(n), 0) FROM creditgraph WHERE id = ?"); // номер графика для построения
		$stmt->bind_param("i", $id);
		if (!$stmt->execute())
			return "MySQL error: " . $mysqli->error;
		$n = $stmt->get_result()->fetch_row()[0] + 1;

		$curdate = $mysqli->query("SELECT operdate FROM operdays WHERE current = 1")->fetch_row()[0]; // текущая дата для расчета
		

		// непосредственно построение
		$monthcnt = $terms["monthcnt"];
		$graph = [];
		for ($i = 1; $i <= $monthcnt; $i++)
			$graph[] = [ "date" => add_months($curdate, $i) ];
		//addlog("monthcnt = $monthcnt");

		$ttldays = diff_date($curdate, $graph[count($graph)-1]["date"]);
		$tailsum = $credsum;
		for ($i = 0; $i < $monthcnt; $i++) {
			$prevdate = ($i == 0 ? $curdate : $graph[$i-1]["date"]);
			$days_in_mon = diff_date($prevdate, $graph[$i]["date"]);
			$graph[$i]["pc"] = round_sum($tailsum * ($terms["rate"] / 100 / 365) * $days_in_mon); // проценты на остаток долга
			if ($i < $monthcnt - 1) { // кроме посл. месяца
				$month_sum = round_sum($credsum * ($days_in_mon / $ttldays)); // основной долг
				$graph[$i]["od"] = $month_sum;
				$tailsum -= $month_sum;
			}
			else { // посл. месяц
				$graph[$i]["od"] = $tailsum;
				$tailsum = 0;
			}
		}
		//addlog("graph = " . var_export($graph, true));
		$stmt = $mysqli->prepare("INSERT INTO creditgraph (id, n, dateplat, sumod, sumpc) VALUES (?, ?, ?, ?, ?)");
		for ($i = 0; $i < count($graph); $i++) {
			$row = $graph[$i];
			//addlog("row = " . var_export($row, true));
			$stmt->bind_param("iisdd", $id, $n, $row["date"], $row["od"], $row["pc"]);
			if (!$stmt->execute())
				return "MySQL error: " . $mysqli->error;
		}
		
		return "";
	}

	function update_credit($id, $new_date, $user, $pmysqli = NULL) { // погашение, вынос на просрочку при изменении даты !!!
		$mysqli = $pmysqli ?? get_sql_connection();
		$user = $_SESSION["user"]["login"];

		try {
			$stmt = $mysqli->prepare("SELECT IFNULL(MAX(n), 0) FROM creditgraph WHERE id = ?"); // номер последнего графика
			$stmt->bind_param("i", $id);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$n = $stmt->get_result()->fetch_row()[0];
			if ($n == 0) { // график погашения не найден (ошибки считаем что нет? это нештатная ситуация)
				addlog("Не найден график погашений по кредиту $id");
				verify(false, "");
			}

			$stmt->prepare( // отрезок графика погашений (с предыдущей датой)
				"SELECT * " .
				"FROM ( " .
				"    SELECT dateplat, sumod, sumpc, processed, " .
				"      LAG(dateplat, 1, (SELECT opendate FROM credits c WHERE c.id = creditgraph.id)) " .
				"        OVER (PARTITION BY id, n ORDER BY dateplat) prevdateplat " .
				"    FROM creditgraph " .
				"    WHERE id = ? AND n = ? AND dateplat < ? " .
				"  ) x " .
				"WHERE processed = 0 " .
				"ORDER BY dateplat");
			$stmt->bind_param("iis", $id, $n, $new_date);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$result = $stmt->get_result();
			$gplat = [];
			foreach ($result as $row)
				$gplat[] = $row;

			if (count($gplat) == 0) { //  нет необработанных дат по графику
				addlog("По КД $id на дату " . out_date($new_date) . " действий не требуется.");
				verify(false, "");
			}

			// информация по договору (id, idclient, type, opendate, closedate, curacc, odacc, pcacc, prodacc, prpcacc, update)
			$stmt = $mysqli->prepare("SELECT * FROM credits WHERE id = ?");
			$stmt->bind_param("i", $id);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$res = $stmt->get_result()->fetch_assoc();
			$type = $res["type"];
			$opendate = $res["opendate"];
			$curacc = $res["curacc"]; // тек. счет
			$odacc = $res["odacc"]; // счет учета осн. долга
			$pcacc = $res["pcacc"]; // счет учета нач. процентов
			$prodacc = $res["prodacc"]; // счет учета проср. долга
			$prpcacc = $res["prpcacc"]; // счет учета проср. процентов
			$update = $res["update"];

			// информация по условиям кредита
			$stmt = $mysqli->prepare("SELECT * FROM creditterms WHERE `type` = ?");
			$stmt->bind_param("s", $type);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$res = $stmt->get_result()->fetch_assoc();
			$rate = $res["rate"];
			$ovdrate = $res["ovdrate"];
		
			// счет доходов банка
			$src_bank_accountnum = "";
			$res = find_bank_account($curacc, "70601%0001", $src_bank_accountnum, $mysqli);
			verify($res == "", "Не найден счет доходов банка: $res");

			// обработка по попавшим в выборку датам графика
			foreach ($gplat as $g) {
				addlog("Гашение КД $id за период " . out_date($g["prevdateplat"]) . " - " . out_date($g["dateplat"]) . 
					" (" . diff_date($g["prevdateplat"], $g["dateplat"]) . " дней)");
				// начисление процентов по осн. долгу (по графику)
				if ($g["sumpc"] > 0.00) {
					addlog("начисление %% на осн. долг: " . standart_sum($g["sumpc"]));
					//addlog("баланс $pcacc до: " . check_balance($pcacc, $mysqli));
					$res = transaction($src_bank_accountnum, $pcacc, $g["sumpc"], $user, $mysqli);
					//addlog("баланс $pcacc после: " . check_balance($pcacc, $mysqli));
					verify($res == "", "Ошибка при начислении процентов на осн. долг: $res");
				}
				// начисление процентов на проср. долг (по сумме)
				$sumprod = check_balance($prodacc, $mysqli);
				if ($sumprod > 0.00) {
					$sumprpc = round_sum($sumprod * ($ovdrate / 100 / 365 * diff_date($g["prevdateplat"], $g["dateplat"])));
					if ($sumprpc > 0.00) {
						addlog("начисление %% на проср. долг: " . standart_sum($sumprpc));
						$res = transaction($src_bank_accountnum, $prpcacc, $sumprpc, $user, $mysqli);
						verify($res == "", "Ошибка при начислении процентов на проср. долг: $res");
					}
				}
				// порядок гашения: 1) проср. проценты, 2) проср. долг, 3) осн. проценты, 4) осн. долг (т.к. может не хватить на всё)
				$balcur = check_balance($curacc, $mysqli); // остаток на текущем счете
				addlog("баланс тек. счета: " . standart_sum($balcur));
				// 1) проср. проценты
				$balprpc = check_balance($prpcacc, $mysqli);
				if ($balprpc > 0.00 && $balcur > 0.00) {
					addlog("баланс проср. %%: " . standart_sum($balprpc));
					$sum = min($balprpc, $balcur);
					addlog("гашение проср. %%: " . standart_sum($sum));
					$res = transaction($prpcacc, $curacc, $sum, $user, $mysqli);
					verify($res == "", "Ошибка при гашении проср. процентов: $res");
					$balcur -= $sum;
					addlog("баланс тек. счета: " . standart_sum($balcur));
				}
				// 2) проср. долг
				$balprod = check_balance($prodacc, $mysqli);
				if ($balprod > 0.00 && $balcur > 0.00) {
					addlog("баланс проср. долга: " . standart_sum($balprod));
					$sum = min($balprod, $balcur);
					addlog("гашение проср. долга: " . standart_sum($sum));
					$res = transaction($prodacc, $curacc, $sum, $user, $mysqli);
					verify($res == "", "Ошибка при гашении проср. долга: $res");
					$balcur -= $sum;
					addlog("баланс тек. счета: " . standart_sum($balcur));
				}
				// 3) осн. проценты
				$balpc = check_balance($pcacc, $mysqli);
				verify($balpc == $g["sumpc"], "Баланс осн. %% (" . $balpc . ") должен совпадать с графиком (" . $g["sumpc"] . ")!");
				if ($balpc > 0.00) {
					addlog("баланс осн. %%: " . standart_sum($balpc));
					$sum = min($balpc, $balcur);
					addlog("гашение осн. %%: " . standart_sum($sum));
					if ($sum > 0.00) {
						$res = transaction($pcacc, $curacc, $sum, $user, $mysqli);
						verify($res == "", "Ошибка при гашении осн. %%: $res");
						$balcur -= $sum;
						$balpc -= $sum;
					}
					if ($balpc > 0.00) { // остались непогашенные проценты - переносим в просроченные
						addlog("перенос осн. %% в просроченные: " . standart_sum($balpc));
						$res = transaction($pcacc, $prpcacc, $balpc, $user, $mysqli);
						verify($res == "", "Ошибка при переносе осн. %% в просроченные: $res");
					}
					addlog("баланс тек. счета: " . standart_sum($balcur));
				}
				// 4) основной долг (не весь, сумма из графика погашений)
				$balod = check_balance($odacc, $mysqli);
				verify($balod >= $g["sumod"], "Баланс осн. долга (" . $balod . ") не должен быть меньше графика (" . $g["sumod"] . ")!");
				if ($balod > 0.00) {
					addlog("баланс осн. долга: " . standart_sum($balod) . "; сумма по графику: " . standart_sum($g["sumod"]));
					$sum = min($g["sumod"], $balcur);
					addlog("гашение осн. долга: " . standart_sum($sum));
					if ($sum > 0.00) {
						$res = transaction($odacc, $curacc, $sum, $user, $mysqli);
						verify($res == "", "Ошибка при гашении осн. долга: $res");
					}
					if ($sum < $g["sumod"]) {
						$sum2 = round_sum($g["sumod"] - $sum);
						addlog("перенос осн. долга в просроченный: " . standart_sum($sum2));
						$res = transaction($odacc, $prodacc, $sum2, $user, $mysqli);
						verify($res == "", "Ошибка при переносе осн. долга в просроченный: $res");
					}
				}
				// поставим флаг в графике, что строка обработана, обновим дату посл. обновления в поле `update`
				$stmt = $mysqli->prepare("UPDATE creditgraph SET processed = 1 WHERE id = ? AND n = ? AND dateplat = ?");
				$stmt->bind_param("iis", $id, $n, $g["dateplat"]);
				verify($stmt->execute(), "MySQL error: " . $mysqli->error);
				$stmt = $mysqli->prepare("UPDATE credits SET `update` = ? WHERE id = ?");
				$stmt->bind_param("si", $g["dateplat"], $id);
				verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			} // foreach(...

			// если задолженность полностью погашена - автоматически закроем кредит
			$z = NULL;
			verify(credit_tail_sum($id, $z, $mysqli) == "", "Ошибка определения задолженности по кредиту $id");
			if ($z["total"] == 0.00) {
				$res = close_credit($id, $mysqli);
				verify($res == "", "Ошибка закрытия кредита $id: $res");
			}

		} // try
		catch (Exception $e) {
			$errmsg = $e->getMessage();
			if ($errmsg != "")
				addlog("ошибка при выполнении update_credit(): $errmsg");
			return $errmsg;
		}

		return "";
	}

	function credit_tail_sum($id, &$pvzadolg, $pmysqli = NULL) { // сумма необходимая для погашения кредита в текущий день
		$mysqli = $pmysqli ?? get_sql_connection();
		$pvzadolg = [];

		try {
			// информация по договору
			$stmt = $mysqli->prepare(
				"SELECT c.curacc, c.odacc, c.pcacc, c.prodacc, c.prpcacc, t.rate, t.ovdrate, " .
				"  (SELECT operdate FROM operdays WHERE current = 1) curdate, " .
				"  COALESCE(`update`, (SELECT MAX(dateplat) FROM creditgraph g WHERE g.id = c.id AND g.processed = 1), opendate) lastupdate " .
				"FROM credits c " .
				"  LEFT JOIN creditterms t ON t.`type` = c.`type` " .
				"WHERE c.id = ?");
			$stmt->bind_param("i", $id);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$res = $stmt->get_result()->fetch_assoc();
			verify($res, "Не найдена информация по кредиту $id");
			$curdate = $res["curdate"]; // тек. дата

			// считаем остаток задолженности
			$pvzadolg["update"] = $res["lastupdate"]; // дата последнего обновления кредита
			$pvzadolg["taildays"] = diff_date($res["lastupdate"], $res["curdate"]); // кол-во дней после посл. обновления кредита
			$pvzadolg["od"] = check_balance($res["odacc"], $mysqli); // осн. долг
			$pvzadolg["pc"] = check_balance($res["pcacc"], $mysqli); // %%
			$pvzadolg["prod"] = check_balance($res["prodacc"], $mysqli); // проср. долг
			$pvzadolg["prpc"] = check_balance($res["prpcacc"], $mysqli); // проср. %%
			$pvzadolg["pc2"] = round_sum($pvzadolg["od"] * ($res["rate"] / 100 / 365) * $pvzadolg["taildays"]); // %% после посл. обновления
			$pvzadolg["prpc2"] = round_sum($pvzadolg["prod"] * ($res["ovdrate"] / 100 / 365) * $pvzadolg["taildays"]); // штраф. %% после посл. обн.
			$pvzadolg["cur"] = check_balance($res["curacc"], $mysqli); // остаток на текущем счете
			$pvzadolg["total"] = $pvzadolg["od"] + $pvzadolg["pc"] 
				+ $pvzadolg["prod"] + $pvzadolg["prpc"] 
				+ $pvzadolg["pc2"] + $pvzadolg["prpc2"];
		}
		catch (Exception $e) {
			return $e->getMessage();
		}

		return "";
	}

	function close_credit($id, $pmysqli = NULL) { // закрытие кредита (всех счетов и договора, договор должен быть погашен)
		$mysqli = $pmysqli ?? get_sql_connection();
		try {
			// проверим отсутствие задолженности
			$z = NULL;
			verify(credit_tail_sum($id, $z, $mysqli) == "", "Ошибка определения задолженности по кредиту $id");
			verify($z["total"] == 0.00, "По кредиту имеется задолженность в размере " . standart_sum($z["total"]));

			// получим данные по договору
			$stmt = $mysqli->prepare("SELECT * FROM credits WHERE id = ?");
			$stmt->bind_param("i", $id);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
			$cred = $stmt->get_result()->fetch_assoc();
			verify($cred, "Не найдена информация по договору $id");
			verify($cred["closedate"] == "0000-00-00" || $cred["closedate"] == NULL, "Договор $id уже был закрыт " . out_date($cred["closedate"]));

			// закроем счета
			$res = close_account($cred["odacc"], $mysqli);
			verify($res == "", "Ошибка закрытия счета учета осн. долга по кредиту $id: $res");
			$res = close_account($cred["pcacc"], $mysqli);
			verify($res == "", "Ошибка закрытия счета учета процентов по кредиту $id: $res");
			$res = close_account($cred["prodacc"], $mysqli);
			verify($res == "", "Ошибка закрытия счета учета проср. долга по кредиту $id: $res");
			$res = close_account($cred["prpcacc"], $mysqli);
			verify($res == "", "Ошибка закрытия счета учета проср. процентов по кредиту $id: $res");
			if (check_balance($cred["curacc"], $mysqli) == 0.00) {
				$res = close_account($cred["curacc"], $mysqli);
				verify($res == "", "Ошибка закрытия текущего счета по кредиту $id: $res");
			}

			// закроем сам договор
			$stmt = $mysqli->prepare(
				"UPDATE credits SET " .
				"  closedate = (SELECT operdate FROM operdays WHERE current = 1), " .
				"  `update` = (SELECT operdate FROM operdays WHERE current = 1) " .
				"WHERE id = ?");
			$stmt->bind_param("i", $id);
			verify($stmt->execute(), "MySQL error: " . $mysqli->error);
		}
		catch (Exception $e) {
			return $e->getMessage();
		}

		return "";
	}

	function out_client_credit_box($idclient) { // список действующих кредитов (для выпадающего списка)
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare(
			"SELECT c.id, t.descript, t.rate, cur.isocode, " . 
			"  (SELECT `sum` FROM operations WHERE cr = c.odacc ORDER BY idoper LIMIT 1) sumcr " .
			"FROM credits c " .
			"  LEFT JOIN creditterms t ON t.`type` = c.`type` " .
			"  LEFT JOIN currency cur ON cur.`code` = 810 " .
			"WHERE c.idclient = ? AND (c.closedate = '0000-00-00' OR c.closedate IS NULL)");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$str = "";
		$result = $stmt->get_result();
		foreach ($result as $res) {
			$str .= "<option value = \"" . $res["id"] . "\">Кредит №" . $res["id"] . " " . $res["descript"] .
				", сумма " . standart_sum($res["sumcr"]) . " " . $res["isocode"] .
				", ставка " . sprintf("%.2f", $res["rate"]). "%". "</option>\n";
		}
		return $str;
	}

	function client_credit_count($idclient) { // кол-во действующих кредитов у клиента
		$mysqli = get_sql_connection();
		$stmt = $mysqli->prepare("SELECT COUNT(*) FROM credits WHERE idclient = ? AND (closedate = '0000-00-00' OR closedate IS NULL)");
		$stmt->bind_param("i", $idclient);
		$stmt->execute();
		$cnt = $stmt->get_result()->fetch_row()[0];
		return $cnt;
	}

	
?>