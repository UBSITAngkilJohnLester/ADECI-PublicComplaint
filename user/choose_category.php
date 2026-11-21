<?php
	session_start();
	require_once "../dbconn.php";

	//check account status
	$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
	$prep->execute([$_SESSION['ID']]);
	$account = $prep->fetch();

	if (!isset($_SESSION['ID']) || $account['status'] != 'user') {
		session_destroy();
		header("location:../index.php");
		exit();
	}

	$search = "";
	if (isset($_GET['search'])) {
		$search = trim($_GET['search']);
	}

	// "Other Concern" is left out of the list, it has its own link at the bottom
	$sql = "SELECT * FROM categories WHERE category LIKE ? AND category != 'Other Concern'";
	$prep = $pdo->prepare($sql);
	$prep->execute(["%" . $search . "%"]);
	$rows = $prep->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Choose Category</title>
</head>
<body>
	<h1>What is your complaint about?</h1>

	<form method="GET" action="choose_category.php">
		<input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search (e.g. garbage, water, park)">
		<button type="submit">Search</button>
		<a href="choose_category.php">Show all</a>
	</form>

	<table border="1">
		<tr>
			<th>Category</th>
		</tr>

		<?php if (count($rows) == 0): ?>
			<tr>
				<td>No category found. Try another word, or pick "Other Concern" below.</td>
			</tr>
		<?php endif; ?>

		<?php foreach ($rows as $row): ?>
			<tr>
				<td>
					<a href="insert_complain.php?category=<?php echo urlencode($row['category']); ?>">
						<?php echo htmlspecialchars($row['category']); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>

	<p>Can't find it? <a href="insert_complain.php?category=Other+Concern">Other Concern</a></p>

	<a href="index.php">Cancel</a>
</body>
</html>