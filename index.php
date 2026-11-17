<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Baguio City Hall: Service Monitoring System</title>
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
			<h1 class="text-center mb-3">Government Monitoring System</h1>
			<hr>
			<h2 class="text-center mb-4">Login</h2>
			<form method="post" action="login.php">
				<div class="mb-3">
					<label for="username" class="form-label">Username</label>
					<input type="text" class="form-control" id="username" name="username" required>
				</div>

				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" name="password" required>
				</div>

				<button type="submit" name="login" class="btn btn-primary w-100">Login</button>

				<div class="text-center mt-3">
					<a href="register.php" class="btn btn-warning w-100">Register</a>
				</div>
				</table>
			</form>
		</div>
	</div>



	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>

</body>

</html>