<?php
	session_start();

	require "lib.php";      
			
	$_SESSION["message-check"] = "Текущий баланс: " . sprintf("%.2f", check_balance($_POST["check_accountnum"]));
        header("Location: ../operwork.php#check_balance");
		
?>