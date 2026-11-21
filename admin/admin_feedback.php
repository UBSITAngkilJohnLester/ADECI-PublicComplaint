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
</head>
<body>
	<h1>Government Monitoring System (Admin)</h1>
	<nav>
		<a href="index.php">Dashboard</a> |
		<a href="manage_complaints.php">Manage Complaints</a> |
		<a href="admin_feedback.php">Feedback</a> |
		<a href="reports.php">Reports</a> |
		<a href="admin_announcement.php">Announcement</a> |
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>Citizen Feedback</h2>

	<h3>Average Scores (out of 5)</h3>
	<table border="1">
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

	<h3>All Feedback</h3>
	<table border="1">
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
</body>
</html>
