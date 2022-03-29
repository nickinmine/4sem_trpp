function createClient() {
	if (document.getElementById("create_client::name").value == "" || 
		document.getElementById("create_client::email").value == "" ||
		document.getElementById("create_client::birthdate").value == "" ||
		document.getElementById("create_client::address").value == "") {
		alert("Заполните все поля");
		return;
	}
	if (!/^[^@]+@[^@]+\.\w+^/.test(document.getElementById("create_client::email").value)) {
		alert("Некорректный ввод электронной почты.\nПочта вводится в формате \"email@email.com\"");
		return;
	}
	if (!/^[0-9]{4} [0-9]{6}$/.test(document.getElementById("create_client::passport").value)) {
		alert("Некорректный ввод паспорта.\nПаспорт вводится в формате \"XXXX XXXXXX\"");
		return;
	}
	                                              
	var request = new XMLHttpRequest();
	request.open('POST', "client.php", true);
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
				+ "&address=" + encodeURIComponent(document.getElementById("create_client::address").value);
	request.send(data);
}

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
