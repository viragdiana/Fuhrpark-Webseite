<?php
require '../../config/db.php';

$fahrzeug_id = $_GET['vehicle_id'] ?? null;

if (!$fahrzeug_id) {
    die("Kein Fahrzeug angegeben.");
}

$stmt = $pdo->prepare("SELECT kennzeichen, marke, modell FROM fahrzeuge WHERE id = ?");
$stmt->execute([$fahrzeug_id]);
$auto = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtIns = $pdo->prepare("SELECT * FROM versicherung WHERE fahrzeug_id = ?");
$stmtIns->execute([$fahrzeug_id]);
$insurance = $stmtIns->fetch(PDO::FETCH_ASSOC);

$gesellschaft = $insurance['gesellschaft'] ?? '';
$police_nr = $insurance['police_nr'] ?? '';
$deckungsart = $insurance['deckungsart'] ?? 'Haftpflicht';
$ablaufdatum = $insurance['ablaufdatum'] ?? '';
$kuendigungsfrist = $insurance['kuendigungsfrist'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_gesellschaft = $_POST['gesellschaft'];
    $post_police_nr = $_POST['police_nr'];
    $post_deckungsart = $_POST['deckungsart'];
    $post_ablaufdatum = $_POST['ablaufdatum'];
    $post_kuendigungsfrist = $_POST['kuendigungsfrist'];

    if ($insurance) {
        $sql = "UPDATE versicherung SET gesellschaft = ?, police_nr = ?, deckungsart = ?, ablaufdatum = ?, kuendigungsfrist = ? WHERE fahrzeug_id = ?";
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([$post_gesellschaft, $post_police_nr, $post_deckungsart, $post_ablaufdatum, $post_kuendigungsfrist, $fahrzeug_id]);
    } else {
        $sql = "INSERT INTO versicherung (fahrzeug_id, gesellschaft, police_nr, deckungsart, ablaufdatum, kuendigungsfrist) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([$fahrzeug_id, $post_gesellschaft, $post_police_nr, $post_deckungsart, $post_ablaufdatum, $post_kuendigungsfrist]);
    }

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
    <title>Versicherung verwalten</title>

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
                <i class="bi bi-shield-check text-primary"></i>
                Versicherung: <?= htmlspecialchars($auto['kennzeichen']) ?>
            </h1>
        </div>

        <form method="POST" action="insurance_form.php?vehicle_id=<?= $fahrzeug_id ?>" class="p-6">

            <div class="mb-6">
                <label class="<?= $labelClass ?>">Versicherungsgesellschaft <span class="text-destructive">*</span></label>
                <input type="text" name="gesellschaft" class="<?= $inputClass ?>" value="<?= htmlspecialchars($gesellschaft) ?>" required placeholder="z.B. Allianz, HUK-Coburg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="<?= $labelClass ?>">Policen-Nummer <span class="text-destructive">*</span></label>
                    <input type="text" name="police_nr" class="<?= $inputClass ?> font-mono text-sm" value="<?= htmlspecialchars($police_nr) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Art der Deckung <span class="text-destructive">*</span></label>
                    <select name="deckungsart" class="<?= $inputClass ?> cursor-pointer" required>
                        <option value="Haftpflicht" <?= $deckungsart == 'Haftpflicht' ? 'selected' : '' ?>>Haftpflicht</option>
                        <option value="Teilkasko" <?= $deckungsart == 'Teilkasko' ? 'selected' : '' ?>>Teilkasko</option>
                        <option value="Vollkasko" <?= $deckungsart == 'Vollkasko' ? 'selected' : '' ?>>Vollkasko</option>
                    </select>
                </div>
            </div>

            <h3 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-4 border-t border-border pt-6">Fristen</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="<?= $labelClass ?>">Ablaufdatum <span class="text-destructive">*</span></label>
                    <input type="date" name="ablaufdatum" class="<?= $inputClass ?>" value="<?= htmlspecialchars($ablaufdatum) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Kündigungsfrist <span class="text-destructive">*</span></label>
                    <input type="date" name="kuendigungsfrist" class="<?= $inputClass ?>" value="<?= htmlspecialchars($kuendigungsfrist) ?>" required>
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