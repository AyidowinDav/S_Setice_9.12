<?php
require_once 'config/db.php';
require_once 'admin/gestion_espaces.php';

// Fonction de test pour insérer un étudiant
function testInsererEtudiant() {
    // Données de test (assurez-vous que ces IDs existent dans la base de données)
    $etudiant_id = 1; // ID d'un étudiant existant
    $promotion_id = 1; // ID d'une promotion existante
    $espace_id = 1; // ID d'un espace pédagogique existant

    echo "Test d'insertion d'un étudiant dans un espace pédagogique...\n";
    $result = insererEtudiantDansEspace($etudiant_id, $promotion_id, $espace_id);
    echo "Résultat: $result\n";
}

// Exécuter le test
testInsererEtudiant();
?>
