<?php
require 'db.php';

$statusFilter = $_GET['status'] ?? '';

if ($statusFilter && $statusFilter !== 'Alle') {
    $stmt = $pdo->prepare("SELECT * FROM fahrzeuge WHERE status = ?");
    $stmt->execute([$statusFilter]);
} else {
    $stmt = $pdo->query("SELECT * FROM fahrzeuge");
}

$fahrzeuge = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fuhrparkmanagement - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <h1 class="mb-4">Fahrzeugverwaltung (Dashboard)</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="vehicle_form.php" class="btn btn-success">+ Neues Fahrzeug hinzufügen</a>

        <form method="GET" action="index.php" class="d-flex align-items-center">
            <label class="me-2 fw-bold text-secondary">Filter:</label>
            <select name="status" class="form-select shadow-sm" onchange="this.form.submit()" style="width: auto;">
                <option value="Alle" <?= $statusFilter == 'Alle' ? 'selected' : '' ?>>Alle Fahrzeuge</option>
                <option value="Aktiv" <?= $statusFilter == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
                <option value="In Reparatur" <?= $statusFilter == 'In Reparatur' ? 'selected' : '' ?>>In Reparatur</option>
                <option value="Ausgemustert" <?= $statusFilter == 'Ausgemustert' ? 'selected' : '' ?>>Ausgemustert</option>
            </select>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Kennzeichen</th>
                    <th>Typ</th>
                    <th>Marke</th>
                    <th>Modell</th>
                    <th>Fahrgestellnummer (VIN)</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($fahrzeuge as $auto): ?>
                    <tr>
                        <td><?= htmlspecialchars($auto['id']) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($auto['kennzeichen']) ?></td>
                        <td><?= htmlspecialchars($auto['fahrzeug_typ']) ?></td>
                        <td><?= htmlspecialchars($auto['marke']) ?></td>
                        <td><?= htmlspecialchars($auto['modell']) ?></td>
                        <td style="font-family: monospace; text-transform: uppercase;">
                            <?= htmlspecialchars($auto['vin']) ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = 'bg-secondary';
                            if ($auto['status'] == 'Aktiv') $statusClass = 'bg-success';
                            if ($auto['status'] == 'In Reparatur') $statusClass = 'bg-warning text-dark';
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                    <?= htmlspecialchars($auto['status']) ?>
                                </span>
                        </td>
                        <td>
                            <a href="vehicle_form.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                            <a href="delete_vehicle.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Fahrzeug wirklich löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($fahrzeuge)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <?= $statusFilter && $statusFilter !== 'Alle'
                                    ? "Keine Fahrzeuge mit dem Status '" . htmlspecialchars($statusFilter) . "' gefunden."
                                    : 'Keine Fahrzeuge im System. Klicken Sie auf "Neues Fahrzeug hinzufügen", um zu beginnen.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>