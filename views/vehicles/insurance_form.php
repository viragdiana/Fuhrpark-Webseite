<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtIns = $pdo->prepare("SELECT * FROM versicherung WHERE fahrzeug_id = ?");
$stmtIns->execute([$fahrzeug_id]);
$insurance = $stmtIns->fetch(PDO::FETCH_ASSOC);

$gesellschaft = $insurance['gesellschaft'] ?? '';
$police_nr = $insurance['police_nr'] ?? '';
$deckungsart = $insurance['deckungsart'] ?? 'Haftpflicht';
$ablaufdatum = $insurance['ablaufdatum'] ?? '';
$kuendigungsfrist = $insurance['kuendigungsfrist'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_gesellschaft = $_POST['gesellschaft'];
    $post_police_nr = $_POST['police_nr'];
    $post_deckungsart = $_POST['deckungsart'];
    $post_ablaufdatum = $_POST['ablaufdatum'];
    $post_kuendigungsfrist = $_POST['kuendigungsfrist'];

    if ($insurance) {
        $sql = "UPDATE versicherung SET gesellschaft = ?, police_nr = ?, deckungsart = ?, ablaufdatum = ?, kuendigungsfrist = ? WHERE fahrzeug_id = ?";
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([$post_gesellschaft, $post_police_nr, $post_deckungsart, $post_ablaufdatum, $post_kuendigungsfrist, $fahrzeug_id]);
    } else {
        $sql = "INSERT INTO versicherung (fahrzeug_id, gesellschaft, police_nr, deckungsart, ablaufdatum, kuendigungsfrist) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([$fahrzeug_id, $post_gesellschaft, $post_police_nr, $post_deckungsart, $post_ablaufdatum, $post_kuendigungsfrist]);
    }

    header("Location: vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}
?>

    <!DOCTYPE html>
    <html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Versicherung verwalten</title>
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
                        <i class="bi bi-shield-check me-2"></i>Versicherung: <?= htmlspecialchars($auto['kennzeichen']) ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="insurance_form.php?vehicle_id=<?= $fahrzeug_id ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Versicherungsgesellschaft *</label>
                            <input type="text" name="gesellschaft" class="form-control" value="<?= htmlspecialchars($gesellschaft) ?>" required placeholder="z.B. Allianz, HUK-Coburg">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Policen-Nummer *</label>
                                <input type="text" name="police_nr" class="form-control" value="<?= htmlspecialchars($police_nr) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Art der Deckung *</label>
                                <select name="deckungsart" class="form-select" required>
                                    <option value="Haftpflicht" <?= $deckungsart == 'Haftpflicht' ? 'selected' : '' ?>>Haftpflicht</option>
                                    <option value="Teilkasko" <?= $deckungsart == 'Teilkasko' ? 'selected' : '' ?>>Teilkasko</option>
                                    <option value="Vollkasko" <?= $deckungsart == 'Vollkasko' ? 'selected' : '' ?>>Vollkasko</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4 border-top pt-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Ablaufdatum *</label>
                                <input type="date" name="ablaufdatum" class="form-control" value="<?= htmlspecialchars($ablaufdatum) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-warning fw-bold">Kündigungsfrist *</label>
                                <input type="date" name="kuendigungsfrist" class="form-control" value="<?= htmlspecialchars($kuendigungsfrist) ?>" required>
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
