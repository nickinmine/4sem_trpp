<!DOCTYPE html> 
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Тестирвание MySQL из PHP</title>
</head>
<body>


<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli("localhost", "root", "", "university");

$result = $mysqli->query("select * from speclist");

foreach ($result as $row) {
    echo "<p> id = " . $row['id'] . "; name = '" . $row['name'] . "'</p>";
}


?>


</body>
</html>