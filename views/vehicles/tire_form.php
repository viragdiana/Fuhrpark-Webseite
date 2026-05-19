<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saison = $_POST['saison'];
    $marke = $_POST['marke'];
    $lagerort = $_POST['lagerort'];
    $profiltiefe = (float) str_replace(',', '.', $_POST['profiltiefe']);

    $stmtCheck = $pdo->prepare("SELECT id FROM reifen WHERE fahrzeug_id = ? AND saison = ?");
    $stmtCheck->execute([$fahrzeug_id, $saison]);
    $existing = $stmtCheck->fetch();

    if ($existing) {
        $sql = "UPDATE reifen SET marke = ?, lagerort = ?, profiltiefe = ? WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([$marke, $lagerort, $profiltiefe, $existing['id']]);
    } else {
        $sql = "INSERT INTO reifen (fahrzeug_id, saison, marke, lagerort, profiltiefe) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([$fahrzeug_id, $saison, $marke, $lagerort, $profiltiefe]);
    }

    header("Location: vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Reifensatz verwalten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include '../../components/navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-record-circle me-2"></i>Reifensatz: <?= htmlspecialchars($auto['kennzeichen']) ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="tire_form.php?vehicle_id=<?= $fahrzeug_id ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Saison / Typ *</label>
                                <select name="saison" class="form-select" required>
                                    <option value="Sommer">Sommerreifen</option>
                                    <option value="Winter">Winterreifen</option>
                                    <option value="Allwetter">Allwetterreifen</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marke (Brand) *</label>
                                <input type="text" name="marke" class="form-control" required placeholder="z.B. Michelin, Continental">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lagerort (Storage Location) *</label>
                            <input type="text" name="lagerort" class="form-control" list="lagerorte" required placeholder="z.B. Am Fahrzeug montiert">
                            <datalist id="lagerorte">
                                <option value="Am Fahrzeug montiert">
                                <option value="Lager intern (Halle A)">
                                <option value="Extern (Reifen-Händler)">
                            </datalist>
                        </div>

                        <div class="mb-4 border-top pt-3 mt-3">
                            <label class="form-label fw-bold">Aktuelle Profiltiefe *</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" name="profiltiefe" class="form-control" step="0.1" min="0" max="15" required placeholder="z.B. 6.5">
                                <span class="input-group-text">mm</span>
                            </div>
                            <small class="text-muted mt-2 d-block">Systemwarnung bei Werten unter 1,6 mm.</small>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="vehicle_details.php?id=<?= $fahrzeug_id ?>" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary px-4">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>