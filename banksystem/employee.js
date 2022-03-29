function empEntry() {
	if (document.getElementById("auth::login").value == "" || document.getElementById("auth::password").value == "") {
		alert("Заполните все поля");
		return;
	}	
	
	var request = new XMLHttpRequest();
	request.open('POST', "employee.php", true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.responseType = 'text';
        
	request.onload = function() {
		if (request.response != "")
			window.location.href = "main.php?login=" + request.response;
		else 
			alert("Неверный пароль");
	};

	var data = "login=" + encodeURIComponent(document.getElementById("auth::login").value) + 
		"&password=" + encodeURIComponent(document.getElementById("auth::password").value);
	request.send(data);  
}