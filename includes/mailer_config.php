<?php
/**
 * Configuration PHPMailer
 * S-SETICE - Système de Gestion de Formation
 */

// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getMailer() {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gloriaabatti60@gmail.com';
        $mail->Password = 'erpxhkppuvyarxxp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 30;
        $mail->SMTPDebug = 0;
        $mail->setFrom('gloriaabatti60@gmail.com', 'S-SETICE');
        $mail->CharSet = 'UTF-8';
        
        if (@$mail->smtpConnect()) {
            $mail->smtpClose();
            return $mail;
        }
    } catch (Exception $e) {
        // Essayer config 2
    }
    
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gloriaabatti60@gmail.com';
        $mail->Password = 'erpxhkppuvyarxxp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->Timeout = 30;
        $mail->SMTPDebug = 0;
        $mail->setFrom('gloriaabatti60@gmail.com', 'S-SETICE');
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    } catch (Exception $e) {
        return $mail;
    }
}

function sendActivationEmail($toEmail, $toName, $token, $role = 'ETUDIANT') {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        
        $lien = "http://localhost/S-SETICE_9.1/views/activation.php?token=$token";
        
        if ($role === 'FORMATEUR') {
            $mail->Subject = 'Création de votre compte Formateur - S-SETICE';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>S-SETICE</h1>
                    </div>
                    <div style='padding: 40px;'>
                        <h2 style='color: #1a4a8e;'>Bienvenue, $toName !</h2>
                        <p>Votre compte <strong>Formateur</strong> a été créé.</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$lien' style='background: #1a4a8e; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block;'>Activer mon compte</a>
                        </div>
                        <p style='font-size: 13px; color: #64748b;'>Ou copiez ce lien : $lien</p>
                    </div>
                </div>
            ";
        } else {
            $mail->Subject = 'Activation de votre compte Étudiant - S-SETICE';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>S-SETICE</h1>
                    </div>
                    <div style='padding: 40px;'>
                        <h2 style='color: #1a4a8e;'>Bienvenue, $toName !</h2>
                        <p>Votre compte <strong>Étudiant</strong> a été créé.</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$lien' style='background: #1a4a8e; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block;'>Activer mon compte</a>
                        </div>
                        <p style='font-size: 13px; color: #64748b;'>Ou copiez ce lien : $lien</p>
                    </div>
                </div>
            ";
        }
        
        $result = $mail->send();
        return ['success' => true, 'message' => 'Email envoyé à ' . $toEmail];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}

function sendNotificationToDirector($subject, $message) {
    try {
        $mail = getMailer();
        $mail->addAddress('gloriaabatti60@gmail.com');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

function sendGroupNotificationEmail($toEmail, $toName, $groupName, $workTitle, $workDescription, $spaceName, $trainerName) {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle assignation de travail en groupe - S-SETICE';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>S-SETICE</h1>
                </div>
                <div style='padding: 40px;'>
                    <h2 style='color: #1a4a8e;'>Bonjour $toName,</h2>
                    <p>Vous avez été assigné à un nouveau travail en groupe.</p>

                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #1a4a8e;'>Détails du travail :</h3>
                        <p><strong>Titre :</strong> $workTitle</p>
                        <p><strong>Description :</strong> $workDescription</p>
                        <p><strong>Espace pédagogique :</strong> $spaceName</p>
                        <p><strong>Formateur :</strong> $trainerName</p>
                        <p><strong>Votre groupe :</strong> $groupName</p>
                    </div>

                    <p>Vous pouvez consulter les détails complets et soumettre votre travail depuis votre espace étudiant.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/S-SETICE_9.1/views/mes_travaux_etudiant.php' style='background: #1a4a8e; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block;'>Accéder à mes travaux</a>
                    </div>

                    <p style='font-size: 13px; color: #64748b;'>Si vous avez des questions, contactez votre formateur.</p>
                </div>
            </div>
        ";

        $result = $mail->send();
        return ['success' => true, 'message' => 'Email envoyé à ' . $toEmail];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}

function sendIndividualNotificationEmail($toEmail, $toName, $workTitle, $workDescription, $spaceName, $trainerName) {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle assignation de travail individuel - S-SETICE';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>S-SETICE</h1>
                </div>
                <div style='padding: 40px;'>
                    <h2 style='color: #1a4a8e;'>Bonjour $toName,</h2>
                    <p>Vous avez été assigné à un nouveau travail individuel.</p>

                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #1a4a8e;'>Détails du travail :</h3>
                        <p><strong>Titre :</strong> $workTitle</p>
                        <p><strong>Description :</strong> $workDescription</p>
                        <p><strong>Espace pédagogique :</strong> $spaceName</p>
                        <p><strong>Formateur :</strong> $trainerName</p>
                    </div>

                    <p>Vous pouvez consulter les détails complets et soumettre votre travail depuis votre espace étudiant.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/S-SETICE_9.1/views/mes_travaux_etudiant.php' style='background: #1a4a8e; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block;'>Accéder à mes travaux</a>
                    </div>

                    <p style='font-size: 13px; color: #64748b;'>Si vous avez des questions, contactez votre formateur.</p>
                </div>
            </div>
        ";

        $result = $mail->send();
        return ['success' => true, 'message' => 'Email envoyé à ' . $toEmail];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}
?>