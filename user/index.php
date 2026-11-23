<?php
session_start();
require_once "../dbconn.php";

$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!isset($_SESSION['ID']) || $account['status'] != 'user') {
	session_destroy();
	header("location:../index.php");
	exit();
}

$sql = "SELECT * FROM complaints WHERE acc_id = ? ORDER BY ID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['ID']]);
$complaints = $stmt->fetchAll();

$sql = "SELECT complaints.*, feedback.ID AS feedback_id
		FROM complaints
		LEFT JOIN feedback ON feedback.complaint_id = complaints.ID
		WHERE complaints.acc_id = ?
		ORDER BY complaints.ID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['ID']]);
$complaints = $stmt->fetchAll();

$total = count($complaints);
$pending = 0;
$progress = 0;
$actiontaken = 0;
$resolved = 0;
$rejected = 0;

foreach ($complaints as $c) {
	if ($c['status'] == "Pending") {
		$pending++;
	}
	if ($c['status'] == "Under Investigation") {
		$progress++;
	}
	if ($c['status'] == "Action Taken") {
		$actiontaken++;
	}
	if ($c['status'] == "Resolved") {
		$resolved++;
	}
	if ($c['status'] == "Rejected") {
		$rejected++;
	}
}

$badgeColors = [
	'Service Advisory' => 'bg-danger',
	'Public Announcement' => 'bg-primary',
	'Community Update' => 'bg-success'
];

$prep = $pdo->prepare("SELECT * FROM announcements ORDER BY ID DESC LIMIT 3");
$prep->execute();
$announcements = $prep->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Barangay Camp 7: Service Monitoring System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>

<body>
	
	<h1>Barangay Camp 7: Service Monitoring System</h1>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
            <div class="container-fluid d-flex justify-content-start gap-4">
                <a href="index.php" class="nav-item nav-link">My Complaints</a>
                <a href="announcements.php" class="nav-item nav-link">Announcements</a>
				<a href="acc.php" class="nav-item nav-link">Account</a>
                <a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
            </div>
        </nav>
	<hr>

	<div class="container-fluid mb-3">
		<h2 class="mb-3">My Dashboard</h2>
		<div class="row">
			<div class="col">
				<div class="card shadow-sm rounded p-3 bg-primary h-100">
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
	</div>
	<hr>

	<h2>Public Announcement</h2>
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
	<?php if (count($announcements) > 0): ?>
		<p><a href="announcements.php">View all announcements</a></p>
	<?php endif; ?>
	<hr>

	<h2>My Complaints</h2>

	<?php if (isset($_SESSION['success'])): ?>
		<p style="color: green;"><strong><?php echo htmlspecialchars($_SESSION['success']); ?></strong></p>
		<?php unset($_SESSION['success']); ?>
	<?php endif; ?>

	<p><a href="choose_category.php"><button type="button">+ Add Complaint</button></a></p>

	<table border="1">
		<tr>
			<th>Reference No.</th>
			<th>Department</th>
			<th>Category</th>
			<th>Description</th>
			<th>Address</th>
			<th>Status</th>
			<th>Date</th>
			<th>Photo</th>
			<th colspan="2">Actions</th>
			<th colspan="2">FeedBack</th>
		</tr>

		<?php if (count($complaints) == 0) { ?>
			<tr>
				<td colspan="10">No complaints yet.</td>
			</tr>
		<?php } ?>

		<?php foreach ($complaints as $row) { ?>
			<tr>
				<td><?php echo htmlspecialchars($row['reference_no']); ?></td>
				<td><?php echo htmlspecialchars($row['department']); ?></td>
				<td><?php echo htmlspecialchars($row['category']); ?></td>
				<td><?php echo htmlspecialchars($row['description']); ?></td>
				<td>
					<?php echo htmlspecialchars($row['street'] . ', ' . $row['barangay']); ?>
					<?php if ($row['landmark'] != ''): ?>
						<br>Near <?php echo htmlspecialchars($row['landmark']); ?>
					<?php endif; ?>
				</td>
				<td><?php echo htmlspecialchars($row['status']); ?></td>
				<td><?php echo date("M d, Y", strtotime($row['date_submitted'])); ?></td>
				<td>
					<?php if ($row['photo_path'] != ''): ?>
						<img src="<?php echo htmlspecialchars($row['photo_path']); ?>" width="100">
					<?php else: ?>
						No photo
					<?php endif; ?>
				</td>
				<td>
					<?php if ($row['status'] == 'Pending'): ?>
						<form method="post" action="edit_complain.php">
							<input type="hidden" name="edit_id" value="<?php echo $row['ID']; ?>">
							<button type="submit">Edit</button>
						</form>
					<?php else: ?>
						<button type="button" disabled>Edit</button>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($row['status'] == 'Pending'): ?>
						<a href="delete_complain.php?id=<?php echo $row['ID']; ?>"
							onclick="return confirm('Are you sure you want to delete this complaint? This cannot be undone.');">Delete</a>
					<?php else: ?>
						<button type="button" disabled>Delete</button>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($row['status'] == 'Pending' || $row['status'] == 'Under Investigation' || $row['status'] == 'Action Taken') { ?>
						<span style="color:gray;">Please wait for further updates</span>
					<?php } else if ($row['feedback_id']) { ?>
						Feedback sent
					<?php } else { ?>
						<a href="user_feedback.php?id=<?php echo $row['ID']; ?>">Give Feedback</a>
					<?php } ?>
					<?php ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</body>

</html>