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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($auto['kennzeichen']) ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f3f0
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

<div class="container py-4" style="max-width: 1200px;">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="index.php" class="back-arrow"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0"><?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?></h1>
    </div>

    <div class="info-card shadow-sm p-4 mb-4">
        <h2 class="section-title pb-3 mb-4">Fahrzeugdaten</h2>

        <div class="row g-4">
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
        </div>
    </div>
</div>

</body>
</html>
