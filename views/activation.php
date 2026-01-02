<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';
require_once '../config/db.php';
require_once '../includes/mailer_config.php'; // Ajout pour utiliser getMailer()

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

// 1. Vérification avec le nom de colonne correct : activation_token
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE activation_token = ? AND is_active = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Ce lien d'activation est invalide ou le compte est déjà actif.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password === $confirm) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 2. Mise à jour : On active et on vide activation_token
        $update = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ?, is_active = 1, activation_token = NULL WHERE id = ?");
        
        if ($update->execute([$hashedPassword, $user['id']])) {
            
            // 3. Notification au Directeur
            try {
                $mail = getMailer(); // Utilisation de la config centralisée
                $mail->setFrom('admin@votre-ecole.com', 'S-SETICE Notification'); // Ajuster l'expéditeur si nécessaire
                $mail->addAddress('directeur@ecole.com'); 
                $mail->isHTML(true);
                $mail->Subject = 'Compte Etudiant Active';
                $mail->Body = "Le compte de <b>" . htmlspecialchars($user['nom']) . "</b> (" . htmlspecialchars($user['email']) . ") est désormais actif.";
                $mail->send();
            } catch (Exception $e) { }


            $success = "Votre compte a été activé avec succès ! <a href='../index.php'>Connectez-vous ici</a>";
        }
    } else {
        $error = "Les mots de passe ne sont pas identiques.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de compte - S-SETICE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login">
        <div class="wrapper">
            <form action="" method="POST">
                <h1>Activation</h1>
                <p>Définissez votre mot de passe pour : <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
                
                <?php if($error): ?> <p style="color:red; text-align:center;"><?php echo $error; ?></p> <?php endif; ?>
                <?php if($success): ?> <p style="color:green; text-align:center;"><?php echo $success; ?></p> <?php endif; ?>

                <?php if(!$success): ?>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Nouveau mot de passe" required>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
                </div>
                <button type="submit" class="btn">Activer mon compte</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>