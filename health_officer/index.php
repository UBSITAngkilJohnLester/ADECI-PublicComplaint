<?php
session_start();
require_once "../dbconn.php";

if (!isset($_SESSION['ID'])) {
    session_destroy();
    header("location:../index.php");
    exit();
}

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!$account || $account['status'] != 'health_officer') {
    session_destroy();
    header("location:../index.php");
    exit();
}

$badge = [
    'Pending' => 'bg-warning text-white',
    'Under Investigation' => 'bg-info text-white',
    'Action Taken' => 'bg-primary text-white',
    'Resolved' => 'bg-success text-white',
    'Rejected' => 'bg-danger text-white'
];

$department = "Health and Sanitation";

// complaints of this department, Pending first, oldest first
$prep = $pdo->prepare("SELECT complaints.*, accounts.username
    FROM complaints
    JOIN accounts ON complaints.acc_id = accounts.ID
    WHERE complaints.department = :department
    ORDER BY FIELD(complaints.status, 'Pending', 'Under Investigation', 'Action Taken', 'Resolved', 'Rejected'), complaints.ID ASC");
$prep->execute([":department" => $department]);
$complaints = $prep->fetchAll();

// status counters (from the complaints already loaded)
$total = count($complaints);
$pending = 0;
$progress = 0;
$actiontaken = 0;
$resolved = 0;
$rejected = 0;

foreach ($complaints as $c) {
    if ($c['status'] == "Pending") {
        $pending++;
    }
    if ($c['status'] == "Under Investigation") {
        $progress++;
    }
    if ($c['status'] == "Action Taken") {
        $actiontaken++;
    }
    if ($c['status'] == "Resolved") {
        $resolved++;
    }
    if ($c['status'] == "Rejected") {
        $rejected++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health and Sanitation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <style>
        html { font-size: 112.5%; }
    </style>
</head>
<body>
        <nav class="navbar bg-secondary-subtle sticky-top p-1">
            <div class="container-fluid d-flex justify-content-start gap-4">
                <a href="index.php" class="nav-item nav-link">Dashboard</a>
                <a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
            </div>
        </nav>
    <div class="container-fluid p-4">
        <h1 class="mb-2">Government Monitoring System (Health and Sanitation)</h1>
        <hr>

        <h2 class="mb-4">Complaints</h2>

        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-primary h-100">
                    <div class="card-body">
                        <h6 class="text-white">Total</h6>
                        <h2 class="text-white"><?php echo $total; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-warning h-100">
                    <div class="card-body">
                        <h6 class="text-white">Pending</h6>
                        <h2 class="text-white"><?php echo $pending; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-info h-100">
                    <div class="card-body">
                        <h6 class="text-white">Under Investigation</h6>
                        <h2 class="text-white"><?php echo $progress; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-secondary h-100">
                    <div class="card-body">
                        <h6 class="text-white">Action Taken</h6>
                        <h2 class="text-white"><?php echo $actiontaken; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-success h-100">
                    <div class="card-body">
                        <h6 class="text-white">Resolved</h6>
                        <h2 class="text-white"><?php echo $resolved; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm rounded p-3 bg-danger h-100">
                    <div class="card-body">
                        <h6 class="text-white">Rejected</h6>
                        <h2 class="text-white"><?php echo $rejected; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <tr>
                    <th>Reference No.</th>
                    <th>Sent By</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th>Date</th>
                    <th>Photo</th>
                    <th>Actions</th>
                </tr>
                <?php if (count($complaints) == 0): ?>
                    <tr>
                        <td colspan="11">No complaints for this department.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['reference_no']); ?></td>
                        <td><?php echo htmlspecialchars($c['username']); ?></td>
                        <td><?php echo htmlspecialchars($c['category']); ?></td>
                        <td><?php echo htmlspecialchars($c['description']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($c['street'] . ', ' . $c['barangay']); ?>
                            <?php if ($c['landmark'] != ''): ?>
                                <br>Near <?php echo htmlspecialchars($c['landmark']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge fs-6 <?php echo $badge[$c['status']] ?? 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars($c['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $c['priority'] == '' ? 'Not set' : htmlspecialchars($c['priority']); ?></td>
                        <td>
                            <?php if ($c['deadline'] == ''): ?>
                                Not set
                            <?php else: ?>
                                <?php echo date("M d, Y", strtotime($c['deadline'])); ?>
                                <?php if ($c['deadline'] < date("Y-m-d") && $c['status'] != 'Resolved' && $c['status'] != 'Rejected'): ?>
                                    <br><span class="badge bg-danger fs-6">Overdue</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date("M d, Y", strtotime($c['date_submitted'])); ?></td>
                        <td>
                            <?php if ($c['photo_path'] != ''): ?>
                                <img src="../user/<?php echo htmlspecialchars($c['photo_path']); ?>" width="100" class="img-thumbnail">
                            <?php else: ?>
                                No photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="manage_edit_complaints.php">
                                <input type="hidden" name="manage_id" value="<?php echo $c['ID']; ?>">
                                <button type="submit" class="btn btn-primary btn-lg">Manage</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>
</html>