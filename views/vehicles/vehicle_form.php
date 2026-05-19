<?php
require '../../config/db.php';

$id = null;
$kennzeichen = '';
$fahrzeug_typ = '';
$marke = '';
$modell = '';
$vin = '';
$status = 'Aktiv';
$naechster_tuev = '';
$naechster_service = '';
$pageTitle = "Neues Fahrzeug anlegen";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pageTitle = "Fahrzeugprofil bearbeiten";

    $stmt = $pdo->prepare("SELECT * FROM fahrzeuge WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        $kennzeichen = $vehicle['kennzeichen'];
        $fahrzeug_typ = $vehicle['fahrzeug_typ'];
        $marke = $vehicle['marke'];
        $modell = $vehicle['modell'];
        $vin = $vehicle['vin'];
        $status = $vehicle['status'];
        $naechster_tuev = $vehicle['naechster_tuev'];
        $naechster_service = $vehicle['naechster_service'];
    } else {
        die("Fahrzeug nicht gefunden.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kennzeichen = $_POST['kennzeichen'];
    $fahrzeug_typ = $_POST['fahrzeug_typ'];
    $marke = $_POST['marke'];
    $modell = $_POST['modell'];
    $vin = $_POST['vin'];
    $status = $_POST['status'];
    $naechster_tuev = $_POST['naechster_tuev'];
    $naechster_service = $_POST['naechster_service'];

    if (!empty($_POST['id'])) {
        $updateId = $_POST['id'];
        $sql = "UPDATE fahrzeuge SET kennzeichen = ?, fahrzeug_typ = ?, marke = ?, modell = ?, vin = ?, status = ?, naechster_tuev = ?, naechster_service = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kennzeichen, $fahrzeug_typ, $marke, $modell, $vin, $status, $naechster_tuev, $naechster_service, $updateId]);
    } else {
        $sql = "INSERT INTO fahrzeuge (kennzeichen, fahrzeug_typ, marke, modell, vin, status, naechster_tuev, naechster_service) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kennzeichen, $fahrzeug_typ, $marke, $modell, $vin, $status, $naechster_tuev, $naechster_service]);
    }

    header("Location: ../../index.php");
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
    <title><?= $pageTitle ?></title>

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
                <i class="bi bi-car-front text-primary"></i>
                <?= $pageTitle ?>
            </h1>
        </div>

        <form method="POST" action="vehicle_form.php" class="p-6">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="<?= $labelClass ?>">Kennzeichen <span class="text-destructive">*</span></label>
                    <input type="text" name="kennzeichen" class="<?= $inputClass ?>" value="<?= htmlspecialchars($kennzeichen) ?>" required placeholder="z.B. M-AB 1234">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Fahrzeugtyp <span class="text-destructive">*</span></label>
                    <select name="fahrzeug_typ" class="<?= $inputClass ?> cursor-pointer" required>
                        <option value="" disabled <?= empty($fahrzeug_typ) ? 'selected' : '' ?>>Bitte wählen...</option>
                        <option value="PKW" <?= $fahrzeug_typ == 'PKW' ? 'selected' : '' ?>>PKW</option>
                        <option value="Transporter" <?= $fahrzeug_typ == 'Transporter' ? 'selected' : '' ?>>Transporter</option>
                    </select>
                </div>

                <div>
                    <label class="<?= $labelClass ?>">Marke <span class="text-destructive">*</span></label>
                    <input type="text" name="marke" class="<?= $inputClass ?>" value="<?= htmlspecialchars($marke) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Modell <span class="text-destructive">*</span></label>
                    <input type="text" name="modell" class="<?= $inputClass ?>" value="<?= htmlspecialchars($modell) ?>" required>
                </div>

                <div>
                    <label class="<?= $labelClass ?>">Fahrgestellnummer (VIN) <span class="text-destructive">*</span></label>
                    <input type="text" name="vin" class="<?= $inputClass ?> uppercase font-mono text-xs" value="<?= htmlspecialchars($vin) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Status <span class="text-destructive">*</span></label>
                    <select name="status" class="<?= $inputClass ?> cursor-pointer" required>
                        <option value="Aktiv" <?= $status == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
                        <option value="In Reparatur" <?= $status == 'In Reparatur' ? 'selected' : '' ?>>In Reparatur</option>
                        <option value="Ausgemustert" <?= $status == 'Ausgemustert' ? 'selected' : '' ?>>Ausgemustert</option>
                    </select>
                </div>
            </div>

            <h3 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-4 mt-8 border-t border-border pt-6">Fristen & Wartung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="<?= $labelClass ?>">Nächster TÜV <span class="text-destructive">*</span></label>
                    <input type="date" name="naechster_tuev" class="<?= $inputClass ?>" value="<?= htmlspecialchars($naechster_tuev) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Nächster Service <span class="text-destructive">*</span></label>
                    <input type="date" name="naechster_service" class="<?= $inputClass ?>" value="<?= htmlspecialchars($naechster_service) ?>" required>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-border">
                <a href="../../index.php" class="px-4 py-2 text-sm font-medium border border-border rounded-md text-foreground hover:bg-muted transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md shadow-sm hover:opacity-90 transition-opacity">
                    <?= $id ? 'Änderungen speichern' : 'Fahrzeug anlegen' ?>
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>