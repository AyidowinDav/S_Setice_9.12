il n'y a aucun <?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

/**
 * MISE À JOUR DES ENTITÉS
 * Gère la modification de promotions, étudiants, formateurs et espaces
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setError('Token CSRF invalide.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);

if ($id === 0) {
    setError('ID invalide.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

try {
    if ($type == 'promotion') {
        $nom = strtoupper(trim($_POST['nom'] ?? ''));
        $annee = trim($_POST['annee'] ?? '');
        
        // Validations
        if (empty($nom) || empty($annee)) {
            setError('Tous les champs sont obligatoires.');
            header('Location: ../views/edit_promotion.php?id=' . $id);
            exit;
        }
        
        if (strlen($nom) < 2) {
            setError('Le nom doit contenir au moins 2 caractères.');
            header('Location: ../views/edit_promotion.php?id=' . $id);
            exit;
        }
        
        $sql = "UPDATE promotions SET nom = ?, annee = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $annee, $id]);
        $redirect = "../views/promotions.php";
    } 
    elseif ($type == 'etudiant') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $id_promotion = intval($_POST['id_promotion'] ?? 0);
        
        // Validations
        if (empty($nom) || empty($prenom) || empty($email) || $id_promotion === 0) {
            setError('Tous les champs sont obligatoires.');
            header('Location: ../views/edit_etudiant.php?id=' . $id);
            exit;
        }
        
        if (strlen($nom) < 2 || strlen($prenom) < 2) {
            setError('Le nom et prénom doivent contenir au moins 2 caractères.');
            header('Location: ../views/edit_etudiant.php?id=' . $id);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setError('Format d\'email invalide.');
            header('Location: ../views/edit_etudiant.php?id=' . $id);
            exit;
        }
        
        // Récupérer l'ancienne promotion avant mise à jour
        $oldPromotionStmt = $pdo->prepare("SELECT id_promotion FROM etudiants WHERE id = ?");
        $oldPromotionStmt->execute([$id]);
        $oldPromotion = $oldPromotionStmt->fetchColumn();

        $sql = "UPDATE etudiants SET nom = ?, prenom = ?, email = ?, id_promotion = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $email, $id_promotion, $id]);

        // Mettre à jour dans utilisateur
        $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM etudiants WHERE id = ?");
        $stmtUser->execute([$id]);
        $userId = $stmtUser->fetchColumn();

        if ($userId) {
            $stmtUpdateUser = $pdo->prepare("UPDATE utilisateur SET nom = ?, email = ? WHERE id = ?");
            $stmtUpdateUser->execute([$nom . ' ' . $prenom, $email, $userId]);
        }

        // Gérer l'assignation automatique aux espaces si la promotion a changé
        if ($oldPromotion != $id_promotion) {
            // Supprimer l'étudiant de tous les espaces liés à l'ancienne promotion
            $removeOldSpaces = $pdo->prepare("
                DELETE ee FROM etudiant_espace ee
                INNER JOIN promotion_espace pe ON ee.id_espace = pe.id_espace
                WHERE ee.id_etudiant = ? AND pe.id_promotion = ?
            ");
            $removeOldSpaces->execute([$id, $oldPromotion]);

            // Vérifier si la nouvelle promotion est assignée à exactement un espace
            $checkSpaces = $pdo->prepare("
                SELECT id_espace
                FROM promotion_espace
                WHERE id_promotion = ?
            ");
            $checkSpaces->execute([$id_promotion]);
            $assignedSpaces = $checkSpaces->fetchAll(PDO::FETCH_COLUMN);

            // Si la nouvelle promotion est assignée à exactement un espace, ajouter automatiquement l'étudiant à cet espace
            if (count($assignedSpaces) === 1) {
                $id_espace = $assignedSpaces[0];

                // Vérifier que l'étudiant n'est pas déjà dans cet espace
                $checkExisting = $pdo->prepare("
                    SELECT id FROM etudiant_espace
                    WHERE id_etudiant = ? AND id_espace = ?
                ");
                $checkExisting->execute([$id, $id_espace]);

                if ($checkExisting->rowCount() === 0) {
                    // Ajouter l'étudiant à l'espace
                    $stmtAssign = $pdo->prepare("
                        INSERT INTO etudiant_espace (id_etudiant, id_espace)
                        VALUES (?, ?)
                    ");
                    $stmtAssign->execute([$id, $id_espace]);
                }
            }
        }
        
        $redirect = "../views/etudiants.php";
    } 
    elseif ($type == 'formateur') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Validations
        if (empty($nom) || empty($email)) {
            setError('Tous les champs sont obligatoires.');
            header('Location: ../views/edit_formateur.php?id=' . $id);
            exit;
        }
        
        if (strlen($nom) < 2) {
            setError('Le nom doit contenir au moins 2 caractères.');
            header('Location: ../views/edit_formateur.php?id=' . $id);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setError('Format d\'email invalide.');
            header('Location: ../views/edit_formateur.php?id=' . $id);
            exit;
        }
        
        $sql = "UPDATE formateurs SET nom = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $email, $id]);
        
        // Mettre à jour dans utilisateur
        $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM formateurs WHERE id = ?");
        $stmtUser->execute([$id]);
        $userId = $stmtUser->fetchColumn();
        
        if ($userId) {
            $stmtUpdateUser = $pdo->prepare("UPDATE utilisateur SET nom = ?, email = ? WHERE id = ?");
            $stmtUpdateUser->execute([$nom, $email, $userId]);
        }
        
        $redirect = "../views/formateurs.php";
    }
    elseif ($type == 'espace') {
        $nom_espace = trim($_POST['nom_espace'] ?? '');
        $id_formateur = intval($_POST['id_formateur'] ?? 0);

        // Validations
        if (empty($nom_espace)) {
            setError("Le nom de l'espace est obligatoire.");
            header('Location: ../views/edit_espace.php?id=' . $id);
            exit;
        }

        if (strlen($nom_espace) < 3) {
            setError("Le nom de l'espace doit contenir au moins 3 caractères.");
            header('Location: ../views/edit_espace.php?id=' . $id);
            exit;
        }

        // Si un formateur est sélectionné, vérifier qu'il existe
        if ($id_formateur !== 0) {
            $checkFormateur = $pdo->prepare("SELECT id FROM formateurs WHERE id = ?");
            $checkFormateur->execute([$id_formateur]);

            if (!$checkFormateur->fetch()) {
                setError("Le formateur sélectionné n'existe pas.");
                header('Location: ../views/edit_espace.php?id=' . $id);
                exit;
            }
        }

        // Utiliser NULL si aucun formateur n'est sélectionné
        $id_formateur_value = $id_formateur === 0 ? NULL : $id_formateur;

        $sql = "UPDATE espaces_pedagogiques SET nom_espace = ?, id_formateur = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom_espace, $id_formateur_value, $id]);

        setSuccess('Espace mis à jour avec succès !');
        $redirect = "../views/consulter_espaces.php";
    }
    else {
        setError('Type de mise à jour non reconnu.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }

    if (!isset($_SESSION['success'])) {
        setSuccess('Mise à jour effectuée avec succès !');
    }
    
    header("Location: $redirect");
    exit;
    
} catch (PDOException $e) {
    error_log('Erreur update : ' . $e->getMessage());
    setError('Erreur lors de la mise à jour : ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}
?>