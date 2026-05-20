<?php
require '../../config/db.php';

$today = time();
$alerts = [
    'critical' => [],
    'warning' => [],
    'info' => []
];

function formatVehicle($v) {
    return htmlspecialchars($v['marke'] . ' ' . $v['modell']) . ' (' . htmlspecialchars($v['kennzeichen']) . ')';
}

$vehicles = $pdo->query("SELECT id, marke, modell, kennzeichen, naechster_tuev, naechster_service FROM fahrzeuge")->fetchAll(PDO::FETCH_ASSOC);
foreach ($vehicles as $v) {
    $vehName = formatVehicle($v);

    foreach (['naechster_tuev' => 'Hauptuntersuchung (TÜV)', 'naechster_service' => 'Service / Inspektion'] as $key => $label) {
        if (empty($v[$key])) continue;
        $ts = strtotime($v[$key]);
        $days = ceil(($ts - $today) / 86400);
        $dateStr = date('d.m.Y', $ts);

        $item = ['category' => 'Wartung', 'vehicle' => $vehName, 'vid' => $v['id'], 'date' => $dateStr];

        if ($days <= 0) {
            $item['message'] = "$label ist ABGELAUFEN";
            $alerts['critical'][] = $item;
        } elseif ($days <= 30) {
            $item['message'] = "$label läuft in $days Tagen ab";
            $alerts['warning'][] = $item;
        } elseif ($days <= 60) {
            $item['message'] = "$label läuft in $days Tagen ab";
            $alerts['info'][] = $item;
        }
    }
}

$insurances = $pdo->query("SELECT v.ablaufdatum, v.deckungsart, f.id, f.marke, f.modell, f.kennzeichen FROM versicherung v JOIN fahrzeuge f ON v.fahrzeug_id = f.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($insurances as $ins) {
    if (empty($ins['ablaufdatum'])) continue;
    $ts = strtotime($ins['ablaufdatum']);
    $days = ceil(($ts - $today) / 86400);
    $item = ['category' => 'Versicherung', 'vehicle' => formatVehicle($ins), 'vid' => $ins['id'], 'date' => date('d.m.Y', $ts)];

    if ($days <= 0) {
        $item['message'] = "Police ({$ins['deckungsart']}) ist ABGELAUFEN";
        $alerts['critical'][] = $item;
    } elseif ($days <= 30) {
        $item['message'] = "Police ({$ins['deckungsart']}) läuft in $days Tagen ab";
        $alerts['warning'][] = $item;
    } elseif ($days <= 60) {
        $item['message'] = "Police ({$ins['deckungsart']}) läuft in $days Tagen ab";
        $alerts['info'][] = $item;
    }
}

$vignettes = $pdo->query("SELECT v.gueltig_bis, v.land, f.id, f.marke, f.modell, f.kennzeichen FROM vignette v JOIN fahrzeuge f ON v.fahrzeug_id = f.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($vignettes as $vig) {
    if (empty($vig['gueltig_bis'])) continue;
    $ts = strtotime($vig['gueltig_bis']);
    $days = ceil(($ts - $today) / 86400);
    $item = ['category' => 'Vignette', 'vehicle' => formatVehicle($vig), 'vid' => $vig['id'], 'date' => date('d.m.Y', $ts)];

    if ($days <= 0) {
        $item['message'] = "Vignette ({$vig['land']}) ist ABGELAUFEN";
        $alerts['critical'][] = $item;
    } elseif ($days <= 30) {
        $item['message'] = "Vignette ({$vig['land']}) läuft in $days Tagen ab";
        $alerts['warning'][] = $item;
    }
}

$tires = $pdo->query("SELECT r.profiltiefe, r.saison, f.id, f.marke, f.modell, f.kennzeichen FROM reifen r JOIN fahrzeuge f ON r.fahrzeug_id = f.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tires as $tire) {
    $item = ['category' => 'Reifen', 'vehicle' => formatVehicle($tire), 'vid' => $tire['id'], 'date' => date('d.m.Y')];
    if ($tire['profiltiefe'] < 1.6) {
        $item['message'] = "{$tire['saison']}reifen Profiltiefe kritisch ({$tire['profiltiefe']} mm)";
        $alerts['critical'][] = $item;
    } elseif ($tire['profiltiefe'] <= 3.0) {
        $item['message'] = "{$tire['saison']}reifen Profiltiefe gering ({$tire['profiltiefe']} mm)";
        $alerts['warning'][] = $item;
    }
}

$countCrit = count($alerts['critical']);
$countWarn = count($alerts['warning']);
$countInfo = count($alerts['info']);
$totalAlerts = $countCrit + $countWarn + $countInfo;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warnzentrum</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <style type="text/tailwindcss">
        @theme inline {
            --font-sans: 'Outfit', sans-serif;
            --color-background: #F5F3F0;
            --color-foreground: #3d3a35;
            --color-card: #ffffff;
            --color-primary: #968F83;
            --color-primary-foreground: #ffffff;
            --color-secondary: #A5A58D;
            --color-muted: #E8E5DF;
            --color-muted-foreground: #6b6761;
            --color-destructive: #c75146;
            --color-border: #d4cfc7;
        }
        @layer base {
            body { @apply bg-background text-foreground font-sans antialiased; }
        }
    </style>
</head>
<body>

<?php include '../../components/navbar.php'; ?>

<main class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <div class="mb-8">
        <h1 class="text-[28px] font-bold text-foreground">Warnzentrum</h1>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">

        <div class="bg-card rounded-xl p-5 shadow-sm border border-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-muted flex items-center justify-center text-muted-foreground text-xl">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium tracking-wider mb-0.5">Gesamt Alarme</div>
                <div class="text-2xl font-bold text-foreground"><?= $totalAlerts ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-5 shadow-sm border border-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-destructive/10 flex items-center justify-center text-destructive text-xl">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium tracking-wider mb-0.5">Kritisch</div>
                <div class="text-2xl font-bold text-foreground"><?= $countCrit ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-5 shadow-sm border border-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-secondary/20 flex items-center justify-center text-secondary-foreground text-xl">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium tracking-wider mb-0.5">Warnungen</div>
                <div class="text-2xl font-bold text-foreground"><?= $countWarn ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-5 shadow-sm border border-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#E8E5DF] flex items-center justify-center text-[#6b6761] text-xl border border-[#d4cfc7]">
                <i class="bi bi-info-circle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium tracking-wider mb-0.5">Informativ</div>
                <div class="text-2xl font-bold text-foreground"><?= $countInfo ?></div>
            </div>
        </div>

    </div>

    <?php if ($countCrit > 0): ?>
        <div class="mb-10">
            <div class="bg-destructive/10 border-t-2 border-destructive px-5 py-4 rounded-t-xl flex items-center gap-2">
                <i class="bi bi-exclamation-octagon text-destructive text-lg"></i>
                <h2 class="text-lg font-bold text-destructive">Kritische Alarme <span class="font-normal text-sm ml-1">(<?= $countCrit ?> Alarme)</span></h2>
            </div>
            <div class="bg-card border border-t-0 border-border rounded-b-xl p-5 shadow-sm space-y-3">
                <?php foreach ($alerts['critical'] as $alert): ?>
                    <div class="bg-destructive/5 border border-destructive/20 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-exclamation-circle text-destructive mt-0.5"></i>
                            <div>
                                <a href="views/vehicles/vehicle_details.php?id=<?= $alert['vid'] ?>" class="font-bold text-foreground hover:text-primary transition-colors flex items-center gap-2">
                                    <i class="bi bi-car-front text-muted-foreground"></i> <?= $alert['vehicle'] ?>
                                </a>
                                <div class="text-sm font-medium text-foreground mt-1"><?= $alert['message'] ?></div>
                                <div class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                    <i class="bi bi-calendar3"></i> Datum: <?= $alert['date'] ?>
                                </div>
                            </div>
                        </div>
                        <span class="bg-muted text-muted-foreground px-3 py-1 rounded-full text-xs font-bold tracking-wide self-start sm:self-auto shrink-0 border border-border">
                        <?= $alert['category'] ?>
                    </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($countWarn > 0): ?>
        <div class="mb-10">
            <div class="bg-secondary/10 border-t-2 border-secondary px-5 py-4 rounded-t-xl flex items-center gap-2">
                <i class="bi bi-exclamation-triangle text-secondary-foreground text-lg"></i>
                <h2 class="text-lg font-bold text-secondary-foreground">Warnungen <span class="font-normal text-sm ml-1">(<?= $countWarn ?> Alarme)</span></h2>
            </div>
            <div class="bg-card border border-t-0 border-border rounded-b-xl p-5 shadow-sm space-y-3">
                <?php foreach ($alerts['warning'] as $alert): ?>
                    <div class="bg-secondary/5 border border-secondary/20 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-exclamation-triangle text-secondary-foreground mt-0.5"></i>
                            <div>
                                <a href="views/vehicles/vehicle_details.php?id=<?= $alert['vid'] ?>" class="font-bold text-foreground hover:text-primary transition-colors flex items-center gap-2">
                                    <i class="bi bi-car-front text-muted-foreground"></i> <?= $alert['vehicle'] ?>
                                </a>
                                <div class="text-sm font-medium text-foreground mt-1"><?= $alert['message'] ?></div>
                                <div class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                    <i class="bi bi-calendar3"></i> Fällig: <?= $alert['date'] ?>
                                </div>
                            </div>
                        </div>
                        <span class="bg-muted text-muted-foreground px-3 py-1 rounded-full text-xs font-bold tracking-wide self-start sm:self-auto shrink-0 border border-border">
                        <?= $alert['category'] ?>
                    </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($totalAlerts === 0): ?>
        <div class="bg-card border border-border rounded-xl p-12 text-center text-muted-foreground shadow-sm">
            <i class="bi bi-shield-check text-5xl mb-4 block text-primary/50"></i>
            <h2 class="text-xl font-bold text-foreground mb-2">Alles im grünen Bereich!</h2>
            <p>Aktuell gibt es keine kritischen Warnungen oder fälligen Fristen in Ihrem Fuhrpark.</p>
        </div>
    <?php endif; ?>

</main>

</body>
</html>