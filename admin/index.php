<?php
session_start();
require_once "../dbconn.php";

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!isset($_SESSION['ID']) || $account['status'] != 'admin') {
	session_destroy();
	header("location:../index.php");
	exit();
}

// all complaints (for the counters)
$prep = $pdo->prepare("SELECT * FROM complaints");
$prep->execute();
$row = $prep->fetchAll();

$total = count($row);
$pending = 0;
$progress = 0;
$actiontaken = 0;
$resolved = 0;
$rejected = 0;

foreach ($row as $r) {
	if ($r['status'] == "Pending") {
		$pending++;
	}
	if ($r['status'] == "Under Investigation") {
		$progress++;
	}
	if ($r['status'] == "Action Taken") {
		$actiontaken++;
	}
	if ($r['status'] == "Resolved") {
		$resolved++;
	}
	if ($r['status'] == "Rejected") {
	$rejected++;
	}
}

// complaints per department
$prep = $pdo->prepare("SELECT department, COUNT(*) AS total FROM complaints GROUP BY department ORDER BY total DESC");
$prep->execute();
$departments = $prep->fetchAll();

// latest 10 complaints
$prep = $pdo->prepare("SELECT complaints.*, accounts.username
		FROM complaints
		JOIN accounts ON complaints.acc_id = accounts.ID
		ORDER BY complaints.ID DESC LIMIT 10");
$prep->execute();
$latest = $prep->fetchAll();

// feedback: average scores
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
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>

<body>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage</a>
			<a href="monitoring.php" class="nav-item nav-link">Monitoring</a>
			<a href="admin_feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>
	<div class="container-fluid p-4">
		<h1 class="mb-2">Government Monitoring System</h1>
		<hr>
		<h2 class="mb-4">Dashboard</h2>
		<div class="row mb-2">
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-primary">
					<div class="card-body">
						<h6 class="text-white">Total Complaints</h6>
						<h2 class="text-white"><?php echo $total; ?></h2>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-warning h-100">
					<div class="card-body">
						<h6>Pending</h6>
						<h2><?php echo $pending; ?></h2>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-info h-100">
					<div class="card-body">
						<h6>Under Investigation</h6>
						<h2><?php echo $progress; ?></h2>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-secondary h-100">
					<div class="card-body">
						<h6 class="text-white">Action Taken</h6>
						<h2 class="text-white"><?php echo $actiontaken; ?></h2>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-success h-100">
					<div class="card-body">
						<h6 class="text-white">Resolved</h6>
						<h2 class="text-white"><?php echo $resolved; ?></h2>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-danger h-100">
					<div class="card-body">
						<h6 class="text-white">Rejected</h6>
						<h2 class="text-white"><?php echo $rejected; ?></h2>
					</div>
				</div>
			</div>
		</div>

		<h3 class="mb-4">Complaints by Department</h3>
		<div class="col-md-6 ms-3">
			<table class="table table-striped table-hover table-bordered text-center">
				<tr>
					<th>Department</th>
					<th>Complaints</th>
				</tr>
				<?php if (count($departments) == 0): ?>
					<tr>
						<td colspan="2">No complaints yet.</td>
					</tr>
				<?php endif; ?>
				<?php foreach ($departments as $d): ?>
					<tr>
						<td><?php echo $d['department']; ?></td>
						<td><?php echo $d['total']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

		<h3 class="mb-4">Citizen Feedback</h3>
		<a href="admin_feedback.php" class="btn btn-primary mb-2">Check Feedback</a>
		<div class="col-md-8">
			<table class="table table-striped table-bordered">
				<tr>
					<th>Total Feedback</th>
					<th>Overall (out of 5)</th>
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
	</div>




	<h3>Latest Complaints</h3>
	<p><a href="manage_complaints.php"><button type="button">Manage Complaints</button></a></p>
	<table border="1">
		<tr>
			<th>Reference No.</th>
			<th>Sent By</th>
			<th>Department</th>
			<th>Category</th>
			<th>Barangay</th>
			<th>Status</th>
			<th>Date</th>
			<th>Photo</th>
		</tr>
		<?php if (count($latest) == 0): ?>
			<tr>
				<td colspan="8">No complaints yet.</td>
			</tr>
		<?php endif; ?>
		<?php foreach ($latest as $l): ?>
			<tr>
				<td><?php echo $l['reference_no']; ?></td>
				<td><?php echo htmlspecialchars($l['username']); ?></td>
				<td><?php echo $l['department']; ?></td>
				<td><?php echo $l['category']; ?></td>
				<td><?php echo htmlspecialchars($l['barangay']); ?></td>
				<td><?php echo $l['status']; ?></td>
				<td><?php echo date("M d, Y", strtotime($l['date_submitted'])); ?></td>
				<td>
					<?php if ($l['photo_path'] != ''): ?>
						<img src="../user/<?php echo $l['photo_path']; ?>" width="100">
					<?php else: ?>
						No photo
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>