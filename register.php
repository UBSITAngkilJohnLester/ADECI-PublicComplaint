<?php
	require_once "dbconn.php";

	if (isset($_POST['register'])) {
		$firstname = htmlspecialchars($_POST['firstname']);
		$lastname = htmlspecialchars($_POST['lastname']);
		$username = htmlspecialchars($_POST['username']);
		$password = htmlspecialchars($_POST['password']);
		$status = "user";

		$sql = "INSERT INTO accounts(firstname, lastname, username, password, status) VALUES(?,?,?,?,?)";
		$prep = $pdo->prepare($sql);
		$prep -> execute([
			$firstname,
			$lastname,
			$username,
			$password,
			$status
		]);

		header("location:user/index.php");

	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h1>Government Monitoring System</h1>
	<hr>
	<h2>Register</h2>
	<form method="post">
		<table>
			<tr>
				<td>
					<input type="text" name="firstname" placeholder="First Name" required>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="lastname" placeholder="Last Name" required>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="username" placeholder="Username" required>
				</td>
			</tr>
			<tr>
				<td>
					<input type="password" name="password" placeholder="Password" required>
				</td>
			</tr>
			<tr>
				<td>
					<button name="register">Register</button>
					<a href="index.php">Cancel</a>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>