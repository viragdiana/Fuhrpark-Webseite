<?php
require '../../config/db.php';

if (isset($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];

    $stmt = $pdo->prepare("UPDATE fahrzeuge SET fahrer_id = NULL WHERE id = ?");
    $stmt->execute([$vehicle_id]);

    $return_to = $_GET['return_to'] ?? 'vehicle';

    if ($return_to === 'drivers') {
        header("Location: drivers_list.php");
    } else {
        header("Location: ../vehicles/vehicle_details.php?id=" . $vehicle_id);
    }
    exit();
}
?>