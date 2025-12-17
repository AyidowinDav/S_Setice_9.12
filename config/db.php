<?php
$host = "localhost";
$db   = "s_setice";
$user = "root";
$pass = "";

$pdo = null;
try {
    $dsn = "mysql:host=$host;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
