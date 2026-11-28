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

	if (isset($_GET['id'])) {
		$_SESSION['feedback_complaint_id'] = $_GET['id'];
		header("location:user_feedback.php");
		exit();
	}
	if (isset($_POST['complaint_id']) && !isset($_POST['submit'])) {
		$_SESSION['feedback_complaint_id'] = $_POST['complaint_id'];
		header("location:user_feedback.php");
		exit();
	}

	if (!isset($_SESSION['feedback_complaint_id'])) {
		header("location:index.php");
		exit();
	}
	$complaintId = $_SESSION['feedback_complaint_id'];

	$select = $pdo->prepare("SELECT ID, reference_no, category FROM complaints WHERE ID = ? AND acc_id = ?");
	$select->execute([$complaintId, $_SESSION['ID']]);
	$complaint = $select->fetch();

	if (!$complaint) {
		unset($_SESSION['feedback_complaint_id']);
		header("location:index.php");
		exit();
	}

	$check = $pdo->prepare("SELECT ID FROM feedback WHERE complaint_id = ? AND acc_id = ?");
	$check->execute([$complaintId, $_SESSION['ID']]);
	if ($check->fetch()) {
		unset($_SESSION['feedback_complaint_id']);
		header("location:index.php");
		exit();
	}

	if (isset($_POST['submit'])) {
		$insert = "INSERT INTO feedback
			(complaint_id, acc_id, satisfaction_rating, comments, timeliness,
			professionalism, communication, resolution_status, date_submitted)
			VALUES
			(:complaint_id, :acc_id, :rating, :comments, :timeliness,
			:professionalism, :communication, :resolution_status, NOW())";

		$prep = $pdo->prepare($insert);
		$prep->execute([
			":complaint_id"      => $complaintId,
			":acc_id"            => $_SESSION['ID'],
			":rating"            => $_POST['satisfaction_rating'],
			":comments"          => trim($_POST['comments']),
			":timeliness"        => $_POST['timeliness'],
			":professionalism"   => $_POST['professionalism'],
			":communication"     => $_POST['communication'],
			":resolution_status" => $_POST['resolution_status']
			]);

		unset($_SESSION['feedback_complaint_id']);
		header("location:index.php");
		exit();
	}

	function ratingRadio($name) {
		$options = [5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Neutral', 2 => '2 - Poor', 1 => '1 - Very Poor'];
		foreach ($options as $value => $label) {
			echo "<label><input type='radio' name='$name' value='$value' required> $label</label> ";
		}
	}

	function satisfactionRadio($name) {
		$options = [5 => 'Very Satisfied', 4 => 'Satisfied', 3 => 'Neutral',
					2 => 'Dissatisfied', 1 => 'Very Dissatisfied'];
		foreach ($options as $value => $label) {
			echo "<label><input type='radio' name='$name' value='$value' required> $label</label><br>";
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Citizen Feedback</title>
	<link rel="stylesheet" href="../assets/css/barangay.css">
</head>
<body>
	<h1>Citizen Feedback</h1>
	<p>Complaint: <?php echo htmlspecialchars($complaint['reference_no']); ?>
	   (<?php echo htmlspecialchars($complaint['category']); ?>)</p>

	<form method="post">
		
		<h2>Satisfaction Ratings</h2>
		<p>Overall satisfaction (1 = poor, 5 = excellent):<br>
			<?php ratingRadio('satisfaction_rating'); ?></p>
		<hr>

		<h2>Feedback Comments</h2>
		<p><textarea name="comments" rows="4" cols="50"></textarea></p>
		<hr>

		<h2>Service Evaluation</h2>
		<p>How satisfied are you with the timeliness of the service?<br><?php satisfactionRadio('timeliness'); ?></p>
		<p>How satisfied are you with the professionalism of the staff?<br><?php satisfactionRadio('professionalism'); ?></p>
		<p>How satisfied are you with the communication and updates?<br><?php satisfactionRadio('communication'); ?></p>
		<hr>

		<h2>Complaint Resolution Feedback</h2>
		<p>Was your complaint resolved?<br>
			<label><input type="radio" name="resolution_status" value="yes" required> Yes</label><br>
			<label><input type="radio" name="resolution_status" value="no" required> No</label>
		</p>

		<p><button type="submit" name="submit">Submit Feedback</button>
		   <a href="index.php">Cancel</a></p>
		<hr>
	</form>
</body>
</html>