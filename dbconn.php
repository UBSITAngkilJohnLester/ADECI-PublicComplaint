<?php 
	$host = "localhost";
	$user = "root";
	$pass = "";
	$db = "service_monitoring";
	$charset = "utf8mb4";

	$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3308";
	$attributes = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	try {
		$pdo = new PDO($dsn, $user, $pass, $attributes);
	} catch (PDOException $e) {
		die("Connection Error " . $e->getmessage());
	}
?>