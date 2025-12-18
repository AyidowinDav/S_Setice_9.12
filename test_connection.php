<?php
include 'config/db.php';

if ($pdo) {
    echo "Connexion à la base de données réussie.";
} else {
    echo "Erreur de connexion à la base de données.";
}
?>
