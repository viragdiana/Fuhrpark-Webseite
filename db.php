<?php
$dbFile = __DIR__ . '/fuhrpark.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS fahrzeuge (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kennzeichen TEXT NOT NULL,
            fahrzeug_typ TEXT NOT NULL,
            marke TEXT NOT NULL,
            modell TEXT NOT NULL,
            vin TEXT NOT NULL,
            status TEXT DEFAULT 'Aktiv'
        );
    ";
    $pdo->exec($createTableSQL);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>