<?php
session_start();
require_once "../dbconn.php";

// Make sure the user is logged in.
if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
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
</head>
<body>
	<h1>Government Monitoring System (User)</h1>
	<nav>
		<a href="index.php">My Complaints</a> |
		<a href="announcements.php">Announcements</a> |
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>Announcements</h2>

	<?php if (count($announcements) == 0): ?>
		<p>No announcements yet.</p>
	<?php endif; ?>

	<?php foreach ($announcements as $a): ?>
		<div style="border:1px solid #999; padding:10px; margin-bottom:10px;">
			<h3 style="margin:0;"><?php echo htmlspecialchars($a['title']); ?></h3>
			<small><?php echo date("M d, Y h:i A", strtotime($a['date_posted'])); ?></small>
			<p><?php echo nl2br(htmlspecialchars($a['message'])); ?></p>
		</div>
	<?php endforeach; ?>
</body>
</html>