<?php
$dbFile = __DIR__ . '/fuhrpark.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS fahrzeuge (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kennzeichen TEXT NOT NULL,
        fahrzeug_typ TEXT NOT NULL,
        marke TEXT NOT NULL,
        modell TEXT NOT NULL,
        vin TEXT NOT NULL,
        status TEXT DEFAULT 'Aktiv',
        fahrer_id INTEGER DEFAULT NULL
    );
");

    // Inside db.php
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS fahrer (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vorname TEXT NOT NULL,
        nachname TEXT NOT NULL,
        mitarbeiter_id TEXT NOT NULL,
        fuehrerscheinklassen TEXT NOT NULL,
        naechste_pruefung TEXT NOT NULL
    );
");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>