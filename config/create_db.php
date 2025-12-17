<?php
$host = "localhost";
$port = "5432";
$db   = "postgres"; // Connect to default database
$user = "postgres";
$pass = "2001";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Create the database
    $pdo->exec("CREATE DATABASE s_setice");

    echo "Base de données 's_setice' créée avec succès.\n";

    // Connect to the new database
    $pdo = null;
    $dsn = "pgsql:host=$host;port=$port;dbname=s_setice";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Create the table
    $pdo->exec("CREATE TABLE espace_pedagogique (
        id SERIAL PRIMARY KEY,
        nom_matiere VARCHAR(255) NOT NULL,
        description TEXT
    )");

    echo "Table 'espace_pedagogique' créée avec succès.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
