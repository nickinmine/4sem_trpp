function encodeValueById(id) {
	return encodeURIComponent(document.getElementById(id).value);
}

function getValueById(id) {
	return document.getElementById(id).value;
}

function createClient() {
	if (getValueById("create_client::name") == "" || 
		getValueById("create_client::email") == "" ||
		getValueById("create_client::birthdate") == "" ||
		getValueById("create_client::address") == "") {
		alert("Заполните все поля");
		return;
	}
	if (!/^[^@]+@[^@]+\.\w+^/.test(getValueById("create_client::email"))) {
		alert("Некорректный ввод электронной почты.\nПочта вводится в формате \"email@email.com\"");
		return;
	}
	if (!/^[0-9]{4} [0-9]{6}$/.test(getValueById("create_client::passport"))) {
		alert("Некорректный ввод паспорта.\nПаспорт вводится в формате \"XXXX XXXXXX\"");
		return;
	}
	                                              
	var request = new XMLHttpRequest();
	request.open('POST', "client.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
  		alert(request.response);
	};

	var data =  "op=create" + "&name=" + encodeValueById("create_client::name")
				+ "&email=" + encodeValueById("create_client::email")
				+ "&birthdate=" + encodeValueById("create_client::birthdate")
				+ "&passport=" + encodeValueById("create_client::passport")
				+ "&address=" + encodeValueById("create_client::address");
	request.send(data);
}

function findClientId() {
	if (!/^[0-9]{4} [0-9]{6}$/.test(getValueById("find_client_id::passport"))) {
		alert("Некорректный ввод паспорта.\nПаспорт вводится в формате \"XXXX XXXXXX\"");
		return;
	}

	var request = new XMLHttpRequest();
	request.open('POST', "client.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';

	request.onload = function() {
  		alert(request.response);
	};

	var data =  "op=find" + "&passport=" + encodeValueById("find_client_id::passport");
	request.send(data);
}
