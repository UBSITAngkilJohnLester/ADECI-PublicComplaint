<?php
require_once "dbconn.php";
session_start();

$errors  = [];
$success = "";

// Allowed values (must match your form options)
$allowedCategories = ["Civil Complaint", "Governmental Complaint", "Environmental Complaint"];
$allowedBarangays  = [
    "Lower Magsaysay",
    "Harrison-Claudio Carantes",
    "Burnham-Legarda",
    "Andres Bonifacio-Caguioa-Remando(ABCR)",
    "Lourdes Subdivision Proper"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category    = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $barangay    = $_POST['barangay'] ?? '';
    $street      = trim($_POST['street'] ?? '');
    $landmark    = trim($_POST['landmark'] ?? '');
    $photoPath   = null;

    // ---- Validate text fields ----
    if (!in_array($category, $allowedCategories, true)) {
        $errors[] = "Please select a valid complaint category.";
    }
    if ($description === '') {
        $errors[] = "Description of concern is required.";
    }
    if (!in_array($barangay, $allowedBarangays, true)) {
        $errors[] = "Please select a valid barangay.";
    }
    if ($street === '') {
        $errors[] = "Street / Purok / Subdivision is required.";
    }

    // ---- Handle optional photo ----
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Photo upload failed. Please try again.";
        } else {
            $maxSize = 5 * 1024 * 1024; // 5 MB
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($_FILES['photo']['tmp_name']);

            if (!isset($allowedTypes[$mime])) {
                $errors[] = "Only JPG, PNG, or WEBP images are allowed.";
            } elseif ($_FILES['photo']['size'] > $maxSize) {
                $errors[] = "Image must be 5 MB or smaller.";
            } else {
                $folder = __DIR__ . '/uploads/';
                if (!is_dir($folder)) {
                    mkdir($folder, 0755, true);
                }
                $newName = uniqid('complaint_', true) . '.' . $allowedTypes[$mime];

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $folder . $newName)) {
                    $photoPath = 'uploads/' . $newName;
                } else {
                    $errors[] = "Failed to save the image.";
                }
            }
        }
    }

    // ---- Save to database ----
    if (empty($errors)) {
        $referenceNo = "CMP-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(3)));

        $stmt = $conn->prepare(
            "INSERT INTO complaints
             (reference_no, category, description, barangay, street, landmark, photo_path)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        

        if ($stmt->execute()) {
            $success = "Complaint submitted! Your reference number is: " . $referenceNo;
        } else {
            $errors[] = "Database error. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h1>CIVIL COMPLAINT SUBMISSION</h1>
	
	<?php if ($success): ?>
    <p style="color: green;"><strong><?= htmlspecialchars($success) ?></strong></p>
	<?php endif; ?>

	<?php foreach ($errors as $error): ?>
	    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
	<?php endforeach; ?>

	<form method="POST" action="index.php" enctype="multipart/form-data">
		<table>
			<tr>
				<td>Complaint Category</td>
				<td>
				<select name="category" required>
					<option value="" selected disabled>Select Category</option>
					<option value="Civil Complaint">Civil Complaint</option>
					<option value="Governmental Complaint">Governmental Complaint</option>
					<option value="Environmental Complaint">Environmental Complaint</option>
				</select>
				</td>
			</tr>
			<tr>
				<td>Description of Concern</td>
				<td>
					<textarea name="description" rows="4" cols="40" required></textarea>
				</td>
			</tr>
			<tr>
				<td>Location Details</td>
				<td>
					<select name="barangay" required>
						<option value="" selected disabled>Select Barangay</option>
						<option value="Lower Magsaysay">Lower Magsaysay</option>
						<option value="Harrison-Claudio Carantes">Harrison-Claudio Carantes</option>
						<option value="Burnham-Legarda">Burnham-Legarda</option>
						<option value="Andres Bonifacio-Caguioa-Remando(ABCR)">Andres Bonifacio-Caguioa-Remando(ABCR)</option>
						<option value="Lourdes Subdivision Proper">Lourdes Subdivision Proper</option>
					</select>

					<br>
					<input type="text" name="street" placeholder="Street / Purok / Subdivision" required>
					<br>
        			<input type="text" name="landmark" placeholder="Nearest landmark (optional)">
				</td>
			</tr>
			<tr>
				<td>Photo Evidence</td>
				<td><input type="file" name="photo" id="photo" accept="image/*">
				<br>
				<img id="preview" alt="Photo preview" 
				style="display: none; max-width: 500px; margin-top: 100px;">
				</td>
			</tr>

			<tr>
				<td>
				<button type="submit">Submit</button>
				</td>
			</tr>
		</table>
	</form>
	<script>
		document.getElementById('photo').addEventListener('change', function () {
		    const file = this.files[0];
		    const preview = document.getElementById('preview');

		    if (file && file.type.startsWith('image/')) {
		        preview.src = URL.createObjectURL(file);
		        preview.style.display = 'block';
		    } else {
		        preview.src = '';
		        preview.style.display = 'none';
		    }
		});
</script>
</body>

</html>

