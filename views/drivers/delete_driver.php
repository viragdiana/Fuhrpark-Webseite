<?php
require '../../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmtUpdate = $pdo->prepare("UPDATE fahrzeuge SET fahrer_id = NULL WHERE fahrer_id = ?");
    $stmtUpdate->execute([$id]);

    $stmtDelete = $pdo->prepare("DELETE FROM fahrer WHERE id = ?");
    $stmtDelete->execute([$id]);
}

header("Location: drivers_list.php");
exit();
?>
