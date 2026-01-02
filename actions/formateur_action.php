<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';
require_once '../config/db.php';
require_once '../includes/mailer_config.php'; // On inclut la configuration centralisée
require_once '../includes/csrf.php'; // Protection CSRF
require_once '../includes/messages.php'; // Gestion des messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide. Rechargez la page et réessayez.');
    }
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $token = bin2hex(random_bytes(32));

    try {
        $pdo->beginTransaction();

        $stmtUser = $pdo->prepare("INSERT INTO utilisateur (nom, email, role, activation_token, is_active) VALUES (?, ?, 'FORMATEUR', ?, 0)");
        $stmtUser->execute([$nom, $email, $token]);
        $id_utilisateur = $pdo->lastInsertId();

        $stmtProf = $pdo->prepare("INSERT INTO formateurs (nom, email, id_utilisateur) VALUES (?, ?, ?)");
        $stmtProf->execute([$nom, $email, $id_utilisateur]);

        $pdo->commit();

        // Utilisation de la fonction centralisée
        $lien = "http://localhost/S-SETICE_9.1/views/activation.php?token=$token";
        
        $mail = getMailer(); // Appel de la fonction de mailer_config.php
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Creation de votre compte Formateur S-SETICE';
        $mail->Body    = "
            <h2>Bienvenue M./Mme $nom</h2>
            <p>Votre accès formateur a été créé. Veuillez activer votre compte en cliquant ici :</p>
            <p><a href='$lien' style='padding:10px; background:#1a4a8e; color:white; text-decoration:none;'>Activer mon espace formateur</a></p>
        ";

        $mail->send();
        setSuccess('Compte formateur créé. Email d\'activation envoyé.');
        header('Location: ../views/formateurs.php');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setError('Erreur lors de la création : ' . $e->getMessage());
        header('Location: ../views/formateurs.php');
    }
}