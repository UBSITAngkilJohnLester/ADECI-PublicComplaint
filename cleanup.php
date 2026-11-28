<?php
function deleteOldComplaints(PDO $pdo)
{
    $expired = "((status = 'Rejected' AND closed_at < NOW() - INTERVAL 30 DAY)
              OR (status = 'Resolved' AND closed_at < NOW() - INTERVAL 1 YEAR))";

    $old = $pdo->query("SELECT ID, photo_path FROM complaints WHERE $expired")->fetchAll();

    $delete = $pdo->prepare("DELETE FROM complaints WHERE ID = ? AND $expired");

    $userFolder = realpath(__DIR__ . '/user');

    foreach ($old as $c) {
        $delete->execute([$c['ID']]);

        if ($delete->rowCount() == 0 || $c['photo_path'] == '' || $userFolder === false) {
            continue;
        }

        $photo = realpath($userFolder . '/' . $c['photo_path']);
        if ($photo !== false && strpos($photo, $userFolder . DIRECTORY_SEPARATOR) === 0 && is_file($photo)) {
            unlink($photo);
        }
    }
}
