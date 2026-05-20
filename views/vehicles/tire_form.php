<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saison = $_POST['saison'];
    $marke = $_POST['marke'];
    $lagerort = $_POST['lagerort'];
    $profiltiefe = (float) str_replace(',', '.', $_POST['profiltiefe']);

    $stmtCheck = $pdo->prepare("SELECT id FROM reifen WHERE fahrzeug_id = ? AND saison = ?");
    $stmtCheck->execute([$fahrzeug_id, $saison]);
    $existing = $stmtCheck->fetch();

    if ($existing) {
        $sql = "UPDATE reifen SET marke = ?, lagerort = ?, profiltiefe = ? WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([$marke, $lagerort, $profiltiefe, $existing['id']]);
    } else {
        $sql = "INSERT INTO reifen (fahrzeug_id, saison, marke, lagerort, profiltiefe) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([$fahrzeug_id, $saison, $marke, $lagerort, $profiltiefe]);
    }

    header("Location: vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}

// Helper styling variables
$inputClass = "w-full bg-background border border-border text-foreground text-sm rounded-md focus:ring-2 focus:ring-primary focus:border-primary block p-2.5 shadow-sm transition-shadow outline-none";
$labelClass = "block text-sm font-medium text-foreground mb-1.5";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reifensatz verwalten</title>

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

<main class="max-w-3xl mx-auto px-4 py-8">

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="bg-muted/30 border-b border-border px-6 py-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="bi bi-record-circle text-primary"></i>
                Reifensatz: <?= htmlspecialchars($auto['kennzeichen']) ?>
            </h1>
        </div>

        <form method="POST" action="tire_form.php?vehicle_id=<?= $fahrzeug_id ?>" class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="<?= $labelClass ?>">Saison / Typ <span class="text-destructive">*</span></label>
                    <select name="saison" class="<?= $inputClass ?> cursor-pointer" required>
                        <option value="Sommer">Sommerreifen</option>
                        <option value="Winter">Winterreifen</option>
                        <option value="Allwetter">Allwetterreifen</option>
                    </select>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Marke (Brand) <span class="text-destructive">*</span></label>
                    <input type="text" name="marke" class="<?= $inputClass ?>" required placeholder="z.B. Michelin, Continental">
                </div>
            </div>

            <div class="mb-6">
                <label class="<?= $labelClass ?>">Lagerort (Storage Location) <span class="text-destructive">*</span></label>
                <input type="text" name="lagerort" class="<?= $inputClass ?>" list="lagerorte" required placeholder="z.B. Am Fahrzeug montiert">
                <datalist id="lagerorte">
                    <option value="Am Fahrzeug montiert">
                    <option value="Lager intern (Halle A)">
                    <option value="Extern (Reifen-Händler)">
                </datalist>
            </div>

            <div class="mb-8 border-t border-border pt-6">
                <label class="<?= $labelClass ?>">Aktuelle Profiltiefe <span class="text-destructive">*</span></label>
                <div class="flex items-center gap-3 max-w-[200px]">
                    <input type="number" name="profiltiefe" class="<?= $inputClass ?>" step="0.1" min="0" max="15" required placeholder="z.B. 6.5">
                    <span class="text-muted-foreground font-medium">mm</span>
                </div>
                <p class="text-xs text-muted-foreground mt-2">Systemwarnung bei Werten unter 1,6 mm.</p>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-border">
                <a href="vehicle_details.php?id=<?= $fahrzeug_id ?>" class="px-4 py-2 text-sm font-medium border border-border rounded-md text-foreground hover:bg-muted transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md shadow-sm hover:opacity-90 transition-opacity">
                    Speichern
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>