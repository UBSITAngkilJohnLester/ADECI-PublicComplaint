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
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link rel="stylesheet" href="../assets/css/barangay.css">
	<style>
    	table { display: table !important; width: 100% !important; }
	</style>
</head>
<body>
	<h1>Admin!</h1>
	<a href="logout.php">logout</a>
	<hr>
	<a href="manage_accounts.php">Manage</a>
	<hr>
	<table border="1">
			<tr>
				<th>Username</th>
				<th>Address</th>
				<th>Contact Number</th>
				<th>Status</th>
			</tr>
	<?php 
		$sql = "SELECT * FROM accounts WHERE status != 'user' ORDER BY status";
		$prep = $pdo->prepare($sql);
		$prep->execute();
		$rows = $prep->fetchAll();
		foreach($rows as $row){?>
		
			<tr>
				<td><?php echo htmlspecialchars($row['username'])?></td>
				<td><?php echo htmlspecialchars($row['address'])?></td>
				<td><?php echo htmlspecialchars($row['contact_number'])?></td>
				<td><?php echo htmlspecialchars($row['status'])?></td>
			</tr>
		
	<?php }?>
</table>
</body>
</html>