<?php
require __DIR__ . '/../config/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS fichiers_travaux (id INT AUTO_INCREMENT PRIMARY KEY, travail_id INT, fichier_url VARCHAR(255), nom_original VARCHAR(255), type VARCHAR(100), taille INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fichiers_rendus (id INT AUTO_INCREMENT PRIMARY KEY, rendu_id INT, fichier_url VARCHAR(255), nom_original VARCHAR(255), type VARCHAR(100), taille INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    echo "Tables OK\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
