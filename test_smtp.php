<?php
/**
 * FICHIER DE TEST SMTP
 * Placez le fichier à la racine du projet et accédez via le navigateur
 * Ex: http://localhost/S-SETICE_9.1/test_smtp.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

echo "<h2>Test de Configuration SMTP</h2>";
echo "<hr>";

// 1. Vérification des extensions PHP
echo "<h3>1. Extensions PHP</h3>";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Activée' : '❌ Désactivée (REQUIS)') . "<br>";
echo "MBString: " . (extension_loaded('mbstring') ? '✅ Activée' : '⚠️ Désactivée (Recommandé)') . "<br>";
echo "Socket: " . (extension_loaded('socket') ? '✅ Activée' : '⚠️ Désactivée') . "<br>";
echo "<hr>";

// 2. Test de connexion réseau au serveur SMTP
echo "<h3>2. Test de connexion réseau</h3>";
$host = 'smtp.gmail.com';
$port = 587;
$timeout = 10;

$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($connection) {
    echo "✅ Connexion réseau à $host:$port réussie<br>";
    fclose($connection);
} else {
    echo "❌ Impossible de se connecter à $host:$port<br>";
    echo "Erreur ($errno): $errstr<br>";
    echo "<strong>Solution:</strong> Vérifiez votre pare-feu ou antivirus<br>";
}
echo "<hr>";

// 3. Configuration et test PHPMailer
echo "<h3>3. Test d'envoi avec PHPMailer</h3>";

$mail = new PHPMailer(true);

try {
    // Configuration SMTP avec debug activé
    $mail->SMTPDebug = 2; // Active les messages de debug détaillés
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG SMTP: $str<br>";
    };
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'gloriaabatti60@gmail.com'; // Votre email
    $mail->Password = 'erpxhkppuvyarxxp'; // Votre mot de passe d'application
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom('gloriaabatti60@gmail.com', 'S-SETICE');
    $mail->addAddress('gloriaabatti60@gmail.com', 'Test'); // Envoi à vous-même pour tester
    
    $mail->isHTML(true);
    $mail->Subject = 'Test SMTP - S-SETICE';
    $mail->Body = '<h1>Test réussi !</h1><p>La configuration SMTP fonctionne correctement.</p>';
    
    if ($mail->send()) {
        echo "<br><strong style='color: green;'>✅ EMAIL ENVOYÉ AVEC SUCCÈS !</strong><br>";
        echo "Vérifiez votre boîte de réception.<br>";
    }
    
} catch (Exception $e) {
    echo "<br><strong style='color: red;'>❌ ERREUR D'ENVOI</strong><br>";
    echo "Message d'erreur: {$mail->ErrorInfo}<br>";
    echo "<br><strong>Solutions possibles :</strong><br>";
    echo "1. Vérifiez que le mot de passe d'application Gmail est correct<br>";
    echo "2. Activez 'Autoriser les applications moins sécurisées' dans Gmail<br>";
    echo "3. Désactivez temporairement votre antivirus/pare-feu<br>";
    echo "4. Essayez le port 465 avec SSL au lieu de 587 avec TLS<br>";
}

echo "<hr>";

// 4. Informations système
echo "<h3>4. Informations système</h3>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Système: " . PHP_OS . "<br>";
echo "Date/Heure serveur: " . date('Y-m-d H:i:s') . "<br>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
</style>