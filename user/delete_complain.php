<?php
	session_start();
	require_once "../dbconn.php";

	$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
	$prep->execute([$_SESSION['ID']]);
	$account = $prep->fetch();

	if (!isset($_SESSION['ID']) || $account['status'] != 'user') {
		session_destroy();
		header("location:../index.php");
		exit();
	}

	$select = "SELECT photo_path FROM complaints WHERE id = :id AND acc_id = :accId AND status = 'Pending'";
	$prep = $pdo->prepare($select);
	$prep->execute([
		":id"    => $_GET['id'],
		":accId" => $_SESSION['ID']
	]);
	$rows = $prep->fetchAll();

	if (count($rows) > 0) {
		// deletes the photo if there is one
		$photo = $rows[0]['photo_path'];
		if ($photo != '' && file_exists($photo)) {
			unlink($photo);
		}

		$delete = "DELETE FROM complaints WHERE id = :id AND acc_id = :accId AND status = 'Pending'";
		$prep = $pdo->prepare($delete);
		$prep->execute([
			":id"    => $_GET['id'],
			":accId" => $_SESSION['ID']
		]);
	}

	header("location:index.php");
	exit();
?>