<?php
session_start();
require_once "../dbconn.php";

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

if (!isset($_SESSION['ID']) || $account['status'] != 'environment_officer') {
    session_destroy();
    header("location:../index.php");
    exit();
}

$sql = "SELECT * FROM accounts WHERE ID = ?";
$prep = $pdo->prepare($sql);
$prep->execute([$_SESSION['ID']]);
$row = $prep->fetch();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">

    <link rel="stylesheet" href="../assets/css/barangay.css">
</head>

<style>
    html {
        scrollbar-gutter: stable;
    }
</style>

<body class="bg-secondary-subtle">
    <nav class="navbar bg-secondary-subtle sticky-top p-1">
        <div class="container-fluid d-flex justify-content-start gap-4">
            <a href="index.php" class="nav-item nav-link">Dashboard</a>
            <a href="feedback.php" class="nav-item nav-link">Feedback</a>
            <a href="acc.php" class="nav-item nav-link">Account</a>
            <a href="logout.php" class="nav-item nav-link ms-auto">Logout</a>
        </div>
    </nav>
    <h1>Account Details</h1>
    <table>
        <tr>
            <th>First Name: </th>
            <td><?php echo $row['firstname'] ?></td>
        </tr>
        <tr>
            <th>Last Name: </th>
            <td><?php echo $row['lastname'] ?></td>
        </tr>
        <tr>
            <th>Address: </th>
            <td><?php echo $row['address'] ?></td>
        </tr>
        <tr>
            <th>Contact Number: </th>
            <td><?php echo $row['contact_number'] ?></td>
        </tr>
        <tr>
            <th>Username: </th>
            <td><?php echo $row['username'] ?></td>
        </tr>
        <tr>
            <td>
                <form action="edit_acc.php" method="post"><button name="edit_acc">Edit</button></form>
                <a href="index.php">Back</a>
            </td>
        </tr>
    </table>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>

</html>