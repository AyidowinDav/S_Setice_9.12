<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendMail($to, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ayidowindavidali@gmail.com';      // 🔴 À MODIFIER
        $mail->Password   = 'mot_de_passe_app';       // 🔴 Mot de passe d’application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Expéditeur / Destinataire
        $mail->setFrom('tonmail@gmail.com', 'S-SETICE');
        $mail->addAddress($to);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($message);
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
