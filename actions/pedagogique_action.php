<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

/**
 * SSS-4 : CRÉATION D'UN ESPACE PÉDAGOGIQUE VIDE
 * SSS-6 : INSERTION D'UN FORMATEUR DANS UN ESPACE
 * SSS-7 : INSERTION D'UN ÉTUDIANT/PROMOTION DANS UN ESPACE
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['type_action'])) {
    header('Location: ../index.php');
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setError('Token CSRF invalide.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$type = $_POST['type_action'];

try {
    // ==================== SSS-4 : CRÉATION ESPACE ====================
    if ($type === 'creer_espace') {
        $nom_espace = trim($_POST['nom_espace'] ?? '');
        $id_formateur = intval($_POST['id_formateur'] ?? 0);
        
        // VALIDATION 1 : Nom obligatoire
        if (empty($nom_espace)) {
            setError('Le nom de l\'espace est requis.');
            header('Location: ../views/espaces.php');
            exit;
        }
        
        // VALIDATION 2 : Nom minimum 3 caractères
        if (strlen($nom_espace) < 3) {
            setError('Le nom de l\'espace doit contenir au moins 3 caractères.');
            header('Location: ../views/espaces.php');
            exit;
        }
        
        // VALIDATION 3 : Formateur existe
        $checkFormateur = $pdo->prepare("SELECT id FROM formateurs WHERE id = ?");
        $checkFormateur->execute([$id_formateur]);
        
        if ($checkFormateur->rowCount() === 0) {
            setError('Le formateur sélectionné n\'existe pas.');
            header('Location: ../views/espaces.php');
            exit;
        }
        
        // CAS DÉFAVORABLE : Nom d'espace déjà utilisé
        $checkNom = $pdo->prepare("SELECT id FROM espaces_pedagogiques WHERE nom_espace = ?");
        $checkNom->execute([$nom_espace]);
        
        if ($checkNom->rowCount() > 0) {
            setError('Ce nom d\'espace est déjà utilisé dans le système.');
            header('Location: ../views/espaces.php');
            exit;
        }
        
        // CAS FAVORABLE : Créer l'espace avec formateur
        $insert = $pdo->prepare("
            INSERT INTO espaces_pedagogiques (nom_espace, id_formateur) 
            VALUES (?, ?)
        ");
        $insert->execute([$nom_espace, $id_formateur]);
        
        setSuccess('Espace pédagogique créé avec succès !');
        header('Location: ../views/espaces.php');
        exit;
    }
    
    // ==================== SSS-7 : ASSIGNATION PROMOTION ====================
    elseif ($type === 'assigner_promo') {
        $id_promotion = intval($_POST['id_promotion'] ?? 0);
        $id_espace = intval($_POST['id_espace'] ?? 0);

        // VALIDATION : IDs valides
        if ($id_promotion === 0 || $id_espace === 0) {
            setError('Données invalides.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/consulter_espaces.php'));
            exit;
        }

        // CAS DÉFAVORABLE : Vérifier si promotion déjà assignée à cet espace
        $checkPromoEspace = $pdo->prepare("
            SELECT * FROM promotion_espace
            WHERE id_promotion = ? AND id_espace = ?
        ");
        $checkPromoEspace->execute([$id_promotion, $id_espace]);

        if ($checkPromoEspace->rowCount() > 0) {
            setError('Erreur : Cette promotion est déjà assignée à cet espace pédagogique.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/consulter_espaces.php'));
            exit;
        }

        // Récupérer tous les étudiants de cette promotion
        $stmtEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id_promotion = ?");
        $stmtEtu->execute([$id_promotion]);
        $etudiants = $stmtEtu->fetchAll();

        // CAS FAVORABLE : Transaction pour assigner la promotion et les étudiants existants
        $pdo->beginTransaction();

        try {
            // 1. Créer le lien promotion-espace pour les futurs étudiants
            $insertPromoEspace = $pdo->prepare("
                INSERT INTO promotion_espace (id_promotion, id_espace)
                VALUES (?, ?)
            ");
            $insertPromoEspace->execute([$id_promotion, $id_espace]);

            // 2. Assigner tous les étudiants existants de cette promotion à l'espace
            if (!empty($etudiants)) {
                $insertEtudiantEspace = $pdo->prepare("
                    INSERT INTO etudiant_espace (id_etudiant, id_espace)
                    VALUES (?, ?)
                ");

                foreach ($etudiants as $etu) {
                    $insertEtudiantEspace->execute([$etu['id'], $id_espace]);
                }
            }

            $pdo->commit();

            $studentCount = count($etudiants);
            setSuccess('Promotion assignée avec succès à l\'espace ! ' . $studentCount . ' étudiant(s) existant(s) ajouté(s). Les futurs étudiants de cette promotion seront automatiquement assignés.');

        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }

        header('Location: ../views/consulter_espaces.php');
        exit;
    }
    

    
    else {
        setError('Action inconnue.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur action pédagogique : ' . $e->getMessage());
    setError('Erreur : ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}
?>