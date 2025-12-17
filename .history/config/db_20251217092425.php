<?php
$host = "localhost";
$port = "5432";
$db   = "s_setice";
$user = "postgres";
$pass = "2001"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}