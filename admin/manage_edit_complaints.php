<?php
session_start();
require_once "../dbconn.php";
date_default_timezone_set("Asia/Manila");



if (!isset($_SESSION['ID'])) {
	session_destroy();
	header("location:../index.php");
	exit();
}

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!isset($_SESSION['ID']) || $account['status'] != 'admin') {
	session_destroy();
	header("location:../index.php");
	exit();
}


// Save the clicked complaint ID in the session, then clean the URL.
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

// Load the complaint (no acc_id check, the admin can see all complaints).
$select = $pdo->prepare("SELECT * FROM complaints WHERE ID = ?");
$select->execute([$id]);
$row = $select->fetch();

if (!$row) {
	header("location:index.php");
	exit();
}

$error = "";

// Save the new status when the form is submitted.
if (isset($_POST['update'])) {
		$status = $_POST['status'];
	$priority = $_POST['priority'];

	// Empty priority means "Not set".
	if ($priority == '') {
		$priority = null;
	}

	// Empty deadline means "Not set".
	$deadline = $_POST['deadline'];
	if ($deadline == '') {
		$deadline = null;
	}
	// A wrong date is treated as "Not set".
	if ($deadline != null && strtotime($deadline) == false) {
		$deadline = null;
	}
	// Only allow these statuses and priorities.
		$statuses = ['Pending', 'Under Investigation', 'Action Taken', 'Resolved', 'Rejected'];
		$priorities = ['Low', 'Medium', 'High'];

	if (in_array($status, $statuses) && ($priority == null || in_array($priority, $priorities))) {

		$responded = $row['date_responded'];
		$resolved = $row['date_resolved'];

		// First time it leaves Pending = responded.
		if ($status != 'Pending' && $responded == '') {
			$responded = date("Y-m-d H:i:s");
		}

		// First time it becomes Resolved = resolved.
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
		header("location:manage_complaints.php?department=" . urlencode($row['department']));
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
</head>
<body>
	<h1>Update Complaint Status</h1>

	<?php if ($error != ''): ?>
		<p style="color:red;"><?php echo $error; ?></p>
	<?php endif; ?>

	<p>Reference No.: <?php echo htmlspecialchars($row['reference_no']); ?></p>
	<p>Department: <?php echo htmlspecialchars($row['department']); ?></p>
	<p>Category: <?php echo htmlspecialchars($row['category']); ?></p>
	<p>Description: <?php echo htmlspecialchars($row['description']); ?></p>
	<p>Address: <?php echo htmlspecialchars($row['street'] . ', ' . $row['barangay']); ?></p>

	<form method="post">
		<p>
			Status:<br>
			<select name="status">
				<option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
				<option value="Under Investigation" <?php if ($row['status'] == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
				<option value="Action Taken" <?php if ($row['status'] == 'Action Taken') echo 'selected'; ?>>Action Taken</option>
				<option value="Resolved" <?php if ($row['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
				<option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
				</select>
		</p>
		<p>
			Priority:<br>
			<select name="priority">
				<option value="" <?php if ($row['priority'] == '') echo 'selected'; ?>>Not set</option>
				<option value="Low" <?php if ($row['priority'] == 'Low') echo 'selected'; ?>>Low</option>
				<option value="Medium" <?php if ($row['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
				<option value="High" <?php if ($row['priority'] == 'High') echo 'selected'; ?>>High</option>
			</select>
		</p>
		<p>
			Deadline:<br>
			<input type="date" name="deadline" value="<?php echo $row['deadline']; ?>">
		</p>
		<button type="submit" name="update">Update</button>
		<a href="manage_complaints.php?department=<?php echo urlencode($row['department']); ?>">Cancel</a>
	</form>
</body>
</html>