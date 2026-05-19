<?php
require 'config/db.php';

$statusFilter = $_GET['status'] ?? 'Alle';
$currentMonth = date('Y-m');

if ($statusFilter === 'Warnungen') {
    // FIXED: Added Vignette check for expiration within 30 days
    $sql = "SELECT DISTINCT fahrzeuge.*, fahrer.vorname, fahrer.nachname 
            FROM fahrzeuge 
            LEFT JOIN fahrer ON fahrzeuge.fahrer_id = fahrer.id 
            LEFT JOIN vignette ON fahrzeuge.id = vignette.fahrzeug_id
            WHERE substr(fahrzeuge.naechster_tuev, 1, 7) = :currMonth 
               OR substr(fahrzeuge.naechster_service, 1, 7) = :currMonth
               OR fahrzeuge.naechster_tuev < date('now')
               OR fahrzeuge.naechster_service < date('now')
               OR (vignette.gueltig_bis BETWEEN date('now') AND date('now', '+30 days'))
               OR vignette.gueltig_bis < date('now')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['currMonth' => $currentMonth]);

} elseif ($statusFilter && $statusFilter !== 'Alle') {
    $sql = "SELECT fahrzeuge.*, fahrer.vorname, fahrer.nachname 
            FROM fahrzeuge 
            LEFT JOIN fahrer ON fahrzeuge.fahrer_id = fahrer.id 
            WHERE fahrzeuge.status = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$statusFilter]);
} else {
    $sql = "SELECT fahrzeuge.*, fahrer.vorname, fahrer.nachname 
            FROM fahrzeuge 
            LEFT JOIN fahrer ON fahrzeuge.fahrer_id = fahrer.id";
    $stmt = $pdo->query($sql);
}

$fahrzeuge = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuhrparkmanagement - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f5f6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .page-title {
            font-weight: 700;
            color: #0f172a;
        }
        .table-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<?php include 'components/navbar.php'; ?>

<div class="container py-5" style="max-width: 1300px;">

    <div class="mb-4 pb-2 border-bottom d-flex gap-3">
        <a href="index.php" class="text-decoration-none fw-bold text-primary border-bottom border-primary border-2 pb-2">Fahrzeuge</a>
        <a href="views/drivers/drivers_list.php" class="text-decoration-none text-secondary fw-medium pb-2">Fahrer</a>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h1 class="page-title h2 mb-0"><i class="bi bi-car-front me-2 text-primary"></i>Fuhrpark</h1>

        <div class="d-flex gap-3 align-items-center">
            <form method="GET" action="index.php" class="m-0">
                <select name="status" class="form-select shadow-sm border-0" onchange="this.form.submit()">
                    <option value="Alle" <?= $statusFilter == 'Alle' ? 'selected' : '' ?>>Alle Zustände</option>

                    <option value="Warnungen" <?= $statusFilter == 'Warnungen' ? 'selected' : '' ?> class="text-danger fw-bold">
                        ⚠️ Warnungen (Diesen Monat)
                    </option>

                    <option value="Aktiv" <?= $statusFilter == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
                    <option value="In Reparatur" <?= $statusFilter == 'In Reparatur' ? 'selected' : '' ?>>In Reparatur</option>
                    <option value="Ausgemustert" <?= $statusFilter == 'Ausgemustert' ? 'selected' : '' ?>>Ausgemustert</option>
                </select>
            </form>

            <a href="views/vehicles/vehicle_form.php" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Fahrzeug hinzufügen
            </a>
        </div>
    </div>

    <div class="table-card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-4">Kennzeichen</th>
                    <th>Typ</th>
                    <th>Marke / Modell</th>
                    <th>VIN</th>
                    <th>Zugewiesener Fahrer</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aktionen</th>
                </tr>
                </thead>
                <tbody class="border-top-0">
                <?php foreach ($fahrzeuge as $auto): ?>
                    <tr>
                        <td class="ps-4 fw-bold">
                            <a href="views/vehicles/vehicle_details.php?id=<?= $auto['id'] ?>" class="text-decoration-none text-primary">
                                <?= htmlspecialchars($auto['kennzeichen']) ?>
                            </a>
                        </td>
                        <td>
                            <i class="bi <?= $auto['fahrzeug_typ'] == 'Transporter' ? 'bi-truck' : 'bi-car-front' ?> text-secondary me-1"></i>
                            <?= htmlspecialchars($auto['fahrzeug_typ']) ?>
                        </td>
                        <td><?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?></td>
                        <td style="font-family: monospace; letter-spacing: 0.05em;" class="text-muted">
                            <?= htmlspecialchars($auto['vin']) ?>
                        </td>

                        <td>
                            <?php if (!empty($auto['vorname']) && !empty($auto['nachname'])): ?>
                                <span class="text-dark fw-medium">
                                        <i class="bi bi-person-check text-success me-1"></i>
                                        <?= htmlspecialchars($auto['vorname']) ?> <?= htmlspecialchars($auto['nachname']) ?>
                                    </span>
                            <?php else: ?>
                                <span class="text-muted small"><em>Nicht zugewiesen</em></span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php
                            $badgeClass = 'bg-secondary';

                            if ($auto['status'] == 'Aktiv') {
                                $badgeClass = 'bg-success-subtle text-success border border-success';
                            } elseif ($auto['status'] == 'In Reparatur') {
                                $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
                            } elseif ($auto['status'] == 'Ausgemustert') {
                                $badgeClass = 'bg-danger-subtle text-danger border border-danger';
                            }
                            ?>
                            <span class="badge rounded-pill px-2 py-1 <?= $badgeClass ?>"><?= htmlspecialchars($auto['status']) ?></span>
                        </td>

                        <td class="text-end pe-4">
                            <a href="views/vehicles/vehicle_details.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-light border text-primary" title="Details anzeigen">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="views/vehicles/vehicle_form.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-light border text-secondary" title="Bearbeiten">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <a href="views/vehicles/delete_vehicle.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-light border text-danger" title="Löschen" onclick="return confirm('Möchten Sie dieses Fahrzeug wirklich entfernen?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($fahrzeuge)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <div class="fs-1 text-light mb-2"><i class="bi bi-inbox"></i></div>
                            Es wurden keine Fahrzeuge gefunden, die den ausgewählten Filtern entsprechen.
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