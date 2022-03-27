<?php
	function get_connection() { 
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$mysqli = new mysqli("localhost", "root", "", "bankbase");
		return $mysqli;
	}

	function sql_date($date) {
		return substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
	}
?>
