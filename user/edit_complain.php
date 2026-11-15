<?php
session_start();
require_once "../dbconn.php";

// Make sure the user is logged in.
if (!isset($_SESSION['ID'])) {
	header("location:../index.php");
	exit();
}

// Get the complaint ID from the URL.
if (!isset($_GET['id'])) {
	header("location:index.php");
	exit();
}
$id = $_GET['id'];

// Load the current complaint (only if it belongs to the user).
$select = $pdo->prepare("SELECT * FROM complaints WHERE ID = ? AND acc_id = ?");
$select->execute([$id, $_SESSION['ID']]);
$row = $select->fetch();

// Only Pending complaints can be edited.
if ($row['status'] != 'Pending') {
	header("location:index.php");
	exit();
}

$error = "";

// Save changes when the form is submitted.
if (isset($_POST['update'])) {
	$description = $_POST['description'];
	$street = $_POST['street'];
	$barangay = $_POST['barangay'];
	$landmark = $_POST['landmark'];

	// Keep the old photo unless a new one is uploaded.
	$photo_path = $row['photo_path'];

	if ($_FILES['photo']['name'] != '') {
		$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

		if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
			$new_path = "uploads/" . time() . "_" . basename($_FILES['photo']['name']);
			move_uploaded_file($_FILES['photo']['tmp_name'], $new_path);
			$photo_path = $new_path;
		} else {
			$error = "Only JPG and PNG photos are allowed.";
		}
	}

	if ($error == "") {
		$update = $pdo->prepare("UPDATE complaints SET
			description = :description,
			street = :street,
			barangay = :barangay,
			landmark = :landmark,
			photo_path = :photo_path
			WHERE ID = :id AND acc_id = :acc_id");

	$update->execute([
		':description' => $description,
		':street' => $street,
		':barangay' => $barangay,
		':landmark' => $landmark,
		':photo_path' => $photo_path,
		':id' => $id,
		':acc_id' => $_SESSION['ID']
	]);

		header("location:index.php");
		exit();
	}
}
?>	
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Edit Complaint</title>
</head>
<body>
	<h1>Edit Complaint</h1>
	<p>Reference No.: <?php echo htmlspecialchars($row['reference_no']); ?></p>

	<?php if ($error != ''): ?>
		<p style="color:red;"><?php echo $error; ?></p>
	<?php endif; ?>

	<form method="post" enctype="multipart/form-data">
		<p>
			Description:<br>
			<textarea name="description" rows="4" cols="40" required><?php echo htmlspecialchars($row['description']); ?></textarea>
		</p>
		<p>
			Street:<br>
			<input type="text" name="street" value="<?php echo htmlspecialchars($row['street']); ?>" required>
		</p>
		<p>
			Barangay:<br>
			<input type="text" name="barangay" value="<?php echo htmlspecialchars($row['barangay']); ?>" required>
		</p>
		<p>
			Landmark:<br>
			<input type="text" name="landmark" value="<?php echo htmlspecialchars($row['landmark']); ?>">
		</p>
		<p>
			Current Photo:<br>
			<?php if ($row['photo_path'] != ''): ?>
				<img src="<?php echo htmlspecialchars($row['photo_path']); ?>" width="150">
			<?php else: ?>
				No photo
			<?php endif; ?>
		</p>
		<p>
			New Photo (optional):<br>
			<input type="file" name="photo" accept="image/*">
		</p>
		<button type="submit" name="update">Update</button>
		<a href="index.php">Cancel</a>
	</form>
</body>
</html>