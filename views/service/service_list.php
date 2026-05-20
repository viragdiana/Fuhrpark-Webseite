<?php
require '../../config/db.php';

$stats = $pdo->query("
    SELECT COUNT(w.id) as total_count, 
           SUM(w.kosten) as total_cost, 
           AVG(w.kosten) as avg_cost 
    FROM wartung w
    JOIN fahrzeuge f ON w.fahrzeug_id = f.id
")->fetch(PDO::FETCH_ASSOC);

$totalInterventions = $stats['total_count'] ?: 0;
$totalCost = $stats['total_cost'] ?: 0;
$avgCost = $stats['avg_cost'] ?: 0;

$sqlRecent = "
    SELECT w.*, f.marke, f.modell, f.kennzeichen 
    FROM wartung w 
    JOIN fahrzeuge f ON w.fahrzeug_id = f.id 
    ORDER BY w.datum DESC 
    LIMIT 20
";
$recentServices = $pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC);

$sqlVehicles = "
    SELECT f.id, f.marke, f.modell, f.kennzeichen, 
           COUNT(w.id) as intervention_count, 
           MAX(w.datum) as last_service, 
           SUM(w.kosten) as total_vehicle_cost 
    FROM fahrzeuge f 
    LEFT JOIN wartung w ON f.id = w.fahrzeug_id 
    GROUP BY f.id 
    ORDER BY total_vehicle_cost DESC, f.kennzeichen ASC
";
$vehicleCosts = $pdo->query($sqlVehicles)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service-Historie</title>

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
        <h1 class="text-[28px] font-bold text-foreground">Service-Historie</h1>
        <a href="../vehicles/select_vehicle.php?action=service" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-primary-foreground hover:opacity-90 px-4 py-2.5 text-sm font-medium rounded-md shadow-sm transition-opacity">
            <i class="bi bi-plus-lg"></i> Wartung hinzufügen
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 shrink-0 rounded-xl bg-muted flex items-center justify-center text-muted-foreground text-2xl">
                <i class="bi bi-wrench"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm text-muted-foreground font-medium truncate">Gesamt Interventionen</div>
                <div class="text-3xl font-bold text-foreground truncate"><?= $totalInterventions ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 shrink-0 rounded-xl bg-[#E8E5DF] flex items-center justify-center text-[#6b6761] text-2xl border border-[#d4cfc7]">
                <i class="bi bi-currency-euro"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm text-muted-foreground font-medium truncate">Gesamtkosten</div>
                <div class="text-3xl font-bold text-foreground truncate" title="<?= number_format($totalCost, 2, ',', '.') ?> €">
                    <?= number_format($totalCost, 2, ',', '.') ?> €
                </div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 shrink-0 rounded-xl bg-[#E8E5DF] flex items-center justify-center text-[#6b6761] text-2xl border border-[#d4cfc7]">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm text-muted-foreground font-medium truncate">Durchschnittskosten</div>
                <div class="text-3xl font-bold text-foreground truncate" title="<?= number_format($avgCost, 2, ',', '.') ?> €">
                    <?= number_format($avgCost, 2, ',', '.') ?> €
                </div>
            </div>
        </div>

    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm mb-10 overflow-hidden">
        <div class="px-6 py-5 border-b border-border">
            <h2 class="text-xl font-bold text-foreground">Letzte Interventionen</h2>
        </div>

        <div class="p-6 space-y-4 bg-card">
            <?php if (empty($recentServices)): ?>
                <div class="text-center text-muted-foreground py-8">
                    <i class="bi bi-wrench text-4xl mb-3 block text-border"></i>
                    Es wurden noch keine Service-Einträge protokolliert.
                </div>
            <?php else: ?>
                <?php foreach ($recentServices as $service): ?>
                    <div class="bg-[#F5F3F0] border-l-4 border-[#968F83] rounded-r-lg p-5 flex flex-col gap-2 transition-shadow hover:shadow-sm">
                        <div class="flex items-center gap-3 text-sm text-muted-foreground">
                            <span class="flex items-center gap-1 font-medium text-foreground">
                                <i class="bi bi-calendar3"></i> <?= date('d.m.Y', strtotime($service['datum'])) ?>
                            </span>
                            <span>•</span>
                            <span>Wartungsprotokoll</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                                <i class="bi bi-wrench text-muted-foreground text-sm"></i>
                                <?= htmlspecialchars($service['reparatur_typ']) ?>
                            </h3>
                            <div class="text-sm text-muted-foreground mt-1">
                                Fahrzeug: <a href="../vehicles/vehicle_details.php?id=<?= $service['fahrzeug_id'] ?>" class="text-foreground font-medium hover:text-primary transition-colors"><?= htmlspecialchars($service['marke']) ?> <?= htmlspecialchars($service['modell']) ?> (<?= htmlspecialchars($service['kennzeichen']) ?>)</a>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-2 text-sm font-medium">
                            <span class="flex items-center gap-1.5 text-muted-foreground">
                                <i class="bi bi-building"></i> <?= htmlspecialchars($service['werkstatt']) ?>
                            </span>
                            <span class="flex items-center gap-1 text-foreground">
                                <i class="bi bi-currency-euro text-muted-foreground"></i> <?= number_format($service['kosten'], 2, ',', '.') ?> €
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-border">
            <h2 class="text-xl font-bold text-foreground">Kosten pro Fahrzeug</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-muted-foreground uppercase bg-background/50 border-b border-border tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium">Fahrzeug</th>
                    <th class="px-6 py-4 font-medium text-center">Interventionen</th>
                    <th class="px-6 py-4 font-medium text-center">Letzter Service</th>
                    <th class="px-6 py-4 font-medium text-right">Gesamtkosten</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-border">
                <?php foreach ($vehicleCosts as $vc):
                    $hasService = $vc['intervention_count'] > 0;
                    ?>
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4">
                            <a href="../vehicles/vehicle_details.php?id=<?= $vc['id'] ?>" class="font-bold text-foreground hover:text-primary transition-colors block">
                                <?= htmlspecialchars($vc['marke']) ?> <?= htmlspecialchars($vc['modell']) ?>
                            </a>
                            <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                <?= htmlspecialchars($vc['kennzeichen']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-medium text-foreground">
                            <?= $vc['intervention_count'] ?>
                        </td>
                        <td class="px-6 py-4 text-center text-muted-foreground">
                            <?= $hasService ? date('d.m.Y', strtotime($vc['last_service'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-foreground">
                            <?= $hasService ? number_format($vc['total_vehicle_cost'], 2, ',', '.') . ' €' : '0,00 €' ?>
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