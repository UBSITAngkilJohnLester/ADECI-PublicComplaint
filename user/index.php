<?php
session_start();
require_once "../dbconn.php";

// Make sures the user is logged in.
if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

// Show the user's complains, newest first.
$sql = "SELECT * FROM complaints WHERE acc_id = ? ORDER BY ID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['ID']]);
$complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Government Monitoring System</title>
</head>
<body>
	<h1>Government Monitoring System (User)</h1>
	<nav>
		<a href="logout.php">Logout</a>
	</nav>
	<hr>

	<h2>My Complaints</h2>
	<p><a href="choose_category.php"><button type="button">+ Add Complaint</button></a></p>

	<table border="1">

		<?php if (count($complaints) == 0){ ?>
				<tr>
					<td colspan="6">No complaints yet.</td>
				</tr>
		<?php } ?>
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
			</tr>
			<?php foreach ($complaints as $row){ ?>
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
							<a href="edit_complain.php?id=<?php echo $row['ID']; ?>">Edit</a>
						<?php endif; ?>
					</td>
        			<td><a href="delete_complain.php?id=<?php echo $row['ID']; ?>">Delete</a></td>
			<?php } ?>
	</table>
</body>
</html>