<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

// Vérifier si c'est un formateur
if ($_SESSION['role'] !== 'FORMATEUR') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'supprimer_travail') {
        // Vérifier le token CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setError('Token CSRF invalide.');
            header('Location: ../views/mes_travaux_formateur.php');
            exit;
        }

        $id_travail = $_POST['id_travail'] ?? 0;

        if (!$id_travail) {
            setError('ID du travail manquant.');
            header('Location: ../views/mes_travaux_formateur.php');
            exit;
        }

        try {
            // Vérifier que le travail appartient bien au formateur
            $stmt = $pdo->prepare("
                SELECT t.id FROM travaux t
                JOIN formateurs f ON t.id_formateur = f.id
                WHERE t.id = ? AND f.id_utilisateur = ?
            ");
            $stmt->execute([$id_travail, $_SESSION['user_id']]);

            if (!$stmt->fetch()) {
                setError('Vous n\'avez pas les droits pour supprimer ce travail.');
                header('Location: ../views/mes_travaux_formateur.php');
                exit;
            }

            // Supprimer les fichiers associés (DB + disque)
            try {
                $fileSelect = $pdo->prepare("SELECT chemin_fichier FROM fichiers_travaux WHERE id_travail = ?");
                $fileSelect->execute([$id_travail]);
                $filesToUnlink = $fileSelect->fetchAll(PDO::FETCH_COLUMN);

                foreach ($filesToUnlink as $fpath) {
                    $abs = __DIR__ . '/../' . ltrim($fpath, '/\\');
                    if (is_file($abs)) @unlink($abs);
                }

                // tenter de supprimer le dossier s'il est vide
                $dir = __DIR__ . '/../uploads/travaux/' . $id_travail;
                if (is_dir($dir)) {
                    // supprimer récursivement
                    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $file) {
                        if ($file->isDir()) rmdir($file->getRealPath()); else @unlink($file->getRealPath());
                    }
                    @rmdir($dir);
                }
            } catch (Exception $e) {
                // ignorer les erreurs d'effacement disque
            }

            $stmt = $pdo->prepare("DELETE FROM fichiers_travaux WHERE id_travail = ?");
            $stmt->execute([$id_travail]);

            // Supprimer les évaluations
            $stmt = $pdo->prepare("
                DELETE ev FROM evaluations ev
                JOIN rendus_travaux rt ON ev.id_rendu = rt.id
                JOIN assignations_travaux at ON rt.id_assignation = at.id
                WHERE at.id_travail = ?
            ");
            $stmt->execute([$id_travail]);

            // Supprimer les rendus
            $stmt = $pdo->prepare("
                DELETE rt FROM rendus_travaux rt
                JOIN assignations_travaux at ON rt.id_assignation = at.id
                WHERE at.id_travail = ?
            ");
            $stmt->execute([$id_travail]);

            // Supprimer les assignations
            $stmt = $pdo->prepare("DELETE FROM assignations_travaux WHERE id_travail = ?");
            $stmt->execute([$id_travail]);

            // Supprimer les groupes et leurs relations s'il y en a
            try {
                $stmtGroupeIds = $pdo->prepare("SELECT id FROM groupes WHERE id_travail = ?");
                $stmtGroupeIds->execute([$id_travail]);
                $groupeIds = $stmtGroupeIds->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($groupeIds)) {
                    // supprimer les membres
                    $in = implode(',', array_map('intval', $groupeIds));
                    $pdo->prepare("DELETE FROM groupe_etudiants WHERE id_groupe IN ($in)")->execute();
                    // supprimer les groupes
                    $pdo->prepare("DELETE FROM groupes WHERE id_travail = ?")->execute([$id_travail]);
                }
            } catch (Exception $e) {
                // ignore
            }

            // Supprimer le travail
            $stmt = $pdo->prepare("DELETE FROM travaux WHERE id = ?");
            $stmt->execute([$id_travail]);

            setSuccess('Travail supprimé avec succès.');

        } catch (Exception $e) {
            setError('Erreur lors de la suppression du travail : ' . $e->getMessage());
        }

        header('Location: ../views/mes_travaux_formateur.php');
        exit;
    }
}
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

/**
 * SUPPRESSION DES ENTITÉS
 * Gère la suppression avec vérification des dépendances
 */

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    setError('Paramètres manquants.');
    header('Location: ../index.php');
    exit;
}

// Vérification CSRF
if (!isset($_GET['csrf_token']) || !verifyCsrfToken($_GET['csrf_token'])) {
    setError('Token CSRF invalide.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$type = $_GET['type'];
$id = intval($_GET['id']);

try {
    $pdo->beginTransaction();
    
    if ($type == 'promotion') {
        // Vérifier si des étudiants sont liés
        $check = $pdo->prepare("SELECT COUNT(*) as nb FROM etudiants WHERE id_promotion = ?");
        $check->execute([$id]);
        $result = $check->fetch();
        
        if ($result['nb'] > 0) {
            throw new Exception("Impossible de supprimer cette promotion car " . $result['nb'] . " étudiant(s) y sont encore inscrits.");
        }
        
        $sql = "DELETE FROM promotions WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $redirect = "../views/promotions.php";
        
    } elseif ($type == 'etudiant') {
        // Récupérer id_utilisateur avant suppression
        $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM etudiants WHERE id = ?");
        $stmtUser->execute([$id]);
        $userId = $stmtUser->fetchColumn();
        
        // Supprimer relations dans etudiant_espace
        $stmtRel = $pdo->prepare("DELETE FROM etudiant_espace WHERE id_etudiant = ?");
        $stmtRel->execute([$id]);
        
        // Supprimer l'étudiant
        $sql = "DELETE FROM etudiants WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Supprimer de utilisateur
        if ($userId) {
            $stmtDelUser = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
            $stmtDelUser->execute([$userId]);
        }
        $redirect = "../views/etudiants.php";
        
    } elseif ($type == 'formateur') {
        // Compter les espaces assignés
        $countEspaces = $pdo->prepare("SELECT COUNT(*) as nb FROM espaces_pedagogiques WHERE id_formateur = ?");
        $countEspaces->execute([$id]);
        $result = $countEspaces->fetch();
        $nbEspaces = $result['nb'];
        
        // Mettre id_formateur à NULL (permettre réassignation ultérieure)
        if ($nbEspaces > 0) {
            $updateEspaces = $pdo->prepare("UPDATE espaces_pedagogiques SET id_formateur = NULL WHERE id_formateur = ?");
            $updateEspaces->execute([$id]);
        }
        
        // Récupérer id_utilisateur
        $stmtUser = $pdo->prepare("SELECT id_utilisateur, email FROM formateurs WHERE id = ?");
        $stmtUser->execute([$id]);
        $formateur = $stmtUser->fetch();
        
        // Supprimer le formateur
        $sql = "DELETE FROM formateurs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Supprimer de utilisateur
        if ($formateur && $formateur['id_utilisateur']) {
            $stmtDelUser = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
            $stmtDelUser->execute([$formateur['id_utilisateur']]);
        }
        
        $pdo->commit();
        
        if ($nbEspaces > 0) {
            setSuccess("Formateur supprimé. Les $nbEspaces espace(s) qu'il gérait sont maintenant sans formateur. Pensez à les réassigner.");
        } else {
            setSuccess('Formateur supprimé avec succès.');
        }
        
        header("Location: ../views/formateurs.php");
        exit;
        
    } elseif ($type == 'espace') {
        // Supprimer les relations
        $stmtRel = $pdo->prepare("DELETE FROM etudiant_espace WHERE id_espace = ?");
        $stmtRel->execute([$id]);
        
        // Supprimer l'espace
        $sql = "DELETE FROM espaces_pedagogiques WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $redirect = "../views/consulter_espaces.php";
        
    } else {
        setError('Type inconnu.');
        header('Location: ../index.php');
        exit;
    }

    $pdo->commit();
    
    if (!isset($_SESSION['success'])) {
        setSuccess('Suppression réussie !');
    }
    
    header("Location: $redirect");
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur suppression : ' . $e->getMessage());
    setError('Erreur : ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}
?>