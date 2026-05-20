<?php
require '../../config/db.php';

$sqlDrivers = "
    SELECT dr.*, f.id AS vehicle_id, f.marke, f.modell, f.kennzeichen 
    FROM fahrer dr 
    LEFT JOIN fahrzeuge f ON dr.id = f.fahrer_id
";
$fahrer = $pdo->query($sqlDrivers)->fetchAll(PDO::FETCH_ASSOC);

$sqlUnassigned = "
    SELECT id, marke, modell, kennzeichen 
    FROM fahrzeuge 
    WHERE status = 'Aktiv' AND (fahrer_id IS NULL OR fahrer_id = 0)
";
$unassignedVehicles = $pdo->query($sqlUnassigned)->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fahrerverwaltung</title>

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
        <h1 class="text-[28px] font-bold text-foreground">Fahrer</h1>
        <a href="driver_form.php" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary text-primary-foreground hover:opacity-90 px-4 py-2.5 text-sm font-medium rounded-md shadow-sm transition-opacity">
            <i class="bi bi-plus-lg"></i> Fahrer hinzufügen
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <?php foreach ($fahrer as $person):
            $isOverdue = ($person['naechste_pruefung'] < $today);
            $formattedDate = date('d.m.Y', strtotime($person['naechste_pruefung']));
            ?>
            <div class="bg-card border border-border rounded-xl shadow-sm p-6 flex flex-col relative group transition-shadow hover:shadow-md">

                <div class="absolute top-4 right-4 flex gap-1">
                    <a href="driver_form.php?id=<?= $person['id'] ?>" class="text-muted-foreground hover:text-primary p-2 rounded-md transition-colors" title="Bearbeiten">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="delete_driver.php?id=<?= $person['id'] ?>" class="text-muted-foreground hover:text-destructive hover:bg-destructive/10 p-2 rounded-md transition-colors" title="Löschen" onclick="return confirm('Möchten Sie diesen Fahrer wirklich entfernen?');">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center text-muted-foreground text-2xl shrink-0">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight text-foreground">
                            <?= htmlspecialchars($person['vorname']) ?> <?= htmlspecialchars($person['nachname']) ?>
                        </h3>
                        <p class="text-sm text-muted-foreground mt-0.5">ID: <?= htmlspecialchars($person['mitarbeiter_id']) ?></p>
                    </div>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-3 text-sm text-foreground">
                        <i class="bi bi-card-heading text-muted-foreground w-4 text-center"></i>
                        <span class="text-muted-foreground mr-1">Klassen:</span>
                        <span class="font-medium"><?= htmlspecialchars($person['fuehrerscheinklassen']) ?: '-' ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-foreground">
                        <i class="bi bi-calendar-check text-muted-foreground w-4 text-center"></i>
                        <span class="text-muted-foreground mr-1">Prüfung:</span>
                        <span class="font-medium <?= $isOverdue ? 'text-destructive' : '' ?>">
                            <?= $formattedDate ?>
                            <?= $isOverdue ? '<i class="bi bi-exclamation-triangle-fill ml-1" title="Überfällig!"></i>' : '' ?>
                        </span>
                    </div>
                </div>

                <div class="mt-auto border-t border-border pt-4">
                    <div class="text-xs text-muted-foreground mb-1 flex items-center gap-2">
                        <i class="bi bi-car-front"></i> Zugewiesenes Fahrzeug:
                    </div>
                    <?php if ($person['vehicle_id']): ?>
                        <div class="group relative">
                            <a href="../vehicles/vehicle_details.php?id=<?= $person['vehicle_id'] ?>" class="block hover:bg-muted/30 -mx-2 px-2 py-1 rounded transition-colors">
                                <div class="font-bold text-foreground text-sm">
                                    <?= htmlspecialchars($person['marke']) ?> <?= htmlspecialchars($person['modell']) ?>
                                </div>
                                <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                    <?= htmlspecialchars($person['kennzeichen']) ?>
                                </div>
                            </a>
                            <a href="unassign_driver.php?vehicle_id=<?= $person['vehicle_id'] ?>&return_to=drivers"
                               class="absolute top-1/2 -translate-y-1/2 right-0 text-destructive hover:bg-destructive/10 p-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity"
                               title="Zuweisung aufheben"
                               onclick="return confirm('Zuweisung wirklich aufheben?');">
                                <i class="bi bi-person-x"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="font-medium text-foreground text-sm italic">Keines</div>
                        <a href="assign_driver.php?fahrer_id=<?= $person['id'] ?>" class="text-xs text-primary hover:underline mt-0.5 inline-block">Jetzt zuweisen</a>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>

        <?php if (empty($fahrer)): ?>
            <div class="col-span-full bg-card border border-border rounded-xl p-12 text-center text-muted-foreground">
                <i class="bi bi-people text-4xl mb-3 block"></i>
                Keine Fahrerprofile gefunden.
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm p-6 mb-8">
        <h2 class="text-xl font-bold text-foreground mb-6">Nicht zugewiesene Fahrzeuge</h2>

        <?php if (empty($unassignedVehicles)): ?>
            <div class="text-sm text-muted-foreground italic bg-muted/20 p-4 rounded-lg">
                <i class="bi bi-check-circle text-primary mr-2"></i> Alle aktiven Fahrzeuge sind derzeit einem Fahrer zugewiesen.
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($unassignedVehicles as $uv): ?>
                    <div class="bg-background border border-border rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-colors hover:border-primary/30 hover:shadow-sm">

                        <div class="flex items-center gap-4">
                            <i class="bi bi-car-front text-2xl text-muted-foreground"></i>
                            <div>
                                <a href="../vehicles/vehicle_details.php?id=<?= $uv['id'] ?>" class="font-bold text-foreground hover:text-primary transition-colors">
                                    <?= htmlspecialchars($uv['marke']) ?> <?= htmlspecialchars($uv['modell']) ?>
                                </a>
                                <div class="text-xs text-muted-foreground uppercase mt-0.5 tracking-wide">
                                    <?= htmlspecialchars($uv['kennzeichen']) ?>
                                </div>
                            </div>
                        </div>

                        <a href="assign_driver.php?vehicle_id=<?= $uv['id'] ?>" class="flex items-center gap-2 text-sm font-medium text-primary hover:text-foreground transition-colors shrink-0">
                            <i class="bi bi-person-plus"></i> Fahrer zuweisen
                        </a>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>