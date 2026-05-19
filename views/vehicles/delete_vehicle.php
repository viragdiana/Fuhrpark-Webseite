<?php
require '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../index.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT fahrer_id FROM fahrzeuge WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if ($vehicle) {
    if (!empty($vehicle['fahrer_id'])) {
        $updateStmt = $pdo->prepare("UPDATE fahrzeuge SET status = 'Ausgemustert' WHERE id = ?");
        $updateStmt->execute([$id]);
    } else {
        $deleteStmt = $pdo->prepare("DELETE FROM fahrzeuge WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

header("Location: ../../index.php");
exit();
?>
