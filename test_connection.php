<?php
<<<<<<< HEAD
<<<<<<< HEAD
include 'config/db.php';

try {
    echo "Connection successful!\n";
    echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
=======
require_once 'config/db.php';

try {
    // Test the connection by executing a simple query
    $stmt = $pdo->query("SELECT 1");
    echo "Connexion à la base de données réussie!";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
>>>>>>> 51bb5f3af26f3934429b676cf181c97ccfd8d42c
=======
include 'config/db.php';

if ($pdo) {
    echo "Connexion à la base de données réussie.";
} else {
    echo "Erreur de connexion à la base de données.";
>>>>>>> e30197b71af36a9448a7c5ab054e44d81486b2a8
}
?>
