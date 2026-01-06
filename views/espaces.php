<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

$formateurs = $pdo->query("SELECT * FROM formateurs ORDER BY nom")->fetchAll();
if (isset($_GET['debug'])) {
    echo '<pre>' . htmlspecialchars(print_r($formateurs, true)) . '</pre>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Espaces Pédagogiques</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Création d'Espace Pédagogique</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Créez un nouvel espace et assignez-lui un formateur</p>

        <?php $error = getError(); if ($error): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php $success = getSuccess(); if ($success): ?>
        <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 600px; border: 1px solid #edf2f7;">
            <form action="../actions/pedagogique_action.php" method="POST">
                <input type="hidden" name="type_action" value="creer_espace">
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom de l'espace *</label>
                    <input type="text" name="nom_espace" placeholder="Ex: Mathématiques Avancées" required 
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Formateur responsable *</label>
                    <select name="id_formateur" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                        <option value="">-- Sélectionner un formateur --</option>
                        <?php foreach($formateurs as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?> (<?= htmlspecialchars($f['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">
                    <i class="fas fa-plus-circle"></i> Créer l'espace pédagogique
                </button>
            </form>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="consulter_espaces.php" style="color: #1a4a8e; text-decoration: none; font-weight: 600;">
                <i class="fas fa-eye"></i> Consulter tous les espaces →
            </a>
        </div>
    </main>
</body>
</html>