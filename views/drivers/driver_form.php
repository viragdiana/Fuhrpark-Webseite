<?php
require '../../config/db.php';

$id = null;
$vorname = '';
$nachname = '';
$mitarbeiter_id = '';
$saved_klassen = [];
$naechste_pruefung = '';
$pageTitle = "Fahrerprofil anlegen";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pageTitle = "Fahrerprofil bearbeiten";

    $stmt = $pdo->prepare("SELECT * FROM fahrer WHERE id = ?");
    $stmt->execute([$id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($driver) {
        $vorname = $driver['vorname'];
        $nachname = $driver['nachname'];
        $mitarbeiter_id = $driver['mitarbeiter_id'];
        $naechste_pruefung = $driver['naechste_pruefung'];
        if (!empty($driver['fuehrerscheinklassen'])) {
            $saved_klassen = explode(', ', $driver['fuehrerscheinklassen']);
        }
    } else {
        die("Fahrereintrag nicht gefunden.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vorname = $_POST['vorname'];
    $nachname = $_POST['nachname'];
    $mitarbeiter_id = $_POST['mitarbeiter_id'];
    $naechste_pruefung = $_POST['naechste_pruefung'];

    $klassen_array = $_POST['klassen'] ?? [];
    $fuehrerscheinklassen = implode(', ', $klassen_array);

    if (!empty($_POST['id'])) {
        $updateId = $_POST['id'];
        $sql = "UPDATE fahrer SET vorname = ?, nachname = ?, mitarbeiter_id = ?, fuehrerscheinklassen = ?, naechste_pruefung = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vorname, $nachname, $mitarbeiter_id, $fuehrerscheinklassen, $naechste_pruefung, $updateId]);
    } else {
        $sql = "INSERT INTO fahrer (vorname, nachname, mitarbeiter_id, fuehrerscheinklassen, naechste_pruefung) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vorname, $nachname, $mitarbeiter_id, $fuehrerscheinklassen, $naechste_pruefung]);
    }

    header("Location: drivers_list.php");
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
                <i class="bi bi-person-badge text-primary"></i>
                <?= $pageTitle ?>
            </h1>
        </div>

        <form method="POST" action="driver_form.php" class="p-6">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="<?= $labelClass ?>">Vorname <span class="text-destructive">*</span></label>
                    <input type="text" name="vorname" class="<?= $inputClass ?>" value="<?= htmlspecialchars($vorname) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Nachname <span class="text-destructive">*</span></label>
                    <input type="text" name="nachname" class="<?= $inputClass ?>" value="<?= htmlspecialchars($nachname) ?>" required>
                </div>

                <div>
                    <label class="<?= $labelClass ?>">Mitarbeiter ID <span class="text-destructive">*</span></label>
                    <input type="text" name="mitarbeiter_id" class="<?= $inputClass ?> font-mono text-sm" value="<?= htmlspecialchars($mitarbeiter_id) ?>" required>
                </div>
                <div>
                    <label class="<?= $labelClass ?> text-primary">Nächste Führerscheinprüfung <span class="text-destructive">*</span></label>
                    <input type="date" name="naechste_pruefung" class="<?= $inputClass ?>" value="<?= htmlspecialchars($naechste_pruefung) ?>" required>
                </div>
            </div>

            <div class="mb-8 border-t border-border pt-6">
                <label class="<?= $labelClass ?>">Führerscheinklassen <span class="text-destructive">*</span></label>
                <div class="flex flex-wrap gap-4 mt-3 p-4 bg-muted/20 border border-border rounded-md">
                    <?php
                    $klassen_options = ['AM', 'A1', 'A2', 'A', 'B', 'BE', 'C1', 'C1E', 'C', 'CE', 'D1', 'D1E', 'D', 'DE'];
                    foreach ($klassen_options as $klasse):
                        $checked = in_array($klasse, $saved_klassen) ? 'checked' : '';
                        ?>
                        <label class="flex items-center gap-2 cursor-pointer w-16">
                            <input type="checkbox" name="klassen[]" value="<?= $klasse ?>" class="w-4 h-4 text-primary bg-background border-border rounded focus:ring-primary focus:ring-2" <?= $checked ?>>
                            <span class="text-sm font-medium text-foreground"><?= $klasse ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-border">
                <a href="drivers_list.php" class="px-4 py-2 text-sm font-medium border border-border rounded-md text-foreground hover:bg-muted transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md shadow-sm hover:opacity-90 transition-opacity">
                    <?= $id ? 'Änderungen speichern' : 'Profil anlegen' ?>
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>