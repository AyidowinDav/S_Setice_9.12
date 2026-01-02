<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_action'])) {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setError('Token CSRF invalide. Rechargez la page et réessayez.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
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
                setError('Erreur : Cette promotion est déjà assignée à cet espace pédagogique.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/consulter_espaces.php'));
                exit;
            }

            // 2. Récupérer TOUS les étudiants de cette promotion
            $stmtEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id_promotion = ?");
            $stmtEtu->execute([$id_p]);
            $etudiants = $stmtEtu->fetchAll();

            if (empty($etudiants)) {
                setError('Erreur : Cette promotion ne contient aucun étudiant.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/consulter_espaces.php'));
                exit;
            }

            // 3. Insérer les étudiants dans la table de liaison
            $insert = $pdo->prepare("INSERT INTO etudiant_espace (id_etudiant, id_espace) VALUES (?, ?)");
            
            $pdo->beginTransaction(); // Sécurité : tout passe ou rien ne passe
            foreach ($etudiants as $etu) {
                $insert->execute([$etu['id'], $id_e]);
            }
            $pdo->commit();

            setSuccess('Promotion assignée avec succès ! ' . count($etudiants) . ' étudiants ajoutés.');
            header('Location: ../views/consulter_espaces.php');
            exit;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setError('Erreur : ' . $e->getMessage());
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/consulter_espaces.php'));
        exit;
    }
}