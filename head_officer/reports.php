<?php
session_start();
require_once "../dbconn.php";

$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

// check user is logged in and with the right credentials
if (!isset($_SESSION['ID']) || $account['status'] != 'barangay_captain') {
	session_destroy();
	header("location:../index.php");
	exit();
}

$responseColumn = "date_responded";

$check = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS
	WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'complaints' AND COLUMN_NAME = ?");
$check->execute([$responseColumn]);
$hasResponse = $check->fetch() !== false;
$prep = $pdo->prepare("SELECT d.department,
		COUNT(c.ID) AS total,
		COALESCE(SUM(c.status = 'Resolved'), 0) AS resolved,
		ROUND(100 * SUM(c.status = 'Resolved') / NULLIF(COUNT(c.ID), 0), 1) AS rate
	FROM (SELECT DISTINCT department FROM categories) d
	LEFT JOIN complaints c ON c.department = d.department
	GROUP BY d.department
	ORDER BY d.department");
$prep->execute();
$rates = $prep->fetchAll();

$overall = null;
$responses = [];
if ($hasResponse) {
	$prep = $pdo->prepare("SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, date_submitted, `$responseColumn`)) / 60, 1) AS hrs
		FROM complaints WHERE `$responseColumn` IS NOT NULL");
	$prep->execute();
	$overall = $prep->fetch()['hrs'];

	$prep = $pdo->prepare("SELECT d.department,
			COUNT(c.`$responseColumn`) AS answered,
			ROUND(AVG(TIMESTAMPDIFF(MINUTE, c.date_submitted, c.`$responseColumn`)) / 60, 1) AS hrs
		FROM (SELECT DISTINCT department FROM categories) d
		LEFT JOIN complaints c ON c.department = d.department
		GROUP BY d.department
		ORDER BY d.department");
	$prep->execute();
	$responses = $prep->fetchAll();
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reports and Analytics</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>

<body>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage</a>
			<a href="admin_feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="reports.php" class="nav-item nav-link">Reports</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="acc.php" class="nav-item nav-link">Account</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>

	<div class="container-fluid p-4">
		<h1 class="mb-2">Barangay Camp 7: Service Monitoring System</h1>
		<hr>
		<h2 class="mb-4">Reports and Analytics</h2>

		<a href="reports_categories.php" class="btn btn-primary mb-4">View Complaints by Category</a>

		<h3 class="mb-3">Resolution Rate by Department</h3>
		<div class="col-md-8 mb-4">
			<table class="table table-striped table-hover table-bordered text-center">
				<tr>
					<th>Department</th>
					<th>Total Complaints</th>
					<th>Resolved</th>
					<th>Resolution Rate</th>
				</tr>
				<?php if (count($rates) == 0): ?>
					<tr>
						<td colspan="4">No departments yet.</td>
					</tr>
				<?php endif; ?>
				<?php foreach ($rates as $r): ?>
					<tr>
						<td><?php echo htmlspecialchars($r['department']); ?></td>
						<td><?php echo $r['total']; ?></td>
						<td><?php echo $r['resolved']; ?></td>
						<td><?php echo $r['rate'] === null ? '-' : $r['rate'] . '%'; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

		<h3 class="mb-3">Average Response Time</h3>
		<?php if (!$hasResponse): ?>
			<div class="alert alert-warning col-md-8">
				Not available yet. This needs Task 6.1 (the complaints column that records when staff first respond).
				Once it is added, set <code>$responseColumn</code> at the top of this file to its name.
			</div>
		<?php else: ?>
			<p><strong>Overall:</strong> <?php echo $overall === null ? 'No responses yet' : $overall . ' hours'; ?></p>
			<div class="col-md-8">
				<table class="table table-striped table-hover table-bordered text-center">
					<tr>
						<th>Department</th>
						<th>Complaints Responded To</th>
						<th>Average Response Time</th>
					</tr>
					<?php foreach ($responses as $r): ?>
						<tr>
							<td><?php echo htmlspecialchars($r['department']); ?></td>
							<td><?php echo $r['answered']; ?></td>
							<td><?php echo $r['hrs'] === null ? '-' : $r['hrs'] . ' hours'; ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		<?php endif; ?>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>