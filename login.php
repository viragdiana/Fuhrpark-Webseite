<?php
session_start();
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Ungültige E-Mail oder falsches Passwort.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fuhrpark OS</title>

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
            --color-border: #d4cfc7;
            --color-destructive: #c75146;
        }
        @layer base {
            body { @apply bg-background text-foreground font-sans antialiased; }
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center p-4">

<div class="bg-card border border-border rounded-xl shadow-md w-full max-w-md p-8">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-muted text-primary text-3xl mb-4 border border-border">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <h1 class="text-2xl font-bold text-foreground">Fuhrpark OS</h1>
        <p class="text-sm text-muted-foreground mt-1">Bitte melden Sie sich an</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-destructive/10 border border-destructive/20 text-destructive p-3 rounded-lg text-sm mb-6 font-medium flex items-center gap-2">
            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-foreground mb-1.5">E-Mail Adresse</label>
            <input type="email" name="email" required autofocus
                   class="w-full bg-background border border-border text-sm rounded-md focus:ring-2 focus:ring-primary focus:border-primary block p-2.5 outline-none transition-shadow text-foreground">
        </div>

        <div>
            <label class="block text-sm font-medium text-foreground mb-1.5">Passwort</label>
            <input type="password" name="password" required
                   class="w-full bg-background border border-border text-sm rounded-md focus:ring-2 focus:ring-primary focus:border-primary block p-2.5 outline-none transition-shadow text-foreground">
        </div>

        <button type="submit" class="w-full bg-primary text-primary-foreground font-medium rounded-md py-2.5 hover:opacity-90 transition-opacity mt-2 shadow-sm">
            Einloggen
        </button>
    </form>

</div>

</body>
</html>