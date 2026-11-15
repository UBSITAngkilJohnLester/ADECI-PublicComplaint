<?php
require_once "../dbconn.php";
session_start();

$errors = [];

// retrieves the category
$category = $_POST['category'] ?? $_GET['category'] ?? '';

$sql = "SELECT * FROM categories WHERE category = ?";
$prep = $pdo->prepare($sql);
$prep->execute([$category]);
$rows = $prep->fetchAll();

// no valid category, It goes back to choosing a category
if (count($rows) < 1) {
	header("location:choose_category.php");
	exit();
}
$department = $rows[0]['department'];

if (isset($_POST['submit'])) {
	$description = trim($_POST['description']);
	$barangay    = trim($_POST['barangay']);
	$street      = trim($_POST['street']);
	$landmark    = trim($_POST['landmark']);
	$photoPath   = '';

	if ($description == '' || $barangay == '' || $street == '') {
		$errors[] = "Please fill in all required fields.";
	}

	// photo insert (optional)
	if ($_FILES['photo']['error'] != UPLOAD_ERR_NO_FILE) {
		$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

		// photo upload checker
		if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
			$errors[] = "Only JPG, PNG, or WEBP images are allowed.";
		// photo was uploaded
		} else {
			$newName = uniqid('complaint_') . '.' . $ext;
			move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $newName);
			$photoPath = 'uploads/' . $newName;
		}
	}


	//if no errors, the function is allowed to run
	if (empty($errors)) {
		$reference  = "CMP-" . date("Ymd") . "-" . rand(1000, 9999);

		$insert = "INSERT INTO complaints(reference_no, department, category, description, barangay, street, landmark, photo_path, status, acc_id)
			VALUES(:reference, :department, :category, :description, :barangay, :street, :landmark, :photo, 'Pending', :accId)";
		$prep = $pdo->prepare($insert);
		$saved = $prep->execute([
			":reference" => $reference,
			":department" => $department,
			":category" => $category,
			":description" => $description,
			":barangay" => $barangay,
			":street" => $street,
			":landmark" => $landmark,
			":photo" => $photoPath,
			":accId" => $_SESSION['ID']
		]);


		//condition if it was succesful
		if ($saved) {
			$_SESSION['success'] = "Complaint successfully added! Your reference number is: " . $reference;
			header("location:index.php");
			exit();
		} else {
			$errors[] = "Something went wrong. Please try again.";
		}
	}
}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Submit Complaint</title>
</head>
<body>
	<h1>COMPLAINT SUBMISSION</h1>

	<?php foreach ($errors as $error): ?>
		<p style="color: red;"><?php echo $error; ?></p>
	<?php endforeach; ?>

	<form method="POST" enctype="multipart/form-data">
		<table>
			<tr>
				<td>Complaint Category</td>
				<td>
					<strong><?php echo $category; ?></strong>
					<input type="hidden" name="category" value="<?php echo $category; ?>">
					<a href="choose_category.php">Change</a>
				</td>
			</tr>
			<tr>
				<td>Description of Concern</td>
				<td><textarea name="description" rows="4" cols="40" required></textarea></td>
			</tr>
	<tr>
		<td>Location Details</td>
		<td>
			<input type="text" name="barangay" placeholder="Barangay" required>
			<input type="text" name="street" placeholder="Street / Purok / Subdivision" required>
			<input type="text" name="landmark" placeholder="Nearest landmark (optional)">
		</td>
	</tr>

			<tr>
				<td>Photo Evidence</td>
				<td><input type="file" name="photo" accept="image/*"></td>
			</tr>
			<tr>
				<td><button type="submit" name="submit">Submit</button></td>
				<td><a href="index.php">Cancel</a></td>
			</tr>

		</table>
	</form>
</body>
</html>