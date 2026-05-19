<?php
require '../../config/db.php';

$id = null;
$kennzeichen = '';
$fahrzeug_typ = '';
$marke = '';
$modell = '';
$vin = '';
$status = 'Aktiv';
$pageTitle = "Neues Fahrzeug anlegen";

// 1. EDIT MODE Check
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pageTitle = "Fahrzeugprofil bearbeiten";

    $stmt = $pdo->prepare("SELECT * FROM fahrzeuge WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        $kennzeichen = $vehicle['kennzeichen'];
        $fahrzeug_typ = $vehicle['fahrzeug_typ'];
        $marke = $vehicle['marke'];
        $modell = $vehicle['modell'];
        $vin = $vehicle['vin'];
        $status = $vehicle['status'];
    } else {
        die("Fahrzeug nicht gefunden.");
    }
}

// 2. SAVE LOGIC (Insert or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kennzeichen = $_POST['kennzeichen'];
    $fahrzeug_typ = $_POST['fahrzeug_typ'];
    $marke = $_POST['marke'];
    $modell = $_POST['modell'];
    $vin = $_POST['vin'];
    $status = $_POST['status'];

    if (!empty($_POST['id'])) {
        // UPDATE Existing Profile
        $updateId = $_POST['id'];
        $sql = "UPDATE fahrzeuge SET kennzeichen = ?, fahrzeug_typ = ?, marke = ?, modell = ?, vin = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kennzeichen, $fahrzeug_typ, $marke, $modell, $vin, $status, $updateId]);
    } else {
        // INSERT New Profile
        $sql = "INSERT INTO fahrzeuge (kennzeichen, fahrzeug_typ, marke, modell, vin, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kennzeichen, $fahrzeug_typ, $marke, $modell, $vin, $status]);
    }

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include '../../components/navbar.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $pageTitle ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="vehicle_form.php">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Kennzeichen *</label>
                                <input type="text" name="kennzeichen" class="form-control" value="<?= htmlspecialchars($kennzeichen) ?>" required placeholder="z.B. CJ 12 ABC">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Fahrzeugtyp *</label>
                                <select name="fahrzeug_typ" class="form-select" required>
                                    <option value="" disabled <?= empty($fahrzeug_typ) ? 'selected' : '' ?>>Bitte wählen...</option>
                                    <option value="PKW" <?= $fahrzeug_typ == 'PKW' ? 'selected' : '' ?>>PKW (Car)</option>
                                    <option value="Transporter" <?= $fahrzeug_typ == 'Transporter' ? 'selected' : '' ?>>Transporter (Van)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Marke *</label>
                                <input type="text" name="marke" class="form-control" value="<?= htmlspecialchars($marke) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Modell *</label>
                                <input type="text" name="modell" class="form-control" value="<?= htmlspecialchars($modell) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Fahrgestellnummer (VIN) *</label>
                                <input type="text" name="vin" class="form-control" value="<?= htmlspecialchars($vin) ?>" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-danger fw-bold">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="Aktiv" <?= $status == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
                                    <option value="In Reparatur" <?= $status == 'In Reparatur' ? 'selected' : '' ?>>In Reparatur</option>
                                    <option value="Ausgemustert" <?= $status == 'Ausgemustert' ? 'selected' : '' ?>>Ausgemustert</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $id ? 'Änderungen speichern' : 'Fahrzeug anlegen' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>