<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!$account || $account['status'] != 'admin') {
	session_destroy();
	header("location:../index.php");
	exit();
}


$prep = $pdo->prepare("SELECT COUNT(*) AS total,
		ROUND(AVG(satisfaction_rating), 1) AS overall,
		ROUND(AVG(timeliness), 1) AS timeliness,
		ROUND(AVG(professionalism), 1) AS professionalism,
		ROUND(AVG(communication), 1) AS communication,
		SUM(resolution_status = 'yes') AS resolved_yes,
		SUM(resolution_status = 'no') AS resolved_no
	FROM feedback");
$prep->execute();
$summary = $prep->fetch();


$prep = $pdo->prepare("SELECT feedback.*, complaints.reference_no, complaints.department,
		complaints.category, accounts.username
	FROM feedback
	LEFT JOIN complaints ON feedback.complaint_id = complaints.ID
	LEFT JOIN accounts ON feedback.acc_id = accounts.ID
	ORDER BY feedback.ID DESC");
$prep->execute();
$feedbacks = $prep->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Citizen Feedback</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>
<body>
	<div class="container-fluid p-4">
	<h1 class="mb-2">Government Monitoring System (Admin)</h1>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage Complaints</a>
			<a href="admin_feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="reports.php" class="nav-item nav-link">Reports</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>
	<hr>

	<h2 class="mb-4">Citizen Feedback</h2>

	<h3 class="mb-3">Average Scores (out of 5)</h3>
	<div class="table-responsive mb-4">
		<table class="table table-striped table-hover table-bordered text-center">
			<tr>
				<th>Total Feedback</th>
				<th>Overall</th>
				<th>Timeliness</th>
				<th>Professionalism</th>
				<th>Communication</th>
				<th>Resolved: Yes</th>
				<th>Resolved: No</th>
			</tr>
			<tr>
				<td><?php echo $summary['total']; ?></td>
				<td><?php echo $summary['overall'] ?? '-'; ?></td>
				<td><?php echo $summary['timeliness'] ?? '-'; ?></td>
				<td><?php echo $summary['professionalism'] ?? '-'; ?></td>
				<td><?php echo $summary['communication'] ?? '-'; ?></td>
				<td><?php echo $summary['resolved_yes'] ?? 0; ?></td>
				<td><?php echo $summary['resolved_no'] ?? 0; ?></td>
			</tr>
		</table>
	</div>

	<h3 class="mb-3">All Feedback</h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-bordered align-middle text-center">
			<tr>
				<th>Reference No.</th>
				<th>Sent By</th>
				<th>Department</th>
				<th>Category</th>
				<th>Overall</th>
				<th>Timeliness</th>
				<th>Professionalism</th>
				<th>Communication</th>
				<th>Resolved?</th>
				<th>Comments</th>
				<th>Date</th>
			</tr>
			<?php if (count($feedbacks) == 0): ?>
				<tr>
					<td colspan="11">No feedback yet.</td>
				</tr>
			<?php endif; ?>
			<?php foreach ($feedbacks as $f): ?>
				<tr>
					<td><?php echo htmlspecialchars($f['reference_no']); ?></td>
					<td><?php echo htmlspecialchars($f['username']); ?></td>
					<td><?php echo htmlspecialchars($f['department']); ?></td>
					<td><?php echo htmlspecialchars($f['category']); ?></td>
					<td><?php echo $f['satisfaction_rating']; ?>/5</td>
					<td><?php echo $f['timeliness']; ?>/5</td>
					<td><?php echo $f['professionalism']; ?>/5</td>
					<td><?php echo $f['communication']; ?>/5</td>
					<td><?php echo ucfirst($f['resolution_status']); ?></td>
					<td><?php echo htmlspecialchars($f['comments']); ?></td>
					<td><?php echo date("M d, Y", strtotime($f['date_submitted'])); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
	</div>
</body>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</html>
