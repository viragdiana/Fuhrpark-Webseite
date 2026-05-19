<?php
require '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT fahrzeuge.*, fahrer.vorname, fahrer.nachname 
        FROM fahrzeuge 
        LEFT JOIN fahrer ON fahrzeuge.fahrer_id = fahrer.id 
        WHERE fahrzeuge.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auto) {
    die("Fahrzeug nicht gefunden.");
}

$statusClass = 'bg-secondary';
if ($auto['status'] == 'Aktiv') $statusClass = 'bg-success-subtle text-success border border-success';
if ($auto['status'] == 'In Reparatur') $statusClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
if ($auto['status'] == 'Ausgemustert') $statusClass = 'bg-danger-subtle text-danger border border-danger';

$sofer = "Neasignat";
if (!empty($auto['vorname']) && !empty($auto['nachname'])) {
    $sofer = htmlspecialchars($auto['vorname']) . " vehicle_details.php" . htmlspecialchars($auto['nachname']);
}

$stmtWartung = $pdo->prepare("SELECT * FROM wartung WHERE fahrzeug_id = ? ORDER BY datum DESC");
$stmtWartung->execute([$id]);
$wartungen = $stmtWartung->fetchAll(PDO::FETCH_ASSOC);

$totalKosten = 0;
foreach ($wartungen as $w) {
    $totalKosten += $w['kosten'];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($auto['kennzeichen']) ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f3f0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .back-arrow {
            color: #1e293b;
            font-size: 1.5rem;
            text-decoration: none;
        }
        .page-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 2rem;
        }
        .info-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            border-bottom: 1px solid #f3f4f6;
        }
        .meta-label {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        .meta-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #111827;
        }
        .module-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            position: relative;
            transition: transform 0.15s ease-in-out;
        }
        .border-tuv { border-left: 4px solid #f97316 !important; }
        .border-wartung { border-left: 4px solid #94a3b8 !important; }
        .border-reifen { border-left: 4px solid #cbd5e1 !important; }
        .border-kfz { border-left: 4px solid #ef4444 !important; }

        .module-title {
            font-weight: 600;
            color: #111827;
            font-size: 1.15rem;
        }
        .module-subtitle {
            font-size: 0.85rem;
            color: #6b7280;
        }
        .alert-icon {
            color: #94a3b8;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="bg-light">

<?php include '../../components/navbar.php'; ?>

<div class="container py-4" style="max-width: 1200px;">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="../../index.php" class="back-arrow"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0"><?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?></h1>
    </div>

    <div class="info-card shadow-sm p-4 mb-4">
        <h2 class="section-title pb-3 mb-4">Fahrzeugdaten</h2>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="module-card shadow-sm p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <span class="fs-3 text-primary"><i class="bi bi-person-badge"></i></span>
                            <div>
                                <div class="module-title">Zugewiesener Fahrer</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto text-center py-3">
                        <?php if ($sofer === "Neasignat" || $sofer === "Nicht zugewiesen" || empty($auto['vorname'])): ?>
                            <div class="fs-1 text-light mb-2"><i class="bi bi-person-x"></i></div>
                            <p class="small text-muted mb-3">Kein aktiver Fahrer zugewiesen.</p>
                            <a href="../drivers/assign_driver.php?vehicle_id=<?= $auto['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-lg me-1"></i> Fahrer zuweisen
                            </a>
                        <?php else: ?>
                            <div class="fs-1 text-primary mb-2"><i class="bi bi-person-check-fill"></i></div>
                            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($auto['vorname']) . ' vehicle_details.php' . htmlspecialchars($auto['nachname']) ?></h4>
                            <div class="mt-3">
                                <a href="../drivers/assign_driver.php?vehicle_id=<?= $auto['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-left-right me-1"></i> Fahrer wechseln
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="meta-label">Kennzeichen</div>
                <div class="meta-value text-uppercase fw-bold"><?= htmlspecialchars($auto['kennzeichen']) ?></div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">VIN</div>
                <div class="meta-value" style="font-family: monospace; letter-spacing: 0.02em;"><?= htmlspecialchars($auto['vin']) ?></div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Typ</div>
                <div class="meta-value"><?= htmlspecialchars($auto['fahrzeug_typ']) ?></div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Marke</div>
                <div class="meta-value"><?= htmlspecialchars($auto['marke']) ?></div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Modell</div>
                <div class="meta-value"><?= htmlspecialchars($auto['modell']) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="module-card border-tuv shadow-sm p-4 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex gap-3">
                        <span class="fs-3 text-secondary"><i class="bi bi-file-earmark-text"></i></span>
                        <div>
                            <div class="module-title">TÜV</div>
                            <div class="module-subtitle">Technischer Überwachungsverein</div>
                        </div>
                    </div>
                    <span class="alert-icon"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="mt-2">
                    <div class="text-muted small">Ablaufdatum:</div>
                    <div class="fw-bold fs-5 text-dark">15.06.2026</div>
                    <div class="text-warning small mt-1">Noch 28 Tage</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="module-card border-wartung shadow-sm p-4 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex gap-3">
                        <span class="fs-3 text-secondary"><i class="bi bi-wrench"></i></span>
                        <div>
                            <div class="module-title">Regelmäßige Wartung</div>
                            <div class="module-subtitle">Vorbeugende Wartung</div>
                        </div>
                    </div>
                    <span class="alert-icon"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="mt-2">
                    <div class="text-muted small">Empfohlene Dienstleistung:</div>
                    <div class="fw-bold fs-5 text-dark">01.06.2026</div>
                    <div class="text-muted small">oder alle 15.000 km</div>
                    <div class="text-muted small mt-1" style="color: #94a3b8 !important;">Noch 800 km</div>
                </div>
            </div>
        </div>

                <div class="col-md-6">
                    <div class="module-card border-reifen shadow-sm p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="d-flex gap-3">
                                <span class="fs-3 text-secondary"><i class="bi bi-speedometer2"></i></span>
                                <div>
                                    <div class="module-title">Reifenzustand</div>
                                    <div class="module-subtitle">Allgemeiner Verschleiß</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-auto">
                            <div class="text-muted small">Verbleibende Kapazität:</div>
                            <div class="fw-bold fs-3 text-dark">75%</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="module-card border-kfz shadow-sm p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex gap-3">
                                <span class="fs-3 text-secondary"><i class="bi bi-file-earmark-medical"></i></span>
                                <div>
                                    <div class="module-title">Kfz-Haftpflichtversicherung</div>
                                    <div class="module-subtitle">Allianz</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="text-muted small">Versicherungspolice: RCA123456</div>
                            <div class="text-muted small">Gültig bis: 20.05.2026</div>
                        </div>
                    </div>
                </div>

            </div>
    <div class="col-12 mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-journal-text me-2 text-primary"></i>Service- & Reparaturhistorie
                </h5>
                <a href="../service/service_form.php?vehicle_id=<?= $auto['id'] ?>" class="btn btn-sm btn-dark shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Wartung protokollieren
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-4">Datum</th>
                            <th>Werkstatt</th>
                            <th>Art der Reparatur</th>
                            <th class="text-end pe-4">Kosten</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($wartungen as $w): ?>
                            <tr>
                                <td class="ps-4"><?= date('d.m.Y', strtotime($w['datum'])) ?></td>
                                <td><i class="bi bi-shop text-muted me-2"></i><?= htmlspecialchars($w['werkstatt']) ?></td>
                                <td><?= htmlspecialchars($w['reparatur_typ']) ?></td>
                                <td class="text-end pe-4 fw-bold"><?= number_format($w['kosten'], 2, ',', '.') ?> €</td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($wartungen)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="bi bi-wrench fs-2 text-light d-block mb-2"></i>
                                    Keine historischen Service-Einträge vorhanden.
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr class="table-light border-top-2">
                                <td colspan="3" class="text-end fw-bold text-uppercase text-muted" style="font-size: 0.85rem;">Gesamtkosten:</td>
                                <td class="text-end pe-4 fw-bold fs-5 text-dark"><?= number_format($totalKosten, 2, ',', '.') ?> €</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
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
