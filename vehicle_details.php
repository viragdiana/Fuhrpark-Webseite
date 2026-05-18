<?php
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM fahrzeuge WHERE id = ?");
$stmt->execute([$id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auto) {
    die("Fahrzeug nicht gefunden.");
}

$statusClass = 'bg-secondary';
if ($auto['status'] == 'Aktiv') $statusClass = 'bg-success';
if ($auto['status'] == 'In Reparatur') $statusClass = 'bg-warning text-dark';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($auto['kennzeichen']) ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?>
            <span class="text-muted">(<?= htmlspecialchars($auto['kennzeichen']) ?>)</span>
        </h2>
        <div>
            <span class="badge <?= $statusClass ?> fs-6 me-3"><?= htmlspecialchars($auto['status']) ?></span>
            <a href="index.php" class="btn btn-outline-secondary">Zurück zum Dashboard</a>
            <a href="vehicle_form.php?id=<?= $auto['id'] ?>" class="btn btn-primary">Bearbeiten</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">
                    Fahrzeugdaten
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Kennzeichen:</strong> <?= htmlspecialchars($auto['kennzeichen']) ?></li>
                    <li class="list-group-item"><strong>Typ:</strong> <?= htmlspecialchars($auto['fahrzeug_typ']) ?></li>
                    <li class="list-group-item"><strong>Marke:</strong> <?= htmlspecialchars($auto['marke']) ?></li>
                    <li class="list-group-item"><strong>Modell:</strong> <?= htmlspecialchars($auto['modell']) ?></li>
                    <li class="list-group-item" style="font-family: monospace;"><strong>VIN:</strong> <?= htmlspecialchars($auto['vin']) ?></li>
                </ul>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row">

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100 border-primary">
                        <div class="card-header bg-primary text-white fw-bold">
                            Aktueller Fahrer
                        </div>
                        <div class="card-body text-center text-muted d-flex flex-column justify-content-center">
                            <p class="mb-2"><em>Noch kein Fahrer zugewiesen.</em></p>
                            <button class="btn btn-sm btn-outline-primary mt-auto" disabled>+ Fahrer zuweisen</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100 border-info">
                        <div class="card-header bg-info text-dark fw-bold">
                            Reifenstatus
                        </div>
                        <div class="card-body text-center text-muted d-flex flex-column justify-content-center">
                            <p class="mb-0"><em>Keine Reifensätze erfasst.</em></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-warning">
                        <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center">
                            <span>Service & Wartungshistorie</span>
                            <button class="btn btn-sm btn-dark" disabled>+ Eintrag hinzufügen</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 text-center">
                                <tr>
                                    <td class="text-muted py-3"><em>Keine Service-Einträge vorhanden.</em></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>
