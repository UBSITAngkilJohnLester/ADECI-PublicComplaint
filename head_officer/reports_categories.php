<?php
session_start();
require_once "../dbconn.php";

$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

// check user is logged in and with the right credentials
if (!isset($_SESSION['ID']) || $account['status'] != 'barangay_captain') {
	session_destroy();
	header("location:../index.php");
	exit();
}

// Pagination, because the category list gets long
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$prep = $pdo->prepare("SELECT COUNT(*) FROM (SELECT 1 FROM complaints GROUP BY department, category) x");
$prep->execute();
$totalRows = (int)$prep->fetchColumn();
$pages = max(1, (int)ceil($totalRows / $perPage));

$prep = $pdo->prepare("SELECT department, category, COUNT(*) AS total
	FROM complaints
	GROUP BY department, category
	ORDER BY total DESC, department, category
	LIMIT $perPage OFFSET $offset");
$prep->execute();
$cats = $prep->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Complaints by Category</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>

<body>
	<nav class="navbar bg-secondary-subtle sticky-top p-1">
		<div class="container-fluid d-flex justify-content-start gap-4">
			<a href="index.php" class="nav-item nav-link">Dashboard</a>
			<a href="manage_complaints.php" class="nav-item nav-link">Manage</a>
			<a href="admin_feedback.php" class="nav-item nav-link">Feedback</a>
			<a href="reports.php" class="nav-item nav-link">Reports</a>
			<a href="admin_announcement.php" class="nav-item nav-link">Announcement</a>
			<a href="acc.php" class="nav-item nav-link">Account</a>
			<a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
		</div>
	</nav>

	<div class="container-fluid p-4">
		<h1 class="mb-2">Barangay Camp 7: Service Monitoring System</h1>
		<hr>
		<h2 class="mb-3">Complaints by Category</h2>
		<a href="reports.php" class="btn btn-warning mb-3">Back to Reports</a>

		<div class="col-md-8">
			<table class="table table-striped table-hover table-bordered text-center">
				<tr>
					<th>Department</th>
					<th>Category</th>
					<th>Complaints</th>
				</tr>
				<?php if (count($cats) == 0): ?>
					<tr>
						<td colspan="3">No complaints yet.</td>
					</tr>
				<?php endif; ?>
				<?php foreach ($cats as $c): ?>
					<tr>
						<td><?php echo htmlspecialchars($c['department']); ?></td>
						<td><?php echo htmlspecialchars($c['category']); ?></td>
						<td><?php echo $c['total']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<nav>
				<ul class="pagination">
					<?php for ($i = 1; $i <= $pages; $i++): ?>
						<li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
							<a class="page-link" href="reports_categories.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
						</li>
					<?php endfor; ?>
				</ul>
			</nav>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>