<?php
require 'config/db.php';
require 'config/auth.php';

$statusFilter = $_GET['status'] ?? 'Alle';
$currentMonth = date('Y-m');

$totalVehicles = $pdo->query("SELECT COUNT(*) FROM fahrzeuge")->fetchColumn();
$totalDrivers = $pdo->query("SELECT COUNT(*) FROM fahrer")->fetchColumn();

$criticalCount = 0; $warningCount = 0; $alertMessages = [];
$today = time(); $warnLimit = strtotime('+30 days');

$checkWarning = function($date, $label, $type) use (&$criticalCount, &$warningCount, &$alertMessages, $today, $warnLimit) {
    if (!$date) return;
    $ts = strtotime($date);
    if ($ts < $today) {
        $criticalCount++;
        $alertMessages[] = "$label: $type ABGELAUFEN";
    } elseif ($ts <= $warnLimit) {
        $warningCount++;
        $days = ceil(($ts - $today) / 86400);
        $alertMessages[] = "$label: $type in $days Tagen";
    }
};

$allVehicles = $pdo->query("SELECT f.id, f.kennzeichen, f.naechster_tuev, f.naechster_service, 
                                   v.ablaufdatum, vig.gueltig_bis, vig.vignetten_typ 
                            FROM fahrzeuge f 
                            LEFT JOIN versicherung v ON f.id = v.fahrzeug_id 
                            LEFT JOIN vignette vig ON f.id = vig.fahrzeug_id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($allVehicles as $v) {
    $kz = $v['kennzeichen'];
    $checkWarning($v['naechster_tuev'], $kz, 'TÜV');
    $checkWarning($v['naechster_service'], $kz, 'Service');
    $checkWarning($v['ablaufdatum'], $kz, 'Versicherung');
    $checkWarning($v['gueltig_bis'], $kz, $v['vignetten_typ'] ?? 'Vignette');
}

$sql = "SELECT DISTINCT f.*, dr.vorname, dr.nachname 
        FROM fahrzeuge f 
        LEFT JOIN fahrer dr ON f.fahrer_id = dr.id 
        LEFT JOIN vignette vig ON f.id = vig.fahrzeug_id ";

if ($statusFilter === 'Warnungen') {
    $sql .= "WHERE substr(f.naechster_tuev, 1, 7) = :m OR substr(f.naechster_service, 1, 7) = :m 
             OR f.naechster_tuev < date('now') OR f.naechster_service < date('now') 
             OR (vig.gueltig_bis BETWEEN date('now') AND date('now', '+30 days')) OR vig.gueltig_bis < date('now')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['m' => $currentMonth]);
} else {
    $sql .= ($statusFilter !== 'Alle') ? "WHERE f.status = ?" : "";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($statusFilter !== 'Alle' ? [$statusFilter] : []);
}
$fahrzeuge = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuhrparkmanagement - Dashboard</title>

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

<?php include 'components/navbar.php'; ?>

<main class="max-w-[1300px] mx-auto px-4 py-6">

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold">Fuhrpark Dashboard</h1>
        <a href="views/vehicles/vehicle_form.php" class="bg-primary text-white px-4 py-2 text-sm font-medium rounded-md shadow-sm">
            <i class="bi bi-plus-lg mr-1"></i> Fahrzeug hinzufügen
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php
        $kpis = [
                ['Gesamt Fahrzeuge', $totalVehicles, 'bi-car-front', 'bg-muted text-muted-foreground'],
                ['Kritische Alarme', $criticalCount, 'bi-exclamation-circle', 'bg-destructive/10 text-destructive'],
                ['Warnungen', $warningCount, 'bi-exclamation-triangle', 'bg-secondary/20 text-secondary'],
                ['Aktive Fahrer', $totalDrivers, 'bi-person', 'bg-muted text-muted-foreground']
        ];
        foreach ($kpis as $k): ?>
            <div class="bg-card rounded-xl p-5 shadow-sm border border-border flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg <?= $k[3] ?> flex items-center justify-center text-2xl"><i class="bi <?= $k[2] ?>"></i></div>
                <div>
                    <div class="text-sm text-muted-foreground font-medium"><?= $k[0] ?></div>
                    <div class="text-3xl font-bold"><?= $k[1] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($alertMessages)): ?>
        <div class="bg-destructive/5 border-l-4 border-destructive rounded-r-xl p-5 mb-8 shadow-sm">
            <div class="flex items-start gap-3">
                <i class="bi bi-exclamation-circle text-destructive text-2xl"></i>
                <div>
                    <h3 class="font-bold">Achtung: Wartung / Fristen</h3>
                    <p class="text-sm text-muted-foreground mb-2"><?= count($alertMessages) ?> Fahrzeuge benötigen Aufmerksamkeit.</p>
                    <ul class="space-y-1 text-sm">
                        <?php foreach (array_slice($alertMessages, 0, 5) as $msg): ?>
                            <li><span class="text-muted-foreground">→</span> <?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                        <?php if (count($alertMessages) > 5): ?>
                            <li class="text-muted-foreground italic mt-1">... und <?= count($alertMessages) - 5 ?> weitere.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex justify-end bg-card/50">
            <form method="GET" class="m-0">
                <select name="status" class="border border-border rounded-md p-2 text-sm focus:ring-2 focus:ring-primary outline-none cursor-pointer" onchange="this.form.submit()">
                    <option value="Alle" <?= $statusFilter == 'Alle' ? 'selected' : '' ?>>Alle Zustände</option>
                    <option value="Warnungen" <?= $statusFilter == 'Warnungen' ? 'selected' : '' ?> class="text-destructive font-medium">⚠️ Warnungen</option>
                    <option value="Aktiv" <?= $statusFilter == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
                    <option value="In Reparatur" <?= $statusFilter == 'In Reparatur' ? 'selected' : '' ?>>In Reparatur</option>
                    <option value="Ausgemustert" <?= $statusFilter == 'Ausgemustert' ? 'selected' : '' ?>>Ausgemustert</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/30 border-b border-border">
                <tr>
                    <th class="px-6 py-4">Kennzeichen</th>
                    <th class="px-6 py-4">Typ</th>
                    <th class="px-6 py-4">Marke / Modell</th>
                    <th class="px-6 py-4">VIN</th>
                    <th class="px-6 py-4">Fahrer</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aktionen</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-border">
                <?php foreach ($fahrzeuge as $auto): ?>
                    <tr class="hover:bg-muted/30">
                        <td class="px-6 py-4 font-bold"><a href="views/vehicles/vehicle_details.php?id=<?= $auto['id'] ?>" class="text-primary hover:underline"><?= htmlspecialchars($auto['kennzeichen']) ?></a></td>
                        <td class="px-6 py-4"><i class="bi <?= $auto['fahrzeug_typ'] == 'Transporter' ? 'bi-truck' : 'bi-car-front' ?> text-muted-foreground mr-1"></i> <?= htmlspecialchars($auto['fahrzeug_typ']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($auto['marke']) ?> <?= htmlspecialchars($auto['modell']) ?></td>
                        <td class="px-6 py-4 font-mono text-muted-foreground text-xs"><?= htmlspecialchars($auto['vin']) ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($auto['vorname'])): ?>
                                <span class="font-medium"><i class="bi bi-person-check text-primary mr-1"></i> <?= htmlspecialchars($auto['vorname'] . ' ' . $auto['nachname']) ?></span>
                            <?php else: ?>
                                <span class="text-muted-foreground italic text-xs">Nicht zugewiesen</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $badge = ['bg-muted text-muted-foreground', 'border-border'];
                            if ($auto['status'] == 'Aktiv') $badge = ['bg-primary/10 text-primary', 'border-primary/20'];
                            if ($auto['status'] == 'In Reparatur') $badge = ['bg-secondary/20', 'border-secondary/40'];
                            if ($auto['status'] == 'Ausgemustert') $badge = ['bg-destructive/10 text-destructive', 'border-destructive/20'];
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border <?= implode(' ', $badge) ?>"><?= htmlspecialchars($auto['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="views/vehicles/vehicle_details.php?id=<?= $auto['id'] ?>" class="p-1.5 rounded border border-border hover:bg-muted text-primary" title="Details"><i class="bi bi-eye"></i></a>
                            <a href="views/vehicles/vehicle_form.php?id=<?= $auto['id'] ?>" class="p-1.5 rounded border border-border hover:bg-muted ml-1" title="Bearbeiten"><i class="bi bi-pencil"></i></a>
                            <a href="views/vehicles/delete_vehicle.php?id=<?= $auto['id'] ?>" class="p-1.5 rounded border border-border hover:bg-destructive/10 text-destructive ml-1" title="Löschen" onclick="return confirm('Wirklich löschen?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; if (empty($fahrzeuge)): ?>
                    <tr><td colspan="7" class="px-6 py-12 text-center text-muted-foreground">Keine Fahrzeuge gefunden.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>