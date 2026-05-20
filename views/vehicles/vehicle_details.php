<?php
require '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../index.php");
    exit();
}

$id = $_GET['id'];
$today = time();

$sql = "SELECT f.*, d.vorname, d.nachname 
        FROM fahrzeuge f 
        LEFT JOIN fahrer d ON f.fahrer_id = d.id 
        WHERE f.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auto) die("Fahrzeug nicht gefunden.");

$sofer = (!empty($auto['vorname']) && !empty($auto['nachname']))
        ? htmlspecialchars($auto['vorname'] . ' ' . $auto['nachname'])
        : "Nicht zugewiesen";

$stmtWartung = $pdo->prepare("SELECT * FROM wartung WHERE fahrzeug_id = ? ORDER BY datum DESC");
$stmtWartung->execute([$id]);
$wartungen = $stmtWartung->fetchAll(PDO::FETCH_ASSOC);

$stmtReifen = $pdo->prepare("SELECT * FROM reifen WHERE fahrzeug_id = ? ORDER BY saison DESC");
$stmtReifen->execute([$id]);
$reifenSaetze = $stmtReifen->fetchAll(PDO::FETCH_ASSOC);

$lowestTread = 8.0;
$tireHealth = 0;
$tireBarColor = 'bg-muted-foreground';
$tireTextColor = 'text-foreground';

if (!empty($reifenSaetze)) {
    foreach ($reifenSaetze as $r) {
        if ($r['profiltiefe'] < $lowestTread) $lowestTread = $r['profiltiefe'];
    }

    $tireHealth = max(0, min(100, round(($lowestTread / 8.0) * 100)));

    if ($lowestTread < 1.6) {
        $tireBarColor = 'bg-destructive';
        $tireTextColor = 'text-destructive';
    } elseif ($lowestTread <= 3.0) {
        $tireBarColor = 'bg-[#f59e0b]';
        $tireTextColor = 'text-[#f59e0b]';
    } else {
        $tireBarColor = 'bg-primary';
        $tireTextColor = 'text-foreground';
    }
}

$stmtIns = $pdo->prepare("SELECT * FROM versicherung WHERE fahrzeug_id = ?");
$stmtIns->execute([$id]);
$versicherung = $stmtIns->fetch(PDO::FETCH_ASSOC);

$stmtVig = $pdo->prepare("SELECT * FROM vignette WHERE fahrzeug_id = ? ORDER BY gueltig_bis ASC");
$stmtVig->execute([$id]);
$vignetten = $stmtVig->fetchAll(PDO::FETCH_ASSOC);

function getDaysRemaining($dateStr) {
    if (empty($dateStr)) return null;
    $diff = strtotime($dateStr) - time();
    return ceil($diff / 86400);
}

$tuevDays = getDaysRemaining($auto['naechster_tuev']);
$serviceDays = getDaysRemaining($auto['naechster_service']);
$insDays = $versicherung ? getDaysRemaining($versicherung['ablaufdatum']) : null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($auto['kennzeichen']) ?> - Details</title>

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

<main class="max-w-5xl mx-auto px-4 py-6">

    <div class="flex items-center gap-4 mb-6">
        <a href="../../index.php" class="text-foreground hover:text-primary transition-colors text-xl">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-foreground">
            <?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?>
        </h1>
    </div>

    <div class="bg-card rounded-xl shadow-sm border border-border mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-border bg-card">
            <h2 class="text-lg font-bold text-foreground">Fahrzeugdaten</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-4">
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Kennzeichen</div>
                    <div class="font-bold text-foreground text-base"><?= htmlspecialchars($auto['kennzeichen']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">VIN</div>
                    <div class="font-medium text-foreground text-sm font-mono tracking-wide"><?= htmlspecialchars($auto['vin']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Fahrzeugtyp</div>
                    <div class="font-medium text-foreground text-base"><?= htmlspecialchars($auto['fahrzeug_typ']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1 flex justify-between items-center pr-4">
                        <span>Zugewiesener Fahrer</span>
                        <a href="../drivers/assign_driver.php?vehicle_id=<?= $auto['id'] ?>" class="text-primary hover:underline"><i class="bi bi-pencil"></i></a>
                    </div>
                    <div class="font-medium text-foreground text-base flex items-center gap-2">
                        <?php if ($sofer === "Nicht zugewiesen"): ?>
                            <i class="bi bi-person-x text-muted-foreground"></i> <span class="text-muted-foreground italic">Nicht zugewiesen</span>
                        <?php else: ?>
                            <i class="bi bi-person-check text-primary"></i> <?= $sofer ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div class="bg-card rounded-xl shadow-sm border border-border p-5 flex flex-col relative border-l-4 border-l-[#f59e0b]">
            <?php if ($tuevDays !== null && $tuevDays <= 30): ?>
                <i class="bi bi-exclamation-triangle absolute top-5 right-5 text-[#f59e0b] text-lg"></i>
            <?php endif; ?>

            <div class="flex items-center gap-3 mb-4">
                <i class="bi bi-file-earmark-text text-2xl text-muted-foreground"></i>
                <div>
                    <h3 class="font-bold text-base text-foreground leading-tight">TÜV</h3>
                    <p class="text-xs text-muted-foreground">Hauptuntersuchung</p>
                </div>
            </div>

            <div class="mt-auto">
                <div class="text-xs text-muted-foreground mb-0.5">Ablaufdatum:</div>
                <div class="font-bold text-foreground text-lg mb-1">
                    <?= !empty($auto['naechster_tuev']) ? date('d.m.Y', strtotime($auto['naechster_tuev'])) : 'Nicht hinterlegt' ?>
                </div>
                <?php if ($tuevDays !== null): ?>
                    <div class="text-xs font-medium <?= $tuevDays < 0 ? 'text-destructive' : 'text-muted-foreground' ?>">
                        <?= $tuevDays < 0 ? 'ABGELAUFEN!' : "$tuevDays Tage verbleibend" ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-card rounded-xl shadow-sm border border-border p-5 flex flex-col relative border-l-4 border-l-primary">
            <?php if ($serviceDays !== null && $serviceDays <= 30): ?>
                <i class="bi bi-exclamation-triangle absolute top-5 right-5 text-primary text-lg"></i>
            <?php endif; ?>

            <div class="flex items-center gap-3 mb-4">
                <i class="bi bi-wrench text-2xl text-muted-foreground"></i>
                <div>
                    <h3 class="font-bold text-base text-foreground leading-tight">Regelmäßige Wartung</h3>
                    <p class="text-xs text-muted-foreground">Vorbeugende Wartung</p>
                </div>
            </div>

            <div class="mt-auto">
                <div class="text-xs text-muted-foreground mb-0.5">Empfohlener Service:</div>
                <div class="font-bold text-foreground text-lg mb-1">
                    <?= !empty($auto['naechster_service']) ? date('d.m.Y', strtotime($auto['naechster_service'])) : 'Nicht hinterlegt' ?>
                </div>
                <?php if ($serviceDays !== null): ?>
                    <div class="text-xs font-medium <?= $serviceDays < 0 ? 'text-destructive' : 'text-muted-foreground' ?>">
                        <?= $serviceDays < 0 ? 'ÜBERFÄLLIG!' : "$serviceDays Tage verbleibend" ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-card rounded-xl shadow-sm border border-border p-5 flex flex-col relative border-l-4 border-l-secondary relative group">
            <a href="tire_form.php?vehicle_id=<?= $auto['id'] ?>" class="absolute top-5 right-5 text-muted-foreground hover:text-primary transition-colors opacity-0 group-hover:opacity-100">
                <i class="bi bi-pencil"></i>
            </a>

            <div class="flex items-center gap-3 mb-8">
                <i class="bi bi-speedometer2 text-2xl text-muted-foreground"></i>
                <div>
                    <h3 class="font-bold text-base text-foreground leading-tight">Reifenzustand</h3>
                    <p class="text-xs text-muted-foreground">Allgemeiner Verschleiß</p>
                </div>
            </div>

            <div class="mt-auto">
                <div class="flex justify-between items-end mb-2">
                    <div class="text-xs text-muted-foreground">Verbleibende Kapazität:</div>
                    <div class="font-bold text-xl <?= $tireTextColor ?>"><?= empty($reifenSaetze) ? '-' : $tireHealth ?>%</div>
                </div>
                <div class="w-full bg-muted rounded-full h-2.5 overflow-hidden">
                    <div class="<?= $tireBarColor ?> h-2.5 rounded-full transition-all duration-500 ease-in-out" style="width: <?= $tireHealth ?>%"></div>
                </div>
                <?php if (empty($reifenSaetze)): ?>
                    <p class="text-xs text-muted-foreground mt-2 italic">Noch keine Reifensätze angelegt.</p>
                <?php elseif ($lowestTread < 1.6): ?>
                    <p class="text-xs text-destructive font-bold mt-2">Kritische Profiltiefe erreicht!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-card rounded-xl shadow-sm border border-border p-5 flex flex-col relative border-l-4 border-l-destructive relative group">
            <a href="insurance_form.php?vehicle_id=<?= $auto['id'] ?>" class="absolute top-5 right-5 text-muted-foreground hover:text-primary transition-colors opacity-0 group-hover:opacity-100">
                <i class="bi bi-pencil"></i>
            </a>

            <div class="flex items-center gap-3 mb-4">
                <i class="bi bi-file-earmark-medical text-2xl text-muted-foreground"></i>
                <div>
                    <h3 class="font-bold text-base text-foreground leading-tight">Versicherung</h3>
                    <p class="text-xs text-muted-foreground"><?= $versicherung ? htmlspecialchars($versicherung['gesellschaft']) : 'Keine hinterlegt' ?></p>
                </div>
            </div>

            <div class="mt-auto">
                <?php if ($versicherung): ?>
                    <div class="text-xs text-muted-foreground mb-0.5">Police: <?= htmlspecialchars($versicherung['police_nr']) ?> (<?= htmlspecialchars($versicherung['deckungsart']) ?>)</div>
                    <div class="font-bold text-foreground text-lg mb-1">Ablauf: <?= date('d.m.Y', strtotime($versicherung['ablaufdatum'])) ?></div>
                    <div class="text-xs font-medium <?= $insDays < 0 ? 'text-destructive' : ($insDays <= 30 ? 'text-[#f59e0b]' : 'text-muted-foreground') ?>">
                        <?= $insDays < 0 ? 'ABGELAUFEN!' : "$insDays Tage verbleibend" ?>
                    </div>
                <?php else: ?>
                    <div class="text-sm font-medium text-muted-foreground italic mt-4">Bitte Versicherungsdaten ergänzen.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-card rounded-xl shadow-sm border border-border p-5 flex flex-col relative border-l-4 border-l-[#10b981] md:col-span-2 relative group">
            <a href="vignette_form.php?vehicle_id=<?= $auto['id'] ?>" class="absolute top-5 right-5 text-muted-foreground hover:text-primary transition-colors opacity-0 group-hover:opacity-100">
                <i class="bi bi-plus-lg"></i>
            </a>

            <div class="flex items-center gap-3 mb-4">
                <i class="bi bi-globe-europe-africa text-2xl text-muted-foreground"></i>
                <div>
                    <h3 class="font-bold text-base text-foreground leading-tight">Vignetten & Maut</h3>
                    <p class="text-xs text-muted-foreground">Aktive Auslandzulassungen</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <?php foreach ($vignetten as $vig):
                    $vDays = getDaysRemaining($vig['gueltig_bis']);
                    ?>
                    <div class="bg-background border border-border rounded-lg p-3">
                        <div class="font-bold text-sm text-foreground"><?= htmlspecialchars($vig['land']) ?> <span class="font-normal text-muted-foreground">| <?= htmlspecialchars($vig['vignetten_typ']) ?></span></div>
                        <div class="text-xs text-muted-foreground mt-1">Bis: <?= date('d.m.Y', strtotime($vig['gueltig_bis'])) ?></div>
                        <div class="text-xs font-medium mt-1 <?= $vDays < 0 ? 'text-destructive' : ($vDays <= 30 ? 'text-[#f59e0b]' : 'text-[#10b981]') ?>">
                            <?= $vDays < 0 ? 'Abgelaufen' : "$vDays Tage verbleibend" ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($vignetten)): ?>
                    <div class="text-sm text-muted-foreground italic py-2">Keine aktiven Vignetten.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border bg-card flex justify-between items-center">
            <h2 class="text-lg font-bold text-foreground">Service-Historie</h2>
            <a href="../service/service_form.php?vehicle_id=<?= $auto['id'] ?>" class="text-sm font-medium text-primary hover:underline">
                <i class="bi bi-plus-lg mr-1"></i>Eintrag hinzufügen
            </a>
        </div>

        <div class="p-6 space-y-4 bg-[#F5F3F0]/50">
            <?php foreach ($wartungen as $w): ?>
                <div class="bg-card border border-border rounded-lg p-4 flex flex-col sm:flex-row justify-between gap-4 transition-shadow hover:shadow-sm">
                    <div>
                        <div class="text-xs text-muted-foreground mb-1 flex items-center gap-2">
                            <i class="bi bi-calendar3"></i> <?= date('d.m.Y', strtotime($w['datum'])) ?>
                        </div>
                        <div class="font-bold text-foreground text-base flex items-center gap-2">
                            <i class="bi bi-wrench text-muted-foreground text-sm"></i> <?= htmlspecialchars($w['reparatur_typ']) ?>
                        </div>
                        <div class="text-sm text-muted-foreground mt-1 flex items-center gap-2">
                            <i class="bi bi-building"></i> <?= htmlspecialchars($w['werkstatt']) ?>
                        </div>
                    </div>
                    <div class="sm:text-right font-bold text-foreground self-start sm:self-center bg-muted/50 px-3 py-1.5 rounded-md border border-border">
                        <?= number_format($w['kosten'], 2, ',', '.') ?> €
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($wartungen)): ?>
                <div class="text-center py-6 text-muted-foreground">
                    <i class="bi bi-inbox text-3xl mb-2 block"></i>
                    Noch keine Service-Einträge vorhanden.
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

</body>
</html>