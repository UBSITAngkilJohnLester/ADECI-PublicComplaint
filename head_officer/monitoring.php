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

if (!$account || $account['status'] != 'head_officer') {
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
</head>
<body>
	<h1>Government Monitoring System (Admin)</h1>
	<nav>
		<a href="index.php">Dashboard</a> |
		<a href="manage_complaints.php">Manage Complaints</a> |
		<a href="monitoring.php">Monitoring</a> |
		<a href="admin_feedback.php">Feedback</a> |
		<a href="admin_announcement.php">Announcement</a> |
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>Monitoring</h2>

	<h3>Response and Resolution Time</h3>
	<table border="1">
		<tr>
			<th>Average Response Time</th>
			<th>Average Resolution Time</th>
		</tr>
		<tr>
			<td><?php echo format_hours($times['avg_response']); ?></td>
			<td><?php echo format_hours($times['avg_resolution']); ?></td>
		</tr>
	</table>

	<h3>Department Performance</h3>
	<table border="1">
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

	<h3>Needs Attention</h3>
	<p>Pending and In Progress complaints, oldest first. Overdue ones are marked.</p>
	<table border="1">
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
</body>
</html>