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

if(isset($_POST['edit']))
    $_SESSION['EDIT_ID'] = $_POST['edit'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/barangay.css">
</head>

<body>
    <h1>Manage Accounts</h1>
    <a href="logout.php">logout</a>
    <a href="index.php">Back</a>
    <hr>
    <a href="add_acc.php">+New Account</a>
    <hr>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Address</th>
            <th>Contact Number</th>
            <th>Status</th>
            <th>Reset Password</th>
        </tr>
        <?php
        $sql = "SELECT * FROM accounts ORDER BY status";
        $prep = $pdo->prepare($sql);
        $prep->execute();
        $rows = $prep->fetchAll();
        foreach ($rows as $row) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']) ?></td>
                <td><?php echo htmlspecialchars($row['address']) ?></td>
                <td><?php echo htmlspecialchars($row['contact_number']) ?></td>
                <td><?php echo htmlspecialchars($row['status']) ?></td>
                <td><?php if ($row['status'] == "admin") { ?>
                        <button disabled>Reset Password</button>
                    <?php } else { ?>
                        <form method="post"><button name="edit" value="<?php echo $row['ID'];?>">Reset Password</button></form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>

</html>