<?php
	require_once "../dbconn.php";
	session_start();

	echo $_SESSION['ID'];
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h1>Government Monitoring System (Admin)</h1>
	<nav>
		<a href="accounts.php">Accounts</a>
		<a href="logout.php">Logout</a>
	</nav>
		
	<hr>
	<h2>Complaints</h2>

	
</body>
</html>