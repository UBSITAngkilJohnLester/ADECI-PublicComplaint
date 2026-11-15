<?php
	require_once "../dbconn.php";

	$id = $_GET['id'];

	$delete = "DELETE FROM complaints WHERE id = :id";
	$prep = $pdo->prepare($delete);
	$prep->execute([
		":id" => $id
	]);

	header("location:index.php");
	exit();
?>