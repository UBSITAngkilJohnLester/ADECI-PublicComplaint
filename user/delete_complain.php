<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

$prep = $pdo->prepare("DELETE FROM complaints WHERE id = :id AND acc_id = :accId");
$prep->execute([
	":id"    => $_GET['id'],
	":accId" => $_SESSION['ID']
]);

header("location:index.php");
exit();
?>