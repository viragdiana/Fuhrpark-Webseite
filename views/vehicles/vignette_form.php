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
    $land = $_POST['land'];
    $vignetten_typ = $_POST['vignetten_typ'];
    $gueltig_von = $_POST['gueltig_von'];
    $gueltig_bis = $_POST['gueltig_bis'];

    $sql = "INSERT INTO vignette (fahrzeug_id, land, vignetten_typ, gueltig_von, gueltig_bis) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrzeug_id, $land, $vignetten_typ, $gueltig_von, $gueltig_bis]);

    header("Location: vehicle_details.php?id=" . $fahrzeug_id);
    exit();
}

$inputClass = "w-full bg-background border border-border text-foreground text-sm rounded-md focus:ring-2 focus:ring-primary focus:border-primary block p-2.5 shadow-sm transition-shadow outline-none";
$labelClass = "block text-sm font-medium text-foreground mb-1.5";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vignette hinzufügen</title>

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
                <i class="bi bi-globe-europe-africa text-primary"></i>
                Vignette / Maut: <?= htmlspecialchars($auto['kennzeichen']) ?>
            </h1>
        </div>

        <form method="POST" action="vignette_form.php?vehicle_id=<?= $fahrzeug_id ?>" class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="<?= $labelClass ?>">Land <span class="text-destructive">*</span></label>
                    <input type="text" name="land" class="<?= $inputClass ?>" list="laender" required placeholder="z.B. Österreich">
                    <datalist id="laender">
                        <option value="Österreich">
                        <option value="Schweiz">
                        <option value="Tschechien">
                        <option value="Slowenien">
                        <option value="Ungarn">
                        <option value="Rumänien">
                    </datalist>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Vignetten-Typ <span class="text-destructive">*</span></label>
                    <select name="vignetten_typ" class="<?= $inputClass ?> cursor-pointer" required>
                        <option value="Jahresvignette">Jahresvignette</option>
                        <option value="Monatsvignette">Monatsvignette</option>
                        <option value="10-Tages-Vignette">10-Tages-Vignette</option>
                        <option value="Streckenmaut">Sondermaut / Streckenmaut</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-t border-border pt-6">
                <div>
                    <label class="<?= $labelClass ?>">Gültig von <span class="text-destructive">*</span></label>
                    <input type="date" name="gueltig_von" class="<?= $inputClass ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Gültig bis <span class="text-destructive">*</span></label>
                    <input type="date" name="gueltig_bis" class="<?= $inputClass ?>" required>
                </div>
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