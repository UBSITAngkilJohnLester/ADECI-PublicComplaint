<?php
	session_start();
	require_once "dbconn.php";

	if (isset($_POST['login'])) {
		$username = htmlspecialchars($_POST['username']);
		$password = htmlspecialchars($_POST['password']);

		$sql = "SELECT * FROM accounts WHERE username = ?";
		$prep = $pdo->prepare($sql);
		$prep->execute([$username]);
		$rows = $prep->fetchAll();

		if (count($rows) <1) {
			header("location:index.php");
		}else{
			foreach ($rows as $row) {
				if ($password == $row['password'] && $row['status'] =="admin") {
					$_SESSION['USERNAME'] = $row['username'];
					$_SESSION['FIRSTNAME'] = $row['firstname'];
					$_SESSION['ID'] = $row['ID'];

					header("location:admin/index.php");
					exit();

				}
				else if ($password == $row['password'] && $row['status']== "user") {
					$_SESSION['USERNAME'] = $row['username'];
					$_SESSION['FIRSTNAME'] = $row['firstname'];
					$_SESSION['ID'] = $row['ID'];

					header("location:user/index.php");
					exit();
				}
				else{
					header("location:index.php");
					exit();
				}
			}
		}

		
	}

?>