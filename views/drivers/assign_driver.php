<?php
require '../../config/db.php';

$stmtVehicles = $pdo->query("SELECT id, kennzeichen, marke, modell FROM fahrzeuge WHERE status = 'Aktiv'");
$activeVehicles = $stmtVehicles->fetchAll(PDO::FETCH_ASSOC);

$stmtDrivers = $pdo->query("SELECT id, vorname, nachname, mitarbeiter_id FROM fahrer");
$drivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);

$preSelectedVehicle = $_GET['vehicle_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_id = $_POST['vehicle_id'];
    $fahrer_id = $_POST['fahrer_id'];

    $sql = "UPDATE fahrzeuge SET fahrer_id = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrer_id, $vehicle_id]);

    header("Location: ../vehicles/vehicle_details.php?id=" . $vehicle_id);
    exit();
}
?>

    <!DOCTYPE html>
    <html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Zuweisung eines Fahrers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include '../../components/navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-link-45deg me-2"></i>Fahrzeugzuweisung – Fahrer</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="assign_driver.php">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Fahrzeug auswählen (nur aktive) *</label>
                            <select name="vehicle_id" class="form-select" required>
                                <option value="" disabled <?= empty($preSelectedVehicle) ? 'selected' : '' ?>>Wähle ein Fahrzeug aus...</option>
                                <?php foreach ($activeVehicles as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= $preSelectedVehicle == $v['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v['kennzeichen']) ?> - <?= htmlspecialchars($v['marke']) ?> <?= htmlspecialchars($v['modell']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($activeVehicles)): ?>
                                <small class="text-danger mt-1 d-block"><i class="bi bi-exclamation-circle"></i> Es sind keine aktiven Fahrzeuge verfügbar.</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Wähle den Fahrer aus *</label>
                            <select name="fahrer_id" class="form-select" required>
                                <option value="" disabled selected>Wähle einen Fahrer aus...</option>
                                <?php foreach ($drivers as $d): ?>
                                    <option value="<?= $d['id'] ?>">
                                        <?= htmlspecialchars($d['vorname']) ?> <?= htmlspecialchars($d['nachname']) ?> (ID: <?= htmlspecialchars($d['mitarbeiter_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="../../index.php" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary px-4" <?= empty($activeVehicles) ? 'disabled' : '' ?>>Zuweisung speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
    </html>
