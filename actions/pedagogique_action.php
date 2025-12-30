<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_action'])) {
    $type = $_POST['type_action'];

    try {
        if ($type === 'assigner_promo') {
            $id_p = $_POST['id_promotion'];
            $id_e = $_POST['id_espace'];

            // 1. Vérifier si la promotion est DÉJÀ assignée à cet espace
            // On vérifie s'il existe au moins un étudiant de cette promo déjà lié à cet espace
            $check = $pdo->prepare("SELECT COUNT(*) FROM etudiant_espace ee 
                                   JOIN etudiants e ON ee.id_etudiant = e.id 
                                   WHERE e.id_promotion = ? AND ee.id_espace = ?");
            $check->execute([$id_p, $id_e]);

            if ($check->fetchColumn() > 0) {
                echo "<script>alert('Erreur : Cette promotion est déjà assignée à cet espace pédagogique.'); window.history.back();</script>";
                exit();
            }

            // 2. Récupérer TOUS les étudiants de cette promotion
            $stmtEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id_promotion = ?");
            $stmtEtu->execute([$id_p]);
            $etudiants = $stmtEtu->fetchAll();

            if (empty($etudiants)) {
                echo "<script>alert('Erreur : Cette promotion ne contient aucun étudiant.'); window.history.back();</script>";
                exit();
            }

            // 3. Insérer les étudiants dans la table de liaison
            $insert = $pdo->prepare("INSERT INTO etudiant_espace (id_etudiant, id_espace) VALUES (?, ?)");
            
            $pdo->beginTransaction(); // Sécurité : tout passe ou rien ne passe
            foreach ($etudiants as $etu) {
                $insert->execute([$etu['id'], $id_e]);
            }
            $pdo->commit();

            echo "<script>alert('Promotion assignée avec succès ! " . count($etudiants) . " étudiants ajoutés.'); window.location.href='../views/consulter_espaces.php';</script>";
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}