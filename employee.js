function encodeValueById(id) {
	return encodeURIComponent(document.getElementById(id).value);
}

function getValueById(id) {
	return document.getElementById(id).value;
}

function empEntry() {
	if (getValueById("auth::login") == "" || getValueById("auth::password") == "") {
		alert("Заполните все поля");
		return;
	}	
	
	var request = new XMLHttpRequest();
	request.open('POST', "employee.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';
        
	request.onload = function() {
  		alert(request.response);
	};

	var data = "login=" + encodeValueById("auth::login") + "&password=" + encodeValueById("auth::password");
	request.send(data);  
}