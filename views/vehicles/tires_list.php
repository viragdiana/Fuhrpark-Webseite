<?php
require '../../config/db.php';

$sql = "SELECT r.*, f.kennzeichen, f.marke AS auto_marke, f.modell AS auto_modell 
        FROM reifen r 
        JOIN fahrzeuge f ON r.fahrzeug_id = f.id 
        ORDER BY f.kennzeichen, r.saison";
$allTires = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$totalSets = count($allTires);
$criticalCount = 0;
$attentionCount = 0;
$groupedTires = [];

foreach ($allTires as $tire) {
    if ($tire['profiltiefe'] < 1.6) {
        $criticalCount++;
    } elseif ($tire['profiltiefe'] <= 3.0) {
        $attentionCount++;
    }

    $groupedTires[$tire['fahrzeug_id']]['vehicle'] = [
            'id' => $tire['fahrzeug_id'],
            'marke' => $tire['auto_marke'],
            'modell' => $tire['auto_modell'],
            'kennzeichen' => $tire['kennzeichen']
    ];
    $groupedTires[$tire['fahrzeug_id']]['tires'][] = $tire;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reifensätze verwalten</title>

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
        <h1 class="text-[28px] font-bold text-foreground">Reifensätze</h1>
        <a href="select_vehicle.php?action=tire" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-primary-foreground hover:opacity-90 px-4 py-2.5 text-sm font-medium rounded-md shadow-sm transition-opacity">
            <i class="bi bi-plus-lg"></i> Neuen Reifensatz anlegen
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-muted flex items-center justify-center text-muted-foreground text-2xl">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Gesamt Reifensätze</div>
                <div class="text-3xl font-bold text-foreground"><?= $totalSets ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-destructive/10 flex items-center justify-center text-destructive text-2xl">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Kritische Profiltiefe</div>
                <div class="text-3xl font-bold text-foreground"><?= $criticalCount ?></div>
            </div>
        </div>

        <div class="bg-card rounded-xl p-6 shadow-sm border border-border flex items-center gap-5">
            <div class="w-14 h-14 rounded-xl bg-secondary/20 flex items-center justify-center text-secondary-foreground text-2xl">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="text-sm text-muted-foreground font-medium">Benötigt Aufmerksamkeit</div>
                <div class="text-3xl font-bold text-foreground"><?= $attentionCount ?></div>
            </div>
        </div>

    </div>

    <div class="space-y-8">
        <?php foreach ($groupedTires as $group):
            $veh = $group['vehicle'];
            ?>
            <div class="bg-card rounded-xl shadow-sm overflow-hidden border border-border">

                <div class="bg-[#E8E5DF]/50 px-6 py-4 border-b border-border flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-car-front text-muted-foreground text-xl"></i>
                        <h2 class="text-lg font-bold text-foreground">
                            <?= htmlspecialchars($veh['marke']) ?> <?= htmlspecialchars($veh['modell']) ?>
                            <span class="text-muted-foreground font-normal text-sm ml-2">(<?= htmlspecialchars($veh['kennzeichen']) ?>)</span>
                        </h2>
                    </div>
                    <a href="vehicle_details.php?id=<?= $veh['id'] ?>" class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                        Fahrzeugdetails
                    </a>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($group['tires'] as $tire):
                        // Determine status colors based on depth
                        $depthColor = 'text-[#8a8270]';
                        $iconClass = 'bi-check-circle-fill text-primary';

                        if ($tire['profiltiefe'] < 1.6) {
                            $depthColor = 'text-destructive font-bold';
                            $iconClass = 'bi-exclamation-triangle-fill text-destructive';
                        } elseif ($tire['profiltiefe'] <= 3.0) {
                            $depthColor = 'text-secondary-foreground font-bold';
                            $iconClass = 'bi-exclamation-triangle-fill text-secondary-foreground';
                        }

                        // Determine badges (Dark for mounted, light for storage)
                        $isMounted = stripos($tire['lagerort'], 'montiert') !== false;
                        ?>
                        <div class="bg-[#F5F3F0] border border-[#d4cfc7] rounded-xl p-5 relative group flex flex-col justify-between">

                            <div class="flex gap-2 mb-3">
                                <?php if ($isMounted): ?>
                                    <span class="bg-[#8a8270] text-white px-3 py-0.5 rounded-full text-xs font-medium tracking-wide">Montiert</span>
                                <?php else: ?>
                                    <span class="bg-[#E8E5DF] text-[#3d3a35] px-3 py-0.5 rounded-full text-xs font-medium tracking-wide border border-[#d4cfc7]">Lager</span>
                                <?php endif; ?>

                                <span class="bg-[#E8E5DF] text-[#3d3a35] px-3 py-0.5 rounded-full text-xs font-medium tracking-wide border border-[#d4cfc7]">
                                    <?= htmlspecialchars($tire['saison']) ?>
                                </span>
                            </div>

                            <a href="tire_form.php?vehicle_id=<?= $veh['id'] ?>" class="absolute top-5 right-5 text-muted-foreground hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <div>
                                <h3 class="text-[17px] font-bold text-foreground leading-tight">
                                    <?= htmlspecialchars($tire['marke']) ?>
                                </h3>
                                <div class="text-sm text-muted-foreground mt-1">
                                    Lagerort: <?= htmlspecialchars($tire['lagerort']) ?>
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-[#d4cfc7]/70 flex items-end justify-between">
                                <div>
                                    <div class="text-muted-foreground text-xs mb-0.5 uppercase tracking-wide font-medium">Profiltiefe</div>
                                    <div class="font-bold <?= $depthColor ?> text-xl">
                                        <?= number_format($tire['profiltiefe'], 1, ',', '.') ?> mm
                                    </div>
                                </div>
                                <i class="bi <?= $iconClass ?> text-2xl opacity-90" title="Reifenzustand"></i>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php endforeach; ?>

        <?php if (empty($groupedTires)): ?>
            <div class="bg-card border border-border rounded-xl p-12 text-center text-muted-foreground shadow-sm">
                <i class="bi bi-record-circle text-4xl mb-3 block"></i>
                Keine Reifensätze im System gefunden.
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>