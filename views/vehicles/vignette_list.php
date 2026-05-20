<?php
require '../../config/db.php';

$today = time();
$warningLimit = strtotime('+30 days');

$sqlVig = "SELECT v.*, f.kennzeichen, f.marke, f.modell 
           FROM vignette v 
           JOIN fahrzeuge f ON v.fahrzeug_id = f.id 
           ORDER BY v.gueltig_bis ASC";
$vignetten = $pdo->query($sqlVig)->fetchAll(PDO::FETCH_ASSOC);

$sqlNoVig = "SELECT id, marke, modell, kennzeichen 
             FROM fahrzeuge 
             WHERE status = 'Aktiv' AND id NOT IN (
                 SELECT fahrzeug_id FROM vignette WHERE gueltig_bis >= date('now')
             )";
$noVigVehicles = $pdo->query($sqlNoVig)->fetchAll(PDO::FETCH_ASSOC);

$activeCount = 0;
$expiredCount = 0;
$expiringSoonCount = 0;
$noVigCount = count($noVigVehicles);

foreach ($vignetten as $vig) {
    $bisTs = strtotime($vig['gueltig_bis']);

    if ($bisTs < $today) {
        $expiredCount++;
    } else {
        $activeCount++;
        if ($bisTs <= $warningLimit) {
            $expiringSoonCount++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vignetten & Maut</title>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>favicon.png?v=1">

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

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <h1 class="text-[28px] font-bold text-foreground">Vignetten</h1>
        <a href="select_vehicle.php?action=vignette" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-primary-foreground hover:opacity-90 px-4 py-2.5 text-sm font-medium rounded-md shadow-sm transition-opacity">
            <i class="bi bi-plus-lg"></i> Vignette hinzufügen
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-muted flex items-center justify-center text-muted-foreground text-2xl shrink-0">
                <i class="bi bi-ticket-perforated"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Aktive Vignetten</div>
                <div class="text-3xl font-bold text-foreground"><?= $activeCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-destructive/10 flex items-center justify-center text-destructive text-2xl shrink-0">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
            <div>
                <div class="text-sm text-destructive font-medium">Abgelaufen</div>
                <div class="text-3xl font-bold text-destructive"><?= $expiredCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-[#f59e0b]/10 flex items-center justify-center text-[#f59e0b] text-2xl shrink-0">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Läuft bald ab (< 30T)</div>
                <div class="text-3xl font-bold text-foreground"><?= $expiringSoonCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-[#E8E5DF] flex items-center justify-center text-[#6b6761] text-2xl border border-[#d4cfc7] shrink-0">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Ohne gültige Vignette</div>
                <div class="text-3xl font-bold text-foreground"><?= $noVigCount ?></div>
            </div>
        </div>

    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-border">
            <h2 class="text-xl font-bold text-foreground">Alle Vignetten-Historie</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-muted-foreground uppercase bg-background/50 border-b border-border tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium">Fahrzeug</th>
                    <th class="px-6 py-4 font-medium">Land & Typ</th>
                    <th class="px-6 py-4 font-medium">Gültigkeit</th>
                    <th class="px-6 py-4 font-medium text-right">Status</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-border">
                <?php foreach ($vignetten as $vig):
                    $bisTs = strtotime($vig['gueltig_bis']);
                    $isExpired = $bisTs < $today;
                    $daysDiff = ceil(($bisTs - $today) / 86400);

                    $statusColor = 'text-primary';
                    $statusIcon = 'bi-check-circle';
                    $statusText = 'Aktiv';
                    $dateColor = 'text-muted-foreground';

                    if ($isExpired) {
                        $statusColor = 'text-destructive font-bold';
                        $statusIcon = 'bi-x-circle';
                        $statusText = 'Abgelaufen';
                        $dateColor = 'text-destructive font-medium';
                    } elseif ($daysDiff <= 30) {
                        $statusColor = 'text-[#f59e0b]';
                        $statusIcon = 'bi-exclamation-triangle';
                        $statusText = 'Achtung';
                        $dateColor = 'text-[#f59e0b] font-medium';
                    }
                    ?>
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-5">
                            <a href="vehicle_details.php?id=<?= $vig['fahrzeug_id'] ?>" class="font-bold text-foreground hover:text-primary transition-colors block">
                                <?= htmlspecialchars($vig['marke']) ?> <?= htmlspecialchars($vig['modell']) ?>
                            </a>
                            <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                <?= htmlspecialchars($vig['kennzeichen']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-foreground flex items-center gap-2 font-medium">
                                <i class="bi bi-geo-alt text-muted-foreground"></i>
                                <?= htmlspecialchars($vig['land']) ?>
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                                <?= htmlspecialchars($vig['vignetten_typ']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-foreground font-medium">
                                <?= date('d.m.Y', strtotime($vig['gueltig_von'])) ?> - <?= date('d.m.Y', $bisTs) ?>
                            </div>
                            <div class="text-xs mt-1 flex items-center gap-1 <?= $dateColor ?>">
                                <i class="bi bi-calendar3"></i>
                                <?= $isExpired ? 'Gültigkeit abgelaufen' : $daysDiff . ' Tage verbleibend' ?>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right font-medium <?= $statusColor ?>">
                            <i class="bi <?= $statusIcon ?> mr-1"></i> <?= $statusText ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-border">
            <h2 class="text-xl font-bold text-foreground">Fahrzeuge ohne gültige Vignette</h2>
        </div>
        <div class="p-6 space-y-3">
            <?php if (empty($noVigVehicles)): ?>
                <div class="text-sm text-muted-foreground italic bg-muted/20 p-4 rounded-lg">
                    <i class="bi bi-check-circle text-primary mr-2"></i> Alle aktiven Fahrzeuge verfügen über eine gültige Vignette.
                </div>
            <?php else: ?>
                <?php foreach ($noVigVehicles as $uv): ?>
                    <div class="bg-muted/30 border border-border rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-colors hover:shadow-sm">
                        <div class="flex items-center gap-4">
                            <i class="bi bi-exclamation-triangle text-2xl text-muted-foreground"></i>
                            <div>
                                <div class="font-bold text-foreground">
                                    <?= htmlspecialchars($uv['marke']) ?> <?= htmlspecialchars($uv['modell']) ?>
                                </div>
                                <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                    <?= htmlspecialchars($uv['kennzeichen']) ?>
                                </div>
                            </div>
                        </div>
                        <a href="vignette_form.php?vehicle_id=<?= $uv['id'] ?>" class="flex items-center gap-2 text-sm font-medium bg-[#968F83] text-white hover:bg-[#8a8270] transition-colors px-4 py-2 rounded-md shrink-0">
                            Vignette hinzufügen
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>