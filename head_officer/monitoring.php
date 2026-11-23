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

// turns a number of hours into text like "1d 4h" or "35m"
function format_hours($hours)
{
	if ($hours === null) {
		return '-';
	}
	$hours = (float)$hours;
	$minutes = round($hours * 60);
	if ($minutes < 60) {
		return $minutes . "m";
	}
	$days = floor($minutes / 1440);
	$hrs = floor(($minutes % 1440) / 60);
	if ($days > 0) {
		return $days . "d " . $hrs . "h";
	}
	return $hrs . "h " . ($minutes % 60) . "m";
}

// Task 6.2: average response time and resolution time
$prep = $pdo->prepare("SELECT
		AVG(TIMESTAMPDIFF(MINUTE, date_submitted, date_responded)) / 60 AS avg_response,
		AVG(TIMESTAMPDIFF(MINUTE, date_submitted, date_resolved)) / 60 AS avg_resolution
	FROM complaints");
$prep->execute();
$times = $prep->fetch();

// Task 6.3: department performance (one row per department)
$prep = $pdo->prepare("SELECT c.department,
		COUNT(DISTINCT c.ID) AS total,
		COUNT(DISTINCT CASE WHEN c.status = 'Resolved' THEN c.ID END) AS resolved,
		AVG(TIMESTAMPDIFF(MINUTE, c.date_submitted, c.date_responded)) / 60 AS avg_response,
		AVG(f.satisfaction_rating) AS avg_feedback
	FROM complaints c
	LEFT JOIN feedback f ON f.complaint_id = c.ID
	GROUP BY c.department
	ORDER BY c.department");
$prep->execute();
$departments = $prep->fetchAll();

// Task 6.4: unresolved complaints (Pending / In Progress), oldest first
$prep = $pdo->prepare("SELECT complaints.*, accounts.username
	FROM complaints
	JOIN accounts ON complaints.acc_id = accounts.ID
	WHERE complaints.status IN ('Pending', 'In Progress')
	ORDER BY complaints.date_submitted ASC, complaints.ID ASC");
$prep->execute();
$unresolved = $prep->fetchAll();

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Monitoring</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>
<body>
	<div class="container-fluid p-4">
	<h1 class="mb-2">Barangay Camp 7: Service Monitoring System</h1>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage</a>
			<a href="monitoring.php" class="nav-item nav-link">Monitoring</a>
			<a href="admin_feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="acc.php" class="nav-item nav-link">Account</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>
	<hr>

	<h2 class="mb-2">Monitoring</h2>

	<h3 class="mb-2">Response and Resolution Time</h3>
	<div class="col-md-6 mb-4">
		<table class="table table-striped table-hover table-bordered text-center">
			<tr>
				<th>Average Response Time</th>
				<th>Average Resolution Time</th>
			</tr>
			<tr>
				<td><?php echo format_hours($times['avg_response']); ?></td>
				<td><?php echo format_hours($times['avg_resolution']); ?></td>
			</tr>
		</table>
	</div>

	<h3 class="mb-2">Department Performance</h3>
	<div class="col-md-8 mb-4">
		<table class="table table-striped table-hover table-bordered text-center">
			<tr>
				<th>Department</th>
				<th>Complaints</th>
				<th>Resolved</th>
				<th>Avg. Response Time</th>
				<th>Avg. Feedback (out of 5)</th>
			</tr>
			<?php if (count($departments) == 0): ?>
				<tr>
					<td colspan="5">No complaints yet.</td>
				</tr>
			<?php endif; ?>
			<?php foreach ($departments as $d): ?>
				<tr>
					<td><?php echo htmlspecialchars($d['department']); ?></td>
					<td><?php echo $d['total']; ?></td>
					<td><?php echo $d['resolved']; ?></td>
					<td><?php echo format_hours($d['avg_response']); ?></td>
					<td><?php echo $d['avg_feedback'] !== null ? round($d['avg_feedback'], 1) : '-'; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

	<h3 class="mb-2">Needs Attention</h3>
	<p>Pending and In Progress complaints, oldest first. Overdue ones are marked.</p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-bordered align-middle text-center">
		<tr>
			<th>Reference No.</th>
			<th>Sent By</th>
			<th>Department</th>
			<th>Category</th>
			<th>Status</th>
			<th>Submitted</th>
			<th>Deadline</th>
			<th>Actions</th>
		</tr>
		<?php if (count($unresolved) == 0): ?>
			<tr>
				<td colspan="8">Nothing needs attention.</td>
			</tr>
		<?php endif; ?>
		<?php foreach ($unresolved as $u): ?>
			<?php $overdue = (!empty($u['deadline']) && $u['deadline'] < $today); ?>
			<tr>
				<td><?php echo htmlspecialchars($u['reference_no']); ?></td>
				<td><?php echo htmlspecialchars($u['username']); ?></td>
				<td><?php echo htmlspecialchars($u['department']); ?></td>
				<td><?php echo htmlspecialchars($u['category']); ?></td>
				<td><?php echo htmlspecialchars($u['status']); ?></td>
				<td><?php echo date("M d, Y", strtotime($u['date_submitted'])); ?></td>
				<td>
					<?php if (!empty($u['deadline'])): ?>
						<?php if ($overdue): ?>
							<mark><?php echo date("M d, Y", strtotime($u['deadline'])); ?> (OVERDUE)</mark>
						<?php else: ?>
							<?php echo date("M d, Y", strtotime($u['deadline'])); ?>
						<?php endif; ?>
					<?php else: ?>
						-
					<?php endif; ?>
				</td>
				<td>
					<form method="post" action="manage_edit_complaints.php">
						<input type="hidden" name="manage_id" value="<?php echo $u['ID']; ?>">
						<button type="submit">Manage</button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>
</html>