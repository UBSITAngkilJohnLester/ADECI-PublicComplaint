<?php
session_start();
require_once "../dbconn.php";
date_default_timezone_set("Asia/Manila");

if (!isset($_SESSION['ID'])) {
	session_destroy();
	header("location:../index.php");
	exit();
}

$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!$account || $account['status'] != 'secretary') {
	session_destroy();
	header("location:../index.php");
	exit();
}

$myDepartment = "Katarungang Pambarangay";

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$badge = [
	'Pending' => 'bg-warning text-white',
	'Under Investigation' => 'bg-info text-white',
	'Action Taken' => 'bg-primary text-white',
	'Resolved' => 'bg-success text-white',
	'Rejected' => 'bg-danger text-white'
];

if (isset($_POST['manage_id'])) {
	$_SESSION['manage_id'] = $_POST['manage_id'];
	header("location:manage_edit_complaints.php");
	exit();
}

if (!isset($_SESSION['manage_id'])) {
	header("location:index.php");
	exit();
}
$id = $_SESSION['manage_id'];

$select = $pdo->prepare("SELECT * FROM complaints WHERE ID = ?");
$select->execute([$id]);
$row = $select->fetch();

if (!$row) {
	header("location:index.php");
	exit();
}

if ($row['department'] != $myDepartment) {
	header("location:index.php");
	exit();
}

$error = "";

if (isset($_POST['update']) || isset($_POST['resolve'])) {

	$token = $_POST['csrf_token'] ?? '';
	if (!hash_equals($_SESSION['csrf_token'], $token)) {
		http_response_code(403);
		exit("Invalid request.");
	}

	if (isset($_POST['resolve'])) {
		$status = 'Resolved';
	} else {
		$status = $_POST['status'] ?? '';
	}
	$priority = $_POST['priority'] ?? '';

	if ($priority == '') {
		$priority = null;
	}

	$deadline = $_POST['deadline'] ?? '';
	if ($deadline == '') {
		$deadline = null;
	}

	if ($deadline != null && strtotime($deadline) == false) {
		$deadline = null;
	}

	$statuses = ['Pending', 'Under Investigation', 'Action Taken', 'Resolved', 'Rejected'];
	$priorities = ['Low', 'Medium', 'High'];

	if (in_array($status, $statuses) && ($priority == null || in_array($priority, $priorities))) {

		$responded = $row['date_responded'];
		$resolved = $row['date_resolved'];

		if ($status != 'Pending' && $responded == '') {
			$responded = date("Y-m-d H:i:s");
		}

		if ($status == 'Resolved' && $resolved == '') {
			$resolved = date("Y-m-d H:i:s");
		}

		$update = $pdo->prepare("UPDATE complaints SET status = :status, priority = :priority, deadline = :deadline, date_responded = :responded, date_resolved = :resolved WHERE ID = :id");
		$update->execute([
			':status' => $status,
			':priority' => $priority,
			':deadline' => $deadline,
			':responded' => $responded,
			':resolved' => $resolved,
			':id' => $id
		]);

		unset($_SESSION['manage_id']);
		header("location:index.php");
		exit();
	} else {
		$error = "Invalid status or priority.";
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Update Complaint Status</title>
	<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
	<style>
		html { font-size: 112.5%; }
	</style>
	<link rel="stylesheet" href="../assets/css/barangay.css">
</head>
<body>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="acc.php" class="nav-item nav-link">Account</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>

	<div class="container-fluid p-4">
		<h1 class="mb-2">Barangay Camp 7: Service Monitoring System</h1>
		<hr>
		<h2 class="mb-4">Update Complaint Status</h2>

		<?php if ($error != ''): ?>
			<div class="alert alert-danger fs-5"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<div class="row">

			<div class="col-md-5 mb-4">
				<div class="card shadow-sm p-3 h-100">
					<h4 class="mb-3">Complaint Details</h4>
					<p class="mb-2"><strong>Reference No.:</strong> <?php echo htmlspecialchars($row['reference_no']); ?></p>
					<p class="mb-2"><strong>Department:</strong> <?php echo htmlspecialchars($row['department']); ?></p>
					<p class="mb-2"><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
					<p class="mb-2"><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
					<p class="mb-2"><strong>Address:</strong> <?php echo htmlspecialchars($row['street'] . ', ' . $row['barangay']); ?></p>
					<p class="mb-0">
						<strong>Current Status:</strong>
						<span class="badge fs-6 <?php echo $badge[$row['status']] ?? 'bg-secondary'; ?>">
							<?php echo htmlspecialchars($row['status']); ?>
						</span>
					</p>
				</div>
			</div>

			<div class="col-md-5 mb-4">
				<div class="card shadow-sm p-3 h-100">
					<h4 class="mb-3">Update</h4>
					<form method="post">

						<!-- [SECURITY 5] Hidden CSRF token -->
						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

						<div class="mb-3">
							<label for="status" class="form-label fw-bold">Status</label>
							<select name="status" id="status" class="form-select form-select-lg">
								<option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
								<option value="Under Investigation" <?php if ($row['status'] == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
								<option value="Action Taken" <?php if ($row['status'] == 'Action Taken') echo 'selected'; ?>>Action Taken</option>
								<option value="Resolved" <?php if ($row['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
								<option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
							</select>
						</div>

						<div class="mb-3">
							<label for="priority" class="form-label fw-bold">Priority</label>
							<select name="priority" id="priority" class="form-select form-select-lg">
								<option value="" <?php if ($row['priority'] == '') echo 'selected'; ?>>Not set</option>
								<option value="Low" <?php if ($row['priority'] == 'Low') echo 'selected'; ?>>Low</option>
								<option value="Medium" <?php if ($row['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
								<option value="High" <?php if ($row['priority'] == 'High') echo 'selected'; ?>>High</option>
							</select>
						</div>

						<div class="mb-4">
							<label for="deadline" class="form-label fw-bold">Deadline</label>
							<!-- [SECURITY 6] htmlspecialchars on the deadline -->
							<input type="date" name="deadline" id="deadline" class="form-control form-control-lg" value="<?php echo htmlspecialchars($row['deadline'] ?? ''); ?>">
						</div>

						<div class="d-flex flex-wrap gap-2">
							<button type="submit" name="update" class="btn btn-primary btn-lg">Update</button>
							<?php if ($row['status'] != 'Resolved'): ?>
								<button type="submit" name="resolve" class="btn btn-success btn-lg" onclick="return confirm('Mark this complaint as Resolved?');">Mark as Resolved</button>
							<?php endif; ?>
							<a href="index.php" class="btn btn-warning btn-lg">Cancel</a>
						</div>
					</form>
				</div>
			</div>

		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>
</html>