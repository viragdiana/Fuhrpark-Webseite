<?php
require '../../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $pdo->prepare("DELETE FROM wartung WHERE fahrzeug_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM reifen WHERE fahrzeug_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM versicherung WHERE fahrzeug_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM vignette WHERE fahrzeug_id = ?")->execute([$id]);

    $pdo->prepare("DELETE FROM fahrzeuge WHERE id = ?")->execute([$id]);
}

header("Location: ../../index.php");
exit();
?>