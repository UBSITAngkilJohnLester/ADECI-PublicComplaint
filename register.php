<?php
session_start();
require_once "dbconn.php";

$message = "";
$wrongcontact = "";

$usernameexist = false;
$invalidcontact = false;

$firstname = "";
$lastname = "";
$username = "";
$address = "";
$contact = "";

if (isset($_POST['register'])) {
	$firstname = htmlspecialchars($_POST['firstname']);
	$lastname = htmlspecialchars($_POST['lastname']);
	$username = htmlspecialchars($_POST['username']);
	$address = htmlspecialchars($_POST['address']);
	$contact = trim(htmlspecialchars($_POST['contact']));
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$status = "user";

	$sql = "SELECT username FROM accounts WHERE username = ?";
	$prep = $pdo->prepare($sql);
	$prep->execute([$username]);
	$rows = $prep->fetch();

	if ($rows) {
		$usernameexist = true;
		$message = "Username already exists. Try a different one.";
		$username = "";
	}
	
	if (!preg_match('/^[0-9]{11}$/', $contact)) {
		$invalidcontact = true;
		$wrongcontact = "Invalid contact number.";
		$contact = "";
	} 
	
	if(!$usernameexist && !$invalidcontact){
		$sql2 = "INSERT INTO accounts(firstname, lastname, username, password, contact_number, address, status) VALUES(?,?,?,?,?,?,?)";
		$prep2 = $pdo->prepare($sql2);
		$prep2->execute([
			$firstname,
			$lastname,
			$username,
			$password,	
			$contact,
			$address,
			$status
		]);

		$_SESSION['USERNAME'] = $username;
		$_SESSION['FIRSTNAME'] = $firstname;
		$_SESSION['ID'] = $pdo->lastInsertId();

		header("location:user/index.php");
		exit();
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">

</head>

<style>
	html {
		scrollbar-gutter: stable;
	}
</style>

<body class="bg-secondary-subtle">

	<div class="container min-vh-100 d-flex justify-content-center align-items-center">
		<div class="card show p-4" style="width: 100%; max-width: 450px;">
			<?php if (!empty($message)) { ?>
				<div class="alert alert-<?php echo $message; ?> alert-dismissible fade show alert-warning" role="alert">
					<?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php }  
			if(!empty($wrongcontact)){?>
			<div class="alert alert-<?php echo $wrongcontact; ?> alert-dismissible fade show alert-warning" role="alert">
				<?php echo htmlspecialchars($wrongcontact, ENT_QUOTES, 'UTF-8'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
			<?php } ?>
			<h1 class="text-center mb-3">Government Monitoring System</h1>
			<hr>
			<h2 class="text-center mb-4">Register</h2>
			<form method="post">
				<div class="row">
					<div class="col">
						<div class="mb-3">
							<label for="firstname" class="form-label">First Name</label>
							<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $firstname ?>" placeholder="eg. Marie" required>
						</div>
					</div>
					<div class="col">
						<div class="mb-3">
							<label for="lastname" class="form-label">Last Name</label>
							<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $lastname ?>" placeholder="e.g. Santos" required>
						</div>
					</div>
				</div>
				<div>
					<div class="mb-3">
						<label for="address" class="form-label">Address</label>
						<input type="text" class="form-control" id="address" name="address" placeholder="e.g. Camp 7 Washingan" value="<?php echo $address ?>" required>
					</div>
				</div>
				<div>
					<div class="mb-3">
						<label for="contact" class="form-label">Contact No.</label>
						<input type="text" class="form-control" id="contact" name="contact" value="<?php echo $contact ?>" placeholder="e.g. 09776655520"  required>
					</div>
				</div>
				<div>
					<div class="mb-3">
						<label for="username" class="form-label">Username</label>
						<input type="text" class="form-control" id="username" name="username" value="<?php echo $username ?>" placeholder="e.g. marieS" required>
					</div>
				</div>
				<div>
					<div class="mb-3">
						<label for="password" class="form-label">Password</label>
						<input type="password" class="form-control" id="password" name="password" required>
					</div>
				</div>

				<button type="submit" name="register" class="btn btn-primary">Register</button>
				<a href="index.php" class="btn btn-warning">Cancel</a>
			</form>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>