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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide. Rechargez la page et réessayez.');
    }
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $id_promotion = $_POST['id_promotion'];
    $token = bin2hex(random_bytes(32));

    try {
        $pdo->beginTransaction(); 

        $stmtUser = $pdo->prepare("INSERT INTO utilisateur (nom, email, role, activation_token, is_active) VALUES (?, ?, 'ETUDIANT', ?, 0)");
        $stmtUser->execute([$nom, $email, $token]);
        $id_utilisateur = $pdo->lastInsertId(); 

        $stmtStudent = $pdo->prepare("INSERT INTO etudiants (nom, prenom, email, id_promotion, id_utilisateur) VALUES (?, ?, ?, ?, ?)");
        $stmtStudent->execute([$nom, $prenom, $email, $id_promotion, $id_utilisateur]);

        $pdo->commit(); 

        // Utilisation de la fonction centralisée
        $lien = "http://localhost/S-SETICE_9.1/views/activation.php?token=$token";
        
        $mail = getMailer(); // Appel de la fonction de mailer_config.php
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Activation de votre compte S-SETICE';
        $mail->Body    = "Bonjour $nom, cliquez ici pour activer votre compte : <a href='$lien'>Activer mon compte</a>";

        $mail->send();
        setSuccess('Étudiant créé avec succès. Email d\'activation envoyé.');
        header('Location: ../views/etudiants.php');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setError('Erreur lors de la création : ' . $e->getMessage());
        header('Location: ../views/etudiants.php');
    }
}