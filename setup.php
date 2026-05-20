<?php
require 'config/db.php';

$email = "admin@fuhrpark.local";
$password = "password123";
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
$stmt->execute([$email, $hash]);

echo "Admin account created successfully! You can now delete setup.php and go to login.php.";
?>