<?php
require '../../config/db.php';

$stmtVehicles = $pdo->query("SELECT id, kennzeichen, marke, modell FROM fahrzeuge WHERE status = 'Aktiv'");
$activeVehicles = $stmtVehicles->fetchAll(PDO::FETCH_ASSOC);

$stmtDrivers = $pdo->query("SELECT id, vorname, nachname, mitarbeiter_id FROM fahrer");
$drivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);

$preSelectedVehicle = $_GET['vehicle_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_id = $_POST['vehicle_id'];
    $fahrer_id = $_POST['fahrer_id'];

    $sql = "UPDATE fahrzeuge SET fahrer_id = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrer_id, $vehicle_id]);

    header("Location: ../vehicles/vehicle_details.php?id=" . $vehicle_id);
    exit();
}

$inputClass = "w-full bg-background border border-border text-foreground text-sm rounded-md focus:ring-2 focus:ring-primary focus:border-primary block p-2.5 shadow-sm transition-shadow outline-none cursor-pointer";
$labelClass = "block text-sm font-medium text-foreground mb-1.5";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fahrer zuweisen</title>

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

<main class="max-w-2xl mx-auto px-4 py-8">

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="bg-muted/30 border-b border-border px-6 py-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="bi bi-person-plus text-primary"></i>
                Fahrer zuweisen
            </h1>
        </div>

        <form method="POST" action="assign_driver.php" class="p-6">

            <div class="mb-6">
                <label class="<?= $labelClass ?>">Fahrzeug auswählen (Nur aktive) <span class="text-destructive">*</span></label>
                <select name="vehicle_id" class="<?= $inputClass ?>" required>
                    <option value="" disabled <?= empty($preSelectedVehicle) ? 'selected' : '' ?>>Bitte ein Fahrzeug wählen...</option>
                    <?php foreach ($activeVehicles as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= $preSelectedVehicle == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['kennzeichen']) ?> - <?= htmlspecialchars($v['marke']) ?> <?= htmlspecialchars($v['modell']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($activeVehicles)): ?>
                    <p class="text-xs font-medium text-destructive mt-2">
                        <i class="bi bi-exclamation-circle me-1"></i> Keine aktiven Fahrzeuge verfügbar.
                    </p>
                <?php endif; ?>
            </div>

            <div class="mb-8">
                <label class="<?= $labelClass ?>">Fahrer auswählen <span class="text-destructive">*</span></label>
                <select name="fahrer_id" class="<?= $inputClass ?>" required>
                    <option value="" disabled selected>Bitte einen Fahrer wählen...</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= $d['id'] ?>">
                            <?= htmlspecialchars($d['vorname']) ?> <?= htmlspecialchars($d['nachname']) ?> (ID: <?= htmlspecialchars($d['mitarbeiter_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-border">
                <a href="<?= empty($preSelectedVehicle) ? '../../index.php' : '../vehicles/vehicle_details.php?id=' . $preSelectedVehicle ?>"
                   class="px-4 py-2 text-sm font-medium border border-border rounded-md text-foreground hover:bg-muted transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md shadow-sm hover:opacity-90 transition-opacity" <?= empty($activeVehicles) ? 'disabled' : '' ?>>
                    Zuweisung speichern
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>