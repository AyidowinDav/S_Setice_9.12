<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';
require_once '../config/db.php';
require_once '../includes/mailer_config.php';

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

if (empty($token)) {
    die("Lien d'activation invalide. Aucun token fourni.");
}

// Vérification du token
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE activation_token = ? AND is_active = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Ce lien d'activation est invalide ou le compte est déjà actif.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($password) || empty($confirm)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Activation du compte
        $update = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ?, is_active = 1, activation_token = NULL WHERE id = ?");
        
        if ($update->execute([$hashedPassword, $user['id']])) {
            // Notification au directeur
            try {
                $mail = getMailer();
                $mail->addAddress('gloriaabatti60@gmail.com');
                $mail->isHTML(true);
                $mail->Subject = 'Nouveau compte activé - S-SETICE';
                $mail->Body = "
                    <h3>Notification d'activation de compte</h3>
                    <p>Le compte de <strong>" . htmlspecialchars($user['nom']) . "</strong> a été activé.</p>
                    <p><strong>Email :</strong> " . htmlspecialchars($user['email']) . "</p>
                    <p><strong>Rôle :</strong> " . htmlspecialchars($user['role']) . "</p>
                    <p><strong>Date :</strong> " . date('d/m/Y H:i:s') . "</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                // On ne bloque pas le processus
            }

            $success = "Votre compte a été activé avec succès ! <br><a href='../index.php' style='color: #1a4a8e; font-weight: bold;'>Connectez-vous maintenant</a>";
        } else {
            $error = "Erreur lors de l'activation. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation de compte - S-SETICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .activation-container {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 100%;
    }
    .logo {
        text-align: center;
        margin-bottom: 30px;
    }
    .logo h1 {
        color: #1a4a8e;
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 5px;
    }
    .logo p {
        color: #64748b;
        font-size: 14px;
    }
    h2 {
        color: #1e293b;
        margin-bottom: 10px;
        font-size: 24px;
    }
    .user-info {
        background: #f8fafc;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 4px solid #1a4a8e;
    }
    .user-info p {
        color: #475569;
        margin: 5px 0;
        font-size: 14px;
    }
    .user-info strong {
        color: #1e293b;
    }
    .alert {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .alert-error {
        background: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }
    .alert-success {
        background: #efe;
        color: #363;
        border: 1px solid #cfc;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        color: #475569;
        font-weight: 600;
        font-size: 14px;
    }
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
    }
    input[type="password"]:focus {
        outline: none;
        border-color: #1a4a8e;
        box-shadow: 0 0 0 3px rgba(26, 74, 142, 0.1);
    }
    .password-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
    }
    .btn {
        width: 100%;
        padding: 15px;
        background: #1a4a8e;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn:hover {
        background: #0f3460;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(26, 74, 142, 0.3);
    }
    .back-link {
        text-align: center;
        margin-top: 20px;
    }
    .back-link a {
        color: #1a4a8e;
        text-decoration: none;
        font-size: 14px;
    }
    .back-link a:hover {
        text-decoration: underline;
    }
    </style>
</head>
<body>
    <div class="activation-container">
        <div class="logo">
            <h1>S-SETICE</h1>
            <p>Système de Gestion de Formation</p>
        </div>

        <h2>Activation de compte</h2>

        <div class="user-info">
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Rôle :</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-error">
            <strong>⚠️ Erreur :</strong> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="alert alert-success">
            <strong>✅ Succès :</strong> <?php echo $success; ?>
        </div>
        <?php else: ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required>
                <div class="password-hint">Minimum 6 caractères</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmez le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Activer mon compte</button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="../index.php">← Retour à la connexion</a>
        </div>
    </div>
</body>
</html>