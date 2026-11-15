<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
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
$resolved = 0;
$rejected = 0;

foreach($row as $r){
	if($r['status'] == "Pending"){
		$pending++;
	}
	if($r['status'] == "In Progress"){
		$progress++;
	}
	if($r['status'] == "Resolved"){
		$resolved++;
	}
	if($r['status'] == "Rejected"){
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
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
</head>
<body>
	<h1>Government Monitoring System (Admin)</h1>
	<nav>
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>Dashboard</h2>

	<table border="1">
		<tr>
			<th>Total</th>
			<th>Pending</th>
			<th>In Progress</th>
			<th>Resolved</th>
			<th>Rejected</th>
		</tr>
		<tr>
			<td><?php echo $total; ?></td>
			<td><?php echo $pending; ?></td>
			<td><?php echo $progress; ?></td>
			<td><?php echo $resolved; ?></td>
			<td><?php echo $rejected; ?></td>
		</tr>
	</table>

	<h3>Complaints by Department</h3>
	<table border="1">
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
</body>
</html>