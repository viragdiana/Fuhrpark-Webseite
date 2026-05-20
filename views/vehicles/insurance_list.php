<?php
require '../../config/db.php';

$sql = "SELECT v.*, f.kennzeichen, f.marke, f.modell 
        FROM versicherung v 
        JOIN fahrzeuge f ON v.fahrzeug_id = f.id 
        ORDER BY v.ablaufdatum ASC";
$insurances = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$totalPolicies = count($insurances);
$expiredCount = 0;
$expiringSoonCount = 0;
$cancellationSoonCount = 0;
$today = time();
$warningLimit = strtotime('+30 days');

foreach ($insurances as $ins) {
    $ablaufTs = strtotime($ins['ablaufdatum']);
    $kuendigungTs = strtotime($ins['kuendigungsfrist']);

    if ($ablaufTs < $today) {
        $expiredCount++;
    } elseif ($ablaufTs <= $warningLimit) {
        $expiringSoonCount++;
    }

    if ($kuendigungTs >= $today && $kuendigungTs <= $warningLimit) {
        $cancellationSoonCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versicherungen verwalten</title>

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
        <h1 class="text-[28px] font-bold text-foreground">Versicherungen</h1>
        <a href="../vehicles/select_vehicle.php?action=insurance" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-primary-foreground hover:opacity-90 px-4 py-2.5 text-sm font-medium rounded-md shadow-sm transition-opacity">
            <i class="bi bi-plus-lg"></i> Versicherung hinzufügen
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-muted flex items-center justify-center text-muted-foreground text-2xl shrink-0">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Gesamt Policen</div>
                <div class="text-3xl font-bold text-foreground"><?= $totalPolicies ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-destructive/10 flex items-center justify-center text-destructive text-2xl shrink-0">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
            <div>
                <div class="text-sm text-destructive font-medium">Bereits abgelaufen</div>
                <div class="text-3xl font-bold text-destructive"><?= $expiredCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-[#f59e0b]/10 flex items-center justify-center text-[#f59e0b] text-2xl shrink-0">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Läuft bald ab (< 30T)</div>
                <div class="text-3xl font-bold text-foreground"><?= $expiringSoonCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-[#E8E5DF] flex items-center justify-center text-[#6b6761] text-2xl border border-[#d4cfc7] shrink-0">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Kündigungsfrist naht</div>
                <div class="text-3xl font-bold text-foreground"><?= $cancellationSoonCount ?></div>
            </div>
        </div>

    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-border">
            <h2 class="text-xl font-bold text-foreground">Versicherungsliste</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-muted-foreground uppercase bg-background/50 border-b border-border tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium">Fahrzeug</th>
                    <th class="px-6 py-4 font-medium">Typ</th>
                    <th class="px-6 py-4 font-medium">Gesellschaft</th>
                    <th class="px-6 py-4 font-medium">Policen-Nr.</th>
                    <th class="px-6 py-4 font-medium">Gültigkeit</th>
                    <th class="px-6 py-4 font-medium text-right">Aktion</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-border">
                <?php foreach ($insurances as $ins):
                    $ablaufTs = strtotime($ins['ablaufdatum']);
                    $isExpired = $ablaufTs < $today;
                    $daysDiff = ceil(($ablaufTs - $today) / 86400);

                    $statusColor = 'text-muted-foreground';
                    $statusIcon = 'bi-calendar';
                    $statusText = $daysDiff . ' Tage verbleibend';

                    if ($isExpired) {
                        $statusColor = 'text-destructive font-bold';
                        $statusIcon = 'bi-x-circle';
                        $statusText = 'ABGELAUFEN';
                    } elseif ($daysDiff <= 30) {
                        $statusColor = 'text-[#f59e0b] font-medium';
                        $statusIcon = 'bi-exclamation-triangle';
                    }
                    ?>
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-5">
                            <a href="../vehicles/vehicle_details.php?id=<?= $ins['fahrzeug_id'] ?>" class="font-bold text-foreground hover:text-primary transition-colors block">
                                <?= htmlspecialchars($ins['marke']) ?> <?= htmlspecialchars($ins['modell']) ?>
                            </a>
                            <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                <?= htmlspecialchars($ins['kennzeichen']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                                <span class="bg-[#E8E5DF] text-[#3d3a35] px-3 py-1 rounded-full text-xs font-bold tracking-wide border border-[#d4cfc7]">
                                    <?= htmlspecialchars($ins['deckungsart']) ?>
                                </span>
                        </td>
                        <td class="px-6 py-5 text-foreground flex items-center gap-2 mt-2">
                            <i class="bi bi-building text-muted-foreground"></i>
                            <?= htmlspecialchars($ins['gesellschaft']) ?>
                        </td>
                        <td class="px-6 py-5 font-medium text-foreground">
                            <?= htmlspecialchars($ins['police_nr']) ?>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-foreground">Bis: <?= date('d.m.Y', $ablaufTs) ?></div>
                            <div class="text-xs mt-1 flex items-center gap-1 <?= $statusColor ?>">
                                <i class="bi <?= $statusIcon ?>"></i> <?= $statusText ?>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="../vehicles/insurance_form.php?vehicle_id=<?= $ins['fahrzeug_id'] ?>" class="text-muted-foreground hover:text-primary p-2 rounded-md transition-colors" title="Bearbeiten">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>