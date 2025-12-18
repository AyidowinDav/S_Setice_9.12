<?php
$host = "localhost";
$dbname = "s_setice";
$user = "root";
$pass = ""; // mot de passe MySQL (souvent vide en local)

$pdo = null;
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
