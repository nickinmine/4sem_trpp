function createAccount(descript) {
	var request = new XMLHttpRequest();
	request.open('POST', "account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {   
  		if (request.response)
			alert("Счет успешно создан");
		else alert("Ошибка при создании счета");
		location.reload(true);
		
	};                           
	
	var data = window.location.search + "&op=create";
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
            
	request.send(data);
}

function checkBalance() {
	if (document.getElementById("check_balance::accountnum_0").selected) {
        	alert("Не выбран счет.");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "account.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
		if (request.response)
			alert("Сумма на счете: " + request.response);
		else alert("Ошибка при проверке баланса счета");
		
	};                           
	
	var data = window.location.search + "&op=check";
	var cnt = 1;          
	while (true) {
		if (document.getElementById("check_balance::accountnum_" + String(cnt)).selected) {
			data += "&accountnum=" + document.getElementById("check_balance::accountnum_" + String(cnt)).value;
			break;
		}
		cnt++;
	}
	                                             
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
	request.open('POST', "account.php", true);
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
