ème refuse qu'il crée un travail pas<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';
require_once '../config/db.php';
require_once '../includes/mailer_config.php';
require_once '../includes/csrf.php';
require_once '../includes/messages.php';



// Vérification autorisation
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    setError('Accès non autorisé.');
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/etudiants.php');
    exit;
}

// Vérification CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setError('Token CSRF invalide.');
    header('Location: ../views/etudiants.php');
    exit;
}

// Récupération données
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$id_promotion = intval($_POST['id_promotion'] ?? 0);

// VALIDATION 1 : Champs obligatoires
if (empty($nom) || empty($prenom) || empty($email) || $id_promotion === 0) {
    setError('Tous les champs sont obligatoires.');
    header('Location: ../views/etudiants.php');
    exit;
}

// VALIDATION 2 : Longueur minimale
if (strlen($nom) < 2 || strlen($prenom) < 2) {
    setError('Le nom et le prénom doivent contenir au moins 2 caractères.');
    header('Location: ../views/etudiants.php');
    exit;
}

// VALIDATION 3 : Format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setError('Format d\'email invalide.');
    header('Location: ../views/etudiants.php');
    exit;
}

try {
    // CAS DÉFAVORABLE : Email déjà utilisé
    $checkEmail = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $checkEmail->execute([$email]);
    
    if ($checkEmail->rowCount() > 0) {
        setError('Cet email est déjà utilisé.');
        header('Location: ../views/etudiants.php');
        exit;
    }

    // CAS DÉFAVORABLE : Étudiant existe déjà dans cette promotion
    $checkStudent = $pdo->prepare("
        SELECT e.id, p.nom as promo_nom 
        FROM etudiants e
        JOIN promotions p ON e.id_promotion = p.id
        WHERE e.nom = ? AND e.prenom = ? AND e.id_promotion = ?
    ");
    $checkStudent->execute([$nom, $prenom, $id_promotion]);
    
    if ($checkStudent->rowCount() > 0) {
        $existingStudent = $checkStudent->fetch();
        setError('Cet étudiant existe déjà dans la promotion ' . $existingStudent['promo_nom'] . '.');
        header('Location: ../views/etudiants.php');
        exit;
    }

    // Vérifier que la promotion existe
    $checkPromo = $pdo->prepare("SELECT id, nom FROM promotions WHERE id = ?");
    $checkPromo->execute([$id_promotion]);
    
    if ($checkPromo->rowCount() === 0) {
        setError('La promotion sélectionnée n\'existe pas.');
        header('Location: ../views/etudiants.php');
        exit;
    }

    // Génération token
    $token = bin2hex(random_bytes(32));

    // Transaction
    $pdo->beginTransaction();

    // CAS FAVORABLE : Créer utilisateur
    $stmtUser = $pdo->prepare("
        INSERT INTO utilisateur (nom, email, role, activation_token, is_active, mot_de_passe) 
        VALUES (?, ?, 'ETUDIANT', ?, 0, '')
    ");
    $stmtUser->execute([$nom . ' ' . $prenom, $email, $token]);
    $id_utilisateur = $pdo->lastInsertId();

    // Créer étudiant
    $stmtStudent = $pdo->prepare("
        INSERT INTO etudiants (nom, prenom, email, id_promotion, id_utilisateur)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtStudent->execute([$nom, $prenom, $email, $id_promotion, $id_utilisateur]);
    $id_etudiant = $pdo->lastInsertId();

    // Vérifier si la promotion est assignée à exactement un espace
    $checkSpaces = $pdo->prepare("
        SELECT id_espace
        FROM promotion_espace
        WHERE id_promotion = ?
    ");
    $checkSpaces->execute([$id_promotion]);
    $assignedSpaces = $checkSpaces->fetchAll(PDO::FETCH_COLUMN);

    // Si la promotion est assignée à exactement un espace, ajouter automatiquement l'étudiant à cet espace
    if (count($assignedSpaces) === 1) {
        $id_espace = $assignedSpaces[0];

        // Vérifier que l'étudiant n'est pas déjà dans cet espace
        $checkExisting = $pdo->prepare("
            SELECT id FROM etudiant_espace
            WHERE id_etudiant = ? AND id_espace = ?
        ");
        $checkExisting->execute([$id_etudiant, $id_espace]);

        if ($checkExisting->rowCount() === 0) {
            // Ajouter l'étudiant à l'espace
            $stmtAssign = $pdo->prepare("
                INSERT INTO etudiant_espace (id_etudiant, id_espace)
                VALUES (?, ?)
            ");
            $stmtAssign->execute([$id_etudiant, $id_espace]);
        }
    }

    $pdo->commit();

    // CAS FAVORABLE : Envoi email
    $emailResult = sendActivationEmail($email, $nom . ' ' . $prenom, $token, 'ETUDIANT');
    
    if ($emailResult['success']) {
        setSuccess('Étudiant créé avec succès ! Email d\'activation envoyé à ' . $email);
    } else {
        setWarning('Étudiant créé mais erreur d\'envoi email : ' . $emailResult['message']);
    }

    header('Location: ../views/etudiants.php');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur création étudiant : ' . $e->getMessage());
    setError('Erreur lors de la création. Réessayez.');
    header('Location: ../views/etudiants.php');
    exit;
}
?>