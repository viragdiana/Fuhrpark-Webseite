<?php
require '../../config/db.php';

$action = $_GET['action'] ?? '';

$config = [
    'vignette'  => ['path' => 'vignette_form.php', 'title' => 'Vignette hinzufügen', 'icon' => 'bi-globe-europe-africa'],
    'insurance' => ['path' => 'insurance_form.php', 'title' => 'Versicherung hinzufügen', 'icon' => 'bi-shield-check'],
    'service'   => ['path' => '../service/service_form.php', 'title' => 'Wartung protokollieren', 'icon' => 'bi-wrench'],
    'tire'      => ['path' => 'tire_form.php', 'title' => 'Reifensatz anlegen', 'icon' => 'bi-record-circle'],
];

if (!array_key_exists($action, $config)) {
    header("Location: ../../index.php");
    exit();
}

$currentConfig = $config[$action];

$stmt = $pdo->query("SELECT id, kennzeichen, marke, modell, vin FROM fahrzeuge WHERE status = 'Aktiv' ORDER BY marke, modell");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fahrzeug auswählen</title>

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

<main class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center gap-4 mb-2">
        <a href="javascript:history.back()" class="text-foreground hover:text-primary transition-colors text-xl">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-[28px] font-bold text-foreground">Fahrzeug auswählen</h1>
    </div>

    <p class="text-muted-foreground mb-8 ml-9">
        Für welches Fahrzeug möchten Sie die Aktion <strong class="text-foreground"><i class="bi <?= $currentConfig['icon'] ?> mx-1"></i><?= $currentConfig['title'] ?></strong> durchführen?
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($vehicles as $v): ?>
            <a href="<?= $currentConfig['path'] ?>?vehicle_id=<?= $v['id'] ?>" class="block bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all group">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-muted flex items-center justify-center text-muted-foreground group-hover:text-primary group-hover:bg-primary/10 transition-colors shrink-0">
                        <i class="bi bi-car-front text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-foreground text-lg leading-tight group-hover:text-primary transition-colors">
                            <?= htmlspecialchars($v['marke'] . ' ' . $v['modell']) ?>
                        </h3>
                        <div class="text-sm font-medium text-muted-foreground mt-0.5">
                            <?= htmlspecialchars($v['kennzeichen']) ?>
                        </div>
                        <div class="text-xs text-muted-foreground font-mono mt-2 bg-muted/50 inline-block px-2 py-0.5 rounded border border-border">
                            <?= htmlspecialchars($v['vin']) ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($vehicles)): ?>
        <div class="bg-card border border-border rounded-xl p-12 text-center text-muted-foreground shadow-sm">
            <i class="bi bi-car-front text-4xl mb-3 block"></i>
            Keine aktiven Fahrzeuge im System gefunden.
        </div>
    <?php endif; ?>

</main>

</body>
</html>
