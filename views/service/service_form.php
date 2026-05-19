<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auto) {
    die("Fahrzeug nicht gefunden.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datum = $_POST['datum'];
    $werkstatt = $_POST['werkstatt'];
    $kosten = $_POST['kosten'];
    $reparatur_typ = $_POST['reparatur_typ'];

    $sql = "INSERT INTO wartung (fahrzeug_id, datum, werkstatt, kosten, reparatur_typ) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrzeug_id, $datum, $werkstatt, $kosten, $reparatur_typ]);

    header("Location: ../vehicles/vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Wartung protokollieren</title>
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
                        <i class="bi bi-tools me-2"></i>Wartung: <?= htmlspecialchars($auto['kennzeichen']) ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="service_form.php?vehicle_id=<?= $fahrzeug_id ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Datum der Reparatur *</label>
                            <input type="date" name="datum" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Werkstatt / Dienstleister *</label>
                            <input type="text" name="werkstatt" class="form-control" required placeholder="z.B. ATU München">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Art der Reparatur *</label>
                            <input type="text" name="reparatur_typ" class="form-control" required placeholder="z.B. Ölwechsel, Bremsen erneuert">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Kosten (Brutto) *</label>
                            <div class="input-group">
                                <input type="number" name="kosten" class="form-control" step="0.01" min="0" required placeholder="0.00">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="../vehicles/vehicle_details.php?id=<?= $fahrzeug_id ?>" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary px-4">Eintrag speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
