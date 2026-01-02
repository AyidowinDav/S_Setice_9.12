<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

// On récupère les données pour les menus déroulants
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom ASC")->fetchAll();
$espaces = $pdo->query("SELECT * FROM espaces_pedagogiques ORDER BY nom_espace ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Assigner Promotion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">

    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Gestion Pédagogique</h1>
            <p style="color: #64748b;">Liez une promotion entière à un espace de cours.</p>
        </header>

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

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7;">
            <form action="../actions/pedagogique_action.php" method="POST">
                <input type="hidden" name="type_action" value="assigner_promo">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:10px; font-weight:600; color:#475569;">Sélectionner la Promotion</label>
                    <select name="id_promotion" required style="width:100%; padding:14px; border:1px solid #e2e8f0; border-radius:12px; background:white; font-size:15px;">
                        <option value="">-- Choisir une promotion --</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?> (<?= $p['annee'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 35px;">
                    <label style="display:block; margin-bottom:10px; font-weight:600; color:#475569;">Sélectionner l'Espace (Matière)</label>
                    <select name="id_espace" required style="width:100%; padding:14px; border:1px solid #e2e8f0; border-radius:12px; background:white; font-size:15px;">
                        <option value="">-- Choisir un espace --</option>
                        <?php foreach($espaces as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom_espace']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:16px; border-radius:14px; cursor:pointer; font-weight:700; width:100%; font-size:16px; transition: 0.3s;">
                    Confirmer l'Assignation
                </button>
            </form>
        </div>
    </main>
</body>
</html>