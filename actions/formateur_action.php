<?php
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

/**
 * SSS-2 : CRÉATION D'UN COMPTE FORMATEUR
 * 
 * Cas Favorable : Email envoyé + message de succès
 * Cas Défavorable : Email existant ou format erroné
 */

// Vérification autorisation
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    setError('Accès non autorisé.');
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/formateurs.php');
    exit;
}

// Vérification CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setError('Token CSRF invalide. Rechargez la page.');
    header('Location: ../views/formateurs.php');
    exit;
}

// Récupération données
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');

// VALIDATION 1 : Champs obligatoires
if (empty($nom) || empty($email)) {
    setError('Tous les champs sont obligatoires.');
    header('Location: ../views/formateurs.php');
    exit;
}

// VALIDATION 2 : Nom minimum 2 caractères
if (strlen($nom) < 2) {
    setError('Le nom doit contenir au moins 2 caractères.');
    header('Location: ../views/formateurs.php');
    exit;
}

// VALIDATION 3 : Format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setError('Format d\'email invalide.');
    header('Location: ../views/formateurs.php');
    exit;
}

// VALIDATION 4 : Email avec domaine valide
$email_parts = explode('@', $email);
if (count($email_parts) !== 2 || empty($email_parts[0]) || empty($email_parts[1])) {
    setError('Email erroné. Vérifiez le format.');
    header('Location: ../views/formateurs.php');
    exit;
}

try {
    // CAS DÉFAVORABLE : Vérifier si email existe déjà
    $checkEmail = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $checkEmail->execute([$email]);
    
    if ($checkEmail->rowCount() > 0) {
        setError('Cet email est déjà utilisé dans le système.');
        header('Location: ../views/formateurs.php');
        exit;
    }

    // Génération token d'activation
    $token = bin2hex(random_bytes(32));

    // Transaction pour cohérence
    $pdo->beginTransaction();

    // CAS FAVORABLE : Créer utilisateur
    $stmtUser = $pdo->prepare("
        INSERT INTO utilisateur (nom, email, role, activation_token, is_active, mot_de_passe) 
        VALUES (?, ?, 'FORMATEUR', ?, 0, '')
    ");
    $stmtUser->execute([$nom, $email, $token]);
    $id_utilisateur = $pdo->lastInsertId();

    // Créer formateur
    $stmtFormateur = $pdo->prepare("
        INSERT INTO formateurs (nom, email, id_utilisateur) 
        VALUES (?, ?, ?)
    ");
    $stmtFormateur->execute([$nom, $email, $id_utilisateur]);

    $pdo->commit();

    // CAS FAVORABLE : Envoi email
    $emailResult = sendActivationEmail($email, $nom, $token, 'FORMATEUR');
    
    if ($emailResult['success']) {
        setSuccess('Compte formateur créé avec succès ! Email d\'activation envoyé à ' . $email);
    } else {
        setWarning('Compte créé mais erreur d\'envoi email : ' . $emailResult['message']);
    }

    // Notification directeur
    sendNotificationToDirector(
        'Nouveau formateur créé',
        "Le formateur <strong>$nom</strong> ($email) a été créé."
    );

    header('Location: ../views/formateurs.php');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur création formateur : ' . $e->getMessage());
    setError('Erreur lors de la création. Réessayez.');
    header('Location: ../views/formateurs.php');
    exit;
}
?>