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

	$badge = [
		'Pending' => 'bg-warning text-white',
		'Under Investigation' => 'bg-info text-white',
		'Action Taken' => 'bg-primary text-white',
		'Resolved' => 'bg-success text-white',
		'Rejected' => 'bg-danger text-white'
	];	


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
		ORDER BY FIELD(complaints.status, 'Pending', 'Under Investigation', 'Action Taken', 'Resolved', 'Rejected'), complaints.ID ASC");
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
		<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">

		<style>
		html { font-size: 112.5%; }
		</style>
	<link rel="stylesheet" href="../assets/css/barangay.css">
</head>
<body>




	<div class="container-fluid p-4">
		<h1 class="mb-2">Barangay Camp 7: Service Monitoring System</h1>
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
		<hr>

	<h2 class="mb-4">Manage Complaints</h2>

	<?php if ($department == ""): ?>

		<p class="fs-5">Pick a department:</p>
		<div class="col-md-8">
		<table class="table table-striped table-hover table-bordered align-middle text-center">
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
					<td><a href="manage_complaints.php?department=<?php echo urlencode($d['department']); ?>" class="fw-bold"><?php echo htmlspecialchars($d['department']); ?></a></td>
					<td><?php echo $total; ?></td>
					<td><?php echo $pending; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>

	<?php else: ?>

		<h3><?php echo htmlspecialchars($department); ?></h3>
		<p><a href="manage_complaints.php" class="btn btn-warning btn-lg mb-3">Back to departments</a></p>

		<div class="table-responsive">
		<table class="table table-striped table-hover table-bordered align-middle">
			<tr>
				<th>Reference No.</th>
				<th>Sent By</th>
				<th>Category</th>
				<th>Description</th>
				<th>Address</th>
				<th>Status</th>
				<th>Priority</th>
				<th>Deadline</th>
				<th>Date</th>
				<th>Photo</th>
				<th>Actions</th>
			</tr>
			<?php if (count($complaints) == 0): ?>
				<tr>
					<td colspan="11">No complaints for this department.</td>
				</tr>
			<?php endif; ?>
			<?php foreach ($complaints as $c): ?>
				<tr>
					<td><?php echo htmlspecialchars($c['reference_no']); ?></td>
					<td><?php echo htmlspecialchars($c['username']); ?></td>
					<td><?php echo htmlspecialchars($c['category']); ?></td>
					<td><?php echo htmlspecialchars($c['description']); ?></td>
					<td>
						<?php echo htmlspecialchars($c['street'] . ', ' . $c['barangay']); ?>
						<?php if ($c['landmark'] != ''): ?>
							<br>Near <?php echo htmlspecialchars($c['landmark']); ?>
						<?php endif; ?>
					</td>

					<td>
						<span class="badge fs-6 <?php echo $badge[$c['status']] ?? 'bg-secondary'; ?>">
								<?php echo htmlspecialchars($c['status']); ?>
						</span>
					</td>
					<td>
						<?php if ($c['priority'] == ''): ?>
							Not set
						<?php else: ?>
							<?php echo htmlspecialchars($c['priority']); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($c['deadline'] == ''): ?>
							Not set
						<?php else: ?>
							<?php echo date("M d, Y", strtotime($c['deadline'])); ?>
							<?php if ($c['deadline'] < date("Y-m-d") && $c['status'] != 'Resolved' && $c['status'] != 'Rejected'): ?>

								<br><span class="badge bg-danger fs-6">Overdue</span>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td><?php echo date("M d, Y", strtotime($c['date_submitted'])); ?></td>
					<td>
						<?php if ($c['photo_path'] != ''): ?>
							<img src="../user/<?php echo htmlspecialchars($c['photo_path']); ?>" width="100" class="img-thumbnail">
						<?php else: ?>
							No photo
						<?php endif; ?>
					</td>
					<td>
						<form method="post" action="manage_edit_complaints.php">
							<input type="hidden" name="manage_id" value="<?php echo $c['ID']; ?>">

							<button type="submit" class="btn btn-primary btn-lg">Manage</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

	<?php endif; ?>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
	</div>
	</div>
</body>
</html>