<?php
use PHPMailer\PHPMailer\PHPMailer;

function getMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gloriaabtti60@gmail.com';
    $mail->Password   = 'erpx hkpp uvya rxxp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom('gloriaabtti60@gmail.com', 'S-SETICE');
    $mail->CharSet    = 'UTF-8'; // Pour bien gérer les accents
    return $mail;
}