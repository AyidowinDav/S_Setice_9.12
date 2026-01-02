<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM formateurs WHERE id = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();

if(!$f) die("Formateur introuvable");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Modifier Formateur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="font-family:'Inter', sans-serif; background:#f8fafc; padding:50px;">
    <div style="background: white; padding: 40px; border-radius: 20px; max-width: 500px; margin: auto; border: 1px solid #eee;">
        <h2 style="color:#1a4a8e;">Modifier Formateur</h2>
        
        <?php $error = getError(); if ($error): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fcc;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php $success = getSuccess(); if ($success): ?>
            <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cfc;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form action="../actions/update_action.php" method="POST">
            <input type="hidden" name="type" value="formateur">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">

            <label style="display:block; margin-bottom:10px;">Nom Complet</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($f['nom']) ?>" required style="width:100%; padding:10px; margin-bottom:20px; border-radius:8px; border:1px solid #ddd;">

            <label style="display:block; margin-bottom:10px;">Email Professionnel</label>
            <input type="email" name="email" value="<?= htmlspecialchars($f['email']) ?>" required style="width:100%; padding:10px; margin-bottom:20px; border-radius:8px; border:1px solid #ddd;">

            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:12px; border-radius:10px; width:100%; cursor:pointer;">Mettre à jour</button>
            <p style="text-align:center;"><a href="formateurs.php" style="color:#64748b;">Retour</a></p>
        </form>
    </div>
</body>
</html>