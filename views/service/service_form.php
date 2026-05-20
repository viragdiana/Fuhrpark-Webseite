<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auto) {
    die("Fahrzeug nicht gefunden.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datum = $_POST['datum'];
    $werkstatt = $_POST['werkstatt'];
    $kosten = $_POST['kosten'];
    $reparatur_typ = $_POST['reparatur_typ'];

    $sql = "INSERT INTO wartung (fahrzeug_id, datum, werkstatt, kosten, reparatur_typ) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fahrzeug_id, $datum, $werkstatt, $kosten, $reparatur_typ]);

    header("Location: ../vehicles/vehicle_details.php?id=" . $fahrzeug_id);
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
    <title>Wartung protokollieren</title>

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
                <i class="bi bi-tools text-primary"></i>
                Wartung: <?= htmlspecialchars($auto['kennzeichen']) ?>
            </h1>
        </div>

        <form method="POST" action="service_form.php?vehicle_id=<?= $fahrzeug_id ?>" class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="<?= $labelClass ?>">Datum der Reparatur <span class="text-destructive">*</span></label>
                    <input type="date" name="datum" class="<?= $inputClass ?>" required value="<?= date('Y-m-d') ?>">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Werkstatt / Dienstleister <span class="text-destructive">*</span></label>
                    <input type="text" name="werkstatt" class="<?= $inputClass ?>" required placeholder="z.B. ATU München">
                </div>
            </div>

            <div class="mb-8">
                <label class="<?= $labelClass ?>">Art der Reparatur <span class="text-destructive">*</span></label>
                <input type="text" name="reparatur_typ" class="<?= $inputClass ?>" required placeholder="z.B. Ölwechsel, Bremsen erneuert">
            </div>

            <div class="mb-4 border-t border-border pt-6">
                <label class="<?= $labelClass ?>">Kosten (Brutto) <span class="text-destructive">*</span></label>
                <div class="flex items-center gap-3 max-w-[200px]">
                    <input type="number" name="kosten" class="<?= $inputClass ?>" step="0.01" min="0" required placeholder="0.00">
                    <span class="text-muted-foreground font-medium">€</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 mt-6 border-t border-border">
                <a href="../vehicles/vehicle_details.php?id=<?= $fahrzeug_id ?>" class="px-4 py-2 text-sm font-medium border border-border rounded-md text-foreground hover:bg-muted transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md shadow-sm hover:opacity-90 transition-opacity">
                    Eintrag speichern
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>