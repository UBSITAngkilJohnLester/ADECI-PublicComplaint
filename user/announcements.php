<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

$badgeColors = [
	'Service Advisory' => 'bg-danger',
	'Public Announcement' => 'bg-primary',
	'Community Update' => 'bg-success'
];

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
			<span class="badge <?php echo $badgeColors[$a['type']] ?? 'bg-secondary'; ?>"><?php echo htmlspecialchars($a['type']); ?></span>
			<h3 style="margin:0;"><?php echo htmlspecialchars($a['title']); ?></h3>
			<small><?php echo date("M d, Y h:i A", strtotime($a['date_posted'])); ?></small>
			<p><?php echo nl2br(htmlspecialchars($a['message'])); ?></p>
		</div>
	<?php endforeach; ?>
</body>
</html>