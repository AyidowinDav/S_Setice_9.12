<?php
require_once 'config/db.php';

try {
    // Test the connection by executing a simple query
    $stmt = $pdo->query("SELECT 1");
    echo "Connexion à la base de données réussie!";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
