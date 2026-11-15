<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

// which department did the admin pick? (empty = none yet)
$department = "";
if (isset($_GET['department'])) {
	$department = $_GET['department'];
}

if ($department == "") {
	// step 1: list of departments + all complaints (for the counts)
	$prep = $pdo->prepare("SELECT DISTINCT department FROM categories");
	$prep->execute();
	$departments = $prep->fetchAll();

	$prep = $pdo->prepare("SELECT department, status FROM complaints");
	$prep->execute();
	$all = $prep->fetchAll();
} else {
	// step 2: complaints of the picked department, Pending first, oldest first
	$prep = $pdo->prepare("SELECT complaints.*, accounts.username
		FROM complaints
		JOIN accounts ON complaints.acc_id = accounts.ID
		WHERE complaints.department = :department
		ORDER BY FIELD(complaints.status, 'Pending', 'In Progress', 'Resolved', 'Rejected'), complaints.ID ASC");
	$prep->execute([":department" => $department]);
	$complaints = $prep->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Manage Complaints</title>
</head>
<body>
	<h1>Government Monitoring System (Admin)</h1>
	<nav>
		<a href="index.php">Dashboard</a>
		<a href="manage_complaints.php">Manage Complaints</a>
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>Manage Complaints</h2>

	<?php if ($department == ""): ?>

		<p>Pick a department:</p>
		<table border="1">
			<tr>
				<th>Department</th>
				<th>Total</th>
				<th>Pending</th>
			</tr>
			<?php foreach ($departments as $d): ?>
				<?php
				$total = 0;
				$pending = 0;
				foreach ($all as $a) {
					if ($a['department'] == $d['department']) {
						$total++;
						if ($a['status'] == "Pending") {
							$pending++;
						}
					}
				}
				?>
				<tr>
					<td><a href="manage_complaints.php?department=<?php echo urlencode($d['department']); ?>"><?php echo $d['department']; ?></a></td>
					<td><?php echo $total; ?></td>
					<td><?php echo $pending; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>

	<?php else: ?>

		<h3><?php echo htmlspecialchars($department); ?></h3>
		<p><a href="manage_complaints.php">Back to departments</a></p>

		<table border="1">
			<tr>
				<th>Reference No.</th>
				<th>Sent By</th>
				<th>Category</th>
				<th>Description</th>
				<th>Address</th>
				<th>Status</th>
				<th>Date</th>
				<th>Photo</th>
				<th>Actions</th>
			</tr>
			<?php if (count($complaints) == 0): ?>
				<tr>
					<td colspan="9">No complaints for this department.</td>
				</tr>
			<?php endif; ?>
			<?php foreach ($complaints as $c): ?>
				<tr>
					<td><?php echo $c['reference_no']; ?></td>
					<td><?php echo htmlspecialchars($c['username']); ?></td>
					<td><?php echo $c['category']; ?></td>
					<td><?php echo htmlspecialchars($c['description']); ?></td>
					<td>
						<?php echo htmlspecialchars($c['street'] . ', ' . $c['barangay']); ?>
						<?php if ($c['landmark'] != ''): ?>
							<br>Near <?php echo htmlspecialchars($c['landmark']); ?>
						<?php endif; ?>
					</td>
					<td><?php echo $c['status']; ?></td>
					<td><?php echo date("M d, Y", strtotime($c['date_submitted'])); ?></td>
					<td>
						<?php if ($c['photo_path'] != ''): ?>
							<img src="../user/<?php echo $c['photo_path']; ?>" width="100">
						<?php else: ?>
							No photo
						<?php endif; ?>
					</td>
					<td><a href="manage_complaint.php?id=<?php echo $c['ID']; ?>">Manage</a></td>
				</tr>
			<?php endforeach; ?>
		</table>

	<?php endif; ?>
</body>
</html>