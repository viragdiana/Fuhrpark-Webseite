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
    $land = $_POST['land'];
    $vignetten_typ = $_POST['vignetten_typ'];
    $gueltig_von = $_POST['gueltig_von'];
    $gueltig_bis = $_POST['gueltig_bis'];

    $sql = "INSERT INTO vignette (fahrzeug_id, land, vignetten_typ, gueltig_von, gueltig_bis) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrzeug_id, $land, $vignetten_typ, $gueltig_von, $gueltig_bis]);

    header("Location: vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Vignette hinzufügen</title>
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
                        <i class="bi bi-geo-alt me-2"></i>Vignette / Maut: <?= htmlspecialchars($auto['kennzeichen']) ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="vignette_form.php?vehicle_id=<?= $fahrzeug_id ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Land *</label>
                                <input type="text" name="land" class="form-control" list="laender" required placeholder="z.B. Österreich">
                                <datalist id="laender">
                                    <option value="Österreich">
                                    <option value="Schweiz">
                                    <option value="Tschechien">
                                    <option value="Slowenien">
                                    <option value="Ungarn">
                                    <option value="Rumänien">
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vignetten-Typ *</label>
                                <select name="vignetten_typ" class="form-select" required>
                                    <option value="Jahresvignette">Jahresvignette</option>
                                    <option value="Monatsvignette">Monatsvignette</option>
                                    <option value="10-Tages-Vignette">10-Tages-Vignette</option>
                                    <option value="Streckenmaut">Sondermaut / Streckenmaut</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4 border-top pt-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label text-success fw-bold">Gültig von *</label>
                                <input type="date" name="gueltig_von" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Gültig bis *</label>
                                <input type="date" name="gueltig_bis" class="form-control" required>
                            </div>
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
