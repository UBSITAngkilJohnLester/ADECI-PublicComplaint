<?php
session_start();
require_once "../dbconn.php";

//check account status
$prep = $pdo->prepare("SELECT status FROM accounts WHERE ID = ?");
$prep->execute([$_SESSION['ID']]);
$account = $prep->fetch();

$message = "";
$wrongcontact = "";

$usernameexist = false;
$invalidcontact = false;
$contactrow = false;

$firstname = "";
$lastname = "";
$username = "";
$address = "";
$contact = "";

if (!isset($_SESSION['ID']) || $account['status'] != 'admin') {
    session_destroy();
    header("location:../index.php");
    exit();
}
if(isset($_POST['edit'])){
    $_SESSION['EDIT_ID'] = $_POST['edit'];
}

$sql = "SELECT * FROM accounts WHERE ID =?";
$prep = $pdo->prepare($sql);
$prep->execute([$_SESSION['EDIT_ID']]);
$row = $prep->fetch();

if(isset($_POST['update'])){
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $username = htmlspecialchars($_POST['username']);
    $address = htmlspecialchars($_POST['address']);
    $contact = htmlspecialchars($_POST['contact']);
    $status = htmlspecialchars($_POST['status']);
    $id = $_SESSION['EDIT_ID'];

    $sql = "SELECT * FROM accounts WHERE ID != ? AND username = ?";
    $prep = $pdo->prepare($sql);
    $prep->execute([
            $_SESSION['EDIT_ID'], 
            $username
        ]);
    $rows = $prep->fetch();

    if ($rows) {
        $usernameexist = true;
        $message = "Username already exists.";
    }

    if ($contactrow) {
        $invalidcontact = true;
        $wrongcontact = "Contact number is already being used.";
    }

    if (!preg_match('/^[0-9]{11}$/', $contact)) {
        $invalidcontact = true;
        $wrongcontact = "Invalid contact number.";
        $contact = "";
    }

    if (!$usernameexist && !$invalidcontact && !$contactrow) {
        $sql2 = "UPDATE accounts SET firstname = ?, lastname = ?, contact_number = ?, address = ?, status = ? WHERE id = ?";
        $prep2 = $pdo->prepare($sql2);
        $prep2->execute([
            $firstname,
            $lastname,
            $contact,
            $address,
            $status,
            $id
        ]);

        header("location:manage_accounts.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">

    <title>Document</title>
</head>

<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card show p-4" style="width: 100%; max-width: 450px;">
            <?php if (!empty($message)) { ?>
                <div class="alert alert-<?php echo $message; ?> alert-dismissible fade show alert-warning" role="alert">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php }
            if (!empty($wrongcontact)) { ?>
                <div class="alert alert-<?php echo $wrongcontact; ?> alert-dismissible fade show alert-warning" role="alert">
                    <?php echo htmlspecialchars($wrongcontact, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>
            <h1 class="text-center mb-3">Government Monitoring System</h1>
            <hr>
            <h2 class="text-center mb-4">Create Account</h2>
            <form method="post">
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $row['firstname'] ?>" placeholder="eg. Marie" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $row['lastname'] ?>" placeholder="e.g. Santos" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="" selected disabled>Status</option>
                                <option value="admin" <?= $row['status'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                                <option value="head_officer" <?= $row['status'] == 'head_officer' ? 'selected' : '' ?>>Head Officer</option>
                                <option value="health_officer" <?= $row['status'] == 'health_officer' ? 'selected' : '' ?>>Health Officer</option>
                                <option value="environment_officer" <?= $row['status'] == 'environment_officer' ? 'selected' : '' ?>>Environment Officer</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="e.g. Camp 7 Washingan" value="<?php echo $row['address'] ?>" required>
                    </div>
                </div>
                <div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact No.</label>
                        <input type="text" class="form-control" id="contact" name="contact" value="<?php echo $row['contact_number'] ?>" placeholder="e.g. 09776655520" required>
                    </div>
                </div>
                <div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $row['username'] ?>" placeholder="e.g. marieS" required>
                    </div>
                </div>

                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <a href="manage_accounts.php" class="btn btn-warning">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>
</body>

</html>