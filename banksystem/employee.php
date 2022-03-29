<?php
	require "lib.php";
	
	$mysqli = get_sql_connection();
        $result = $mysqli->query("SELECT * FROM employee WHERE login = \"" . $_POST["login"] . "\" AND password = \"" . $_POST["password"] . "\"");
        foreach ($result as $res) {
		echo $res["login"];
		break;
	}
	
?>