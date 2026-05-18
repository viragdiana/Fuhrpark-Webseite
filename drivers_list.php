<?php
require 'db.php';

$stmt = $pdo->query("SELECT * FROM fahrer");
$fahrer = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <!DOCTYPE html>
    <html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fahrerverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Fahrerverwaltung</h1>
        <div>
            <a href="index.php" class="btn btn-outline-secondary me-2">Zurück zu Fahrzeugen</a>
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
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($fahrer as $person): ?>
                    <tr>
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
                        <td>
                            <a href="driver_form.php?id=<?= $person['id'] ?>" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($fahrer)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Keine Fahrerprofile erfasst.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
    </html><?php
