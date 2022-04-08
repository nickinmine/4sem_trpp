function findClientId() {
    if (!/^[0-9]{4} [0-9]{6}$/.test(document.getElementById("find_client_id::passport").value)) {
        alert("Некорректный ввод паспорта.\nПаспорт вводится в формате \"XXXX XXXXXX\"");
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', "client.php", true);
    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    request.responseType = 'text';

    request.onload = function() {
        if (request.response != "")
            window.location.href = "clientwork.php" + window.location.search + "&idclient=" + request.response;
        else
            alert("Клиент не найден");
    };

    var data = "op=find" + "&passport=" + encodeURIComponent(document.getElementById("find_client_id::passport").value);
    request.send(data);
}

function createClient() {
    if (document.getElementById("create_client::name").value == "" ||
        document.getElementById("create_client::email").value == "" ||
        document.getElementById("create_client::birthdate").value == "" ||
        document.getElementById("create_client::address").value == "") {
        alert("Заполните все поля");
        return;
    }
    if (!/^[^@]+@[^@]+\.\w+$/.test(document.getElementById("create_client::email").value)) {
        alert("Некорректный ввод электронной почты.\nПочта вводится в формате \"email@email.com\"");
        return;
    }
    if (!/^[0-9]{4} [0-9]{6}$/.test(document.getElementById("create_client::passport").value)) {
        alert("Некорректный ввод паспорта.\nПаспорт вводится в формате \"XXXX XXXXXX\"");
        return;
    }
    if (!/^\+?[0-9]{6,11}$/.test(document.getElementById("create_client::phone").value)) {
        alert("Некорректный ввод номера телефона.");
        return;
    }
    /*
    var request = new XMLHttpRequest();
    request.open('POST', "vendor/reg.php", true);
    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    request.responseType = 'text';

    request.onload = function() {
        alert(request.response);
        location.reload(true);
    };

    var data =  "op=create" + "&name=" + encodeURIComponent(document.getElementById("create_client::name").value)
        + "&email=" + encodeURIComponent(document.getElementById("create_client::email").value)
        + "&birthdate=" + encodeURIComponent(document.getElementById("create_client::birthdate").value)
        + "&passport=" + encodeURIComponent(document.getElementById("create_client::passport").value)
        + "&address=" + encodeURIComponent(document.getElementById("create_client::address").value)
        + "&phone=" + encodeURIComponent(document.getElementById("create_client::phone").value);
    request.send(data);*/
}

function createAccount(descript) {
	var request = new XMLHttpRequest();
	request.open('POST', "./vendor/account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		alert(request.response);
		/*if (request.response)
			alert("Счет успешно создан");
		else
			alert("Ошибка при создании счета");
		location.reload(true);*/
	};

	//var data = window.location.search + "&op=create";
        var data = "op=create";
	var cnt = 0;
	var currency = "000";

	for (cnt = 0; ; cnt++) {
		if (document.getElementById("create_account::radio_" + String(cnt)).checked) {
			currency = encodeURIComponent(document.getElementById("create_account::radio_" + String(cnt)).value);
			data += "&currency=" + currency;
			break;
		}
	}

	data += "&descript=" + descript;
	data += "&closed=0";

	alert(data);

	request.send(data);
}

function checkBalance() {
	if (document.getElementById("check_balance::accountnum_0").selected) {
		alert("Не выбран счет.");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "./vendor/account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		if (request.response)
			alert("Сумма на счете: " + request.response);
		else alert("Ошибка при проверке баланса счета");

	};

	/*var data = window.location.search + "&op=check";
	var cnt = 1;
	while (true) {
		if (document.getElementById("check_balance::accountnum_" + String(cnt)).selected) {
			data += "&accountnum=" + document.getElementById("check_balance::accountnum_" + String(cnt)).value;
			break;
		}
		cnt++;
	}*/

	request.send(data);
}

function depositAccount() {
	if (document.getElementById("deposit_account::accountnum_0").selected) {
		alert("Не выбран счет.");
		return;
	}
	if (!/^[0-9]+\.[0-9]{2}$/.test(document.getElementById("deposit_account::sum").value)) {
		alert("Некорректный ввод суммы пополнения счета.\nСумма вводится в формате \"100.00\".");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		if (request.response)
			alert("Счет успешно пополнен");
		else alert("Ошибка при пополнении счета");

	};

	var data = window.location.search + "&op=deposit";
	var cnt = 1;
	while (true) {
		if (document.getElementById("deposit_account::accountnum_" + String(cnt)).selected) {
			data += "&accountnum=" + document.getElementById("deposit_account::accountnum_" + String(cnt)).value;
			break;
		}
		cnt++;
	}
	data += "&sum=" + document.getElementById("deposit_account::sum").value;

	request.send(data);
}

function findClientIdByPhone() {
	if (!/^\+?[0-9]{6,11}$/.test(document.getElementById("transaction::phone").value)) {
		alert("Некорректный ввод номера телефона.");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "../vendor/account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		if (request.response != "") {
			//alert(request.response);
			document.getElementById("transaction::credit_client_info").innerHTML = request.response;
		}
		else document.getElementById("transaction::credit_client_info").innerHTML = "Клиент не найден";
	};

	var data = "op=findphone" + "&phone=" + encodeURIComponent(document.getElementById("transaction::phone").value);
	request.send(data);
}


function transaction() {
	if (document.getElementById("transaction::credit_accountnum_0").selected) {
		alert("Не выбран счет перевода.");
		return;
	}
	if (document.getElementById("transaction::debit_accountnum_0").selected) {
		alert("Не выбран пополняемый счет.");
		return;
	}
	if (!/^[0-9]+\.[0-9]{2}$/.test(document.getElementById("transaction::sum").value)) {
		alert("Некорректный ввод суммы пополнения счета.\nСумма вводится в формате \"100.00\".");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		if (request.response)
			alert("Успешный перевод");
		else alert("Ошибка при переводе");

	};

	var data = window.location.search + "&op=transaction";
	var cnt = 1;
	while (true) {
		if (document.getElementById("deposit_account::accountnum_" + String(cnt)).selected) {
			data += "&accountnum=" + document.getElementById("deposit_account::accountnum_" + String(cnt)).value;
			break;
		}
		cnt++;
	}
	data += "&sum=" + document.getElementById("deposit_account::sum").value;


	alert(data);

	request.send(data);
}