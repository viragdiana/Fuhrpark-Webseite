<?php
require 'db.php';

$id = null;
$vorname = '';
$nachname = '';
$mitarbeiter_id = '';
$saved_klassen = [];
$naechste_pruefung = '';
$pageTitle = "Fahrerprofil anlegen";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pageTitle = "Fahrerprofil bearbeiten";

    $stmt = $pdo->prepare("SELECT * FROM fahrer WHERE id = ?");
    $stmt->execute([$id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($driver) {
        $vorname = $driver['vorname'];
        $nachname = $driver['nachname'];
        $mitarbeiter_id = $driver['mitarbeiter_id'];
        $naechste_pruefung = $driver['naechste_pruefung'];
        if (!empty($driver['fuehrerscheinklassen'])) {
            $saved_klassen = explode(', ', $driver['fuehrerscheinklassen']);
        }
    } else {
        die("Fahrereintrag nicht gefunden.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vorname = $_POST['vorname'];
    $nachname = $_POST['nachname'];
    $mitarbeiter_id = $_POST['mitarbeiter_id'];
    $naechste_pruefung = $_POST['naechste_pruefung'];

    $klassen_array = $_POST['klassen'] ?? [];
    $fuehrerscheinklassen = implode(', ', $klassen_array);

    if (!empty($_POST['id'])) {
        $updateId = $_POST['id'];
        $sql = "UPDATE fahrer SET vorname = ?, nachname = ?, mitarbeiter_id = ?, fuehrerscheinklassen = ?, naechste_pruefung = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vorname, $nachname, $mitarbeiter_id, $fuehrerscheinklassen, $naechste_pruefung, $updateId]);
    } else {
        $sql = "INSERT INTO fahrer (vorname, nachname, mitarbeiter_id, fuehrerscheinklassen, naechste_pruefung) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vorname, $nachname, $mitarbeiter_id, $fuehrerscheinklassen, $naechste_pruefung]);
    }

    header("Location: drivers_list.php");
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

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><?= $pageTitle ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="driver_form.php">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vorname *</label>
                            <input type="text" name="vorname" class="form-control" value="<?= htmlspecialchars($vorname) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nachname *</label>
                            <input type="text" name="nachname" class="form-control" value="<?= htmlspecialchars($nachname) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mitarbeiter ID *</label>
                            <input type="text" name="mitarbeiter_id" class="form-control" value="<?= htmlspecialchars($mitarbeiter_id) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">Nächste Führerscheinprüfung *</label>
                            <input type="date" name="naechste_pruefung" class="form-control" value="<?= htmlspecialchars($naechste_pruefung) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block fw-bold">Führerscheinklassen *</label>
                            <div class="border rounded p-3 bg-white">
                                <?php
                                $klassen_options = ['AM', 'A1', 'A2', 'A', 'B', 'BE', 'C1', 'C1E', 'C', 'CE', 'D1', 'D1E', 'D', 'DE'];
                                foreach ($klassen_options as $klasse):
                                    $checked = in_array($klasse, $saved_klassen) ? 'checked' : '';
                                    ?>
                                    <div class="form-check form-check-inline" style="width: 80px;">
                                        <input class="form-check-input" type="checkbox" name="klassen[]" value="<?= $klasse ?>" id="class_<?= $klasse ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="class_<?= $klasse ?>"><?= $klasse ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="drivers_list.php" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-dark"><?= $id ? 'Änderungen speichern' : 'Profil anlegen' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>