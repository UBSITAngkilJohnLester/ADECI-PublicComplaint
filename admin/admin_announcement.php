<?php
session_start();
require_once "../dbconn.php";


if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}


$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();
if (!$account || $account['status'] != 'admin') {
	header("location:../index.php");
	exit();
}

$error = "";


if (isset($_POST['post'])) {
	$title = trim($_POST['title']);
	$message = trim($_POST['message']);

	if ($title == "" || $message == "") {
		$error = "Please fill in both the title and the message.";
	} elseif (strlen($title) > 150) {
		$error = "Title is too long (150 characters max).";
	} else {
		$insert = $pdo->prepare("INSERT INTO announcements (title, message, acc_id) VALUES (:title, :message, :acc_id)");
		$insert->execute([
			':title' => $title,
			':message' => $message,
			':acc_id' => $_SESSION['ID']
		]);

		header("location:admin_announcement.php");
		exit();
	}
}

// Delete an announcement.
if (isset($_POST['delete'])) {
	$delete = $pdo->prepare("DELETE FROM announcements WHERE ID = ?");
	$delete->execute([$_POST['announcement_id']]);

	header("location:admin_announcement.php");
	exit();
}

// All announcements, newest first.
$prep = $pdo->prepare("SELECT * FROM announcements ORDER BY ID DESC");
$prep->execute();
$announcements = $prep->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Announcements</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>

<body>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>

	<div class="container-fluid p-4">
		<h1 class="mb-2">Government Monitoring System</h1>
		<hr>
		<h2 class="mb-4">Announcements</h2>

		<div class="card shadow-sm p-3 mb-4">
			<h5>Write an Announcement</h5>

			<?php if ($error != ''): ?>
				<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>

			<form method="post">
				<div class="mb-3">
					<label for="title" class="form-label">Title</label>
					<input type="text" class="form-control" id="title" name="title" maxlength="150" required>
				</div>
				<div class="mb-3">
					<label for="message" class="form-label">Message</label>
					<textarea class="form-control" id="message" name="message" rows="4" required></textarea>
				</div>
				<button type="submit" name="post" class="btn btn-primary">Post Announcement</button>
			</form>
		</div>

		<h5>Posted Announcements</h5>
		<?php if (count($announcements) == 0): ?>
			<p>No announcements yet.</p>
		<?php endif; ?>

		<?php foreach ($announcements as $a): ?>
			<div class="card shadow-sm p-3 mb-3">
				<div class="d-flex justify-content-between align-items-start">
					<div>
						<h5 class="mb-1"><?php echo htmlspecialchars($a['title']); ?></h5>
						<small class="text-muted"><?php echo date("M d, Y h:i A", strtotime($a['date_posted'])); ?></small>
					</div>
					<form method="post" onsubmit="return confirm('Delete this announcement?');">
						<input type="hidden" name="announcement_id" value="<?php echo $a['ID']; ?>">
						<button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
					</form>
				</div>
				<p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($a['message'])); ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>