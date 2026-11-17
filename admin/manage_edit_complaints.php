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

	// Only allow these statuses.
	if ($status == 'Pending' || $status == 'In Progress' || $status == 'Resolved' || $status == 'Rejected') {
		$update = $pdo->prepare("UPDATE complaints SET status = :status WHERE ID = :id");
		$update->execute([
			':status' => $status,
			':id' => $id
		]);

		unset($_SESSION['manage_id']);
		header("location:manage_complaints.php?department=" . urlencode($row['department']));
		exit();
	} else {
		$error = "Invalid status.";
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
				<option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
				<option value="Resolved" <?php if ($row['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
				<option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
			</select>
		</p>
		<button type="submit" name="update">Update</button>
		<a href="manage_complaints.php?department=<?php echo urlencode($row['department']); ?>">Cancel</a>
	</form>
</body>
</html>