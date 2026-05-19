<?php
require '../../config/db.php';

$stmt = $pdo->query("SELECT * FROM fahrer");
$fahrer = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fahrerverwaltung - Compliance Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Fahrerverwaltung</h1>
        <div>
            <a href="../../index.php" class="btn btn-outline-secondary me-2">Zurück zu Fahrzeugen</a>
            <a href="driver_form.php" class="btn btn-dark">+ Neuen Fahrer anlegen</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Mitarbeiter ID</th>
                    <th>Name</th>
                    <th>Führerscheinklassen</th>
                    <th>Nächste Prüfung</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($fahrer as $person):
                    $isOverdue = ($person['naechste_pruefung'] < $today);
                    $formattedDate = date('d.m.Y', strtotime($person['naechste_pruefung']));
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger-subtle' : '' ?>">
                        <td><?= htmlspecialchars($person['id']) ?></td>
                        <td class="fw-bold" style="font-family: monospace;"><?= htmlspecialchars($person['mitarbeiter_id']) ?></td>
                        <td><?= htmlspecialchars($person['nachname']) ?>, <?= htmlspecialchars($person['vorname']) ?></td>
                        <td>
                            <?php
                            $klassen = explode(', ', $person['fuehrerscheinklassen']);
                            foreach ($klassen as $k): if(empty($k)) continue;
                                ?>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($k) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?= $formattedDate ?></td>
                        <td>
                            <?php if ($isOverdue): ?>
                                <span class="badge bg-danger px-2 py-1.5 shadow-sm text-uppercase">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Fällig / Überfällig
                                    </span>
                            <?php else: ?>
                                <span class="badge bg-success px-2 py-1.5 text-uppercase">
                                        <i class="bi bi-check-circle-fill me-1"></i> Gültig
                                    </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="driver_form.php?id=<?= $person['id'] ?>" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($fahrer)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Keine Fahrerprofile erfasst.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>