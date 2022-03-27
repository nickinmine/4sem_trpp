<?php
	require "lib.php";

	$mysqli = get_connection();
        $result = $mysqli->query("SELECT * FROM employee WHERE login LIKE \"%" . $_POST["login"] . "%\" AND password LIKE \"%" . $_POST["password"] . "%\"");
        foreach ($result as $res) {
		header("Location: main.php");
		exit;
	}
	
?>