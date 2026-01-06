<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

// Récupérer les données pour les formulaires
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom")->fetchAll();
$etudiants = $pdo->query("
    SELECT e.id, e.nom, e.prenom, p.nom as promo_nom 
    FROM etudiants e 
    JOIN promotions p ON e.id_promotion = p.id 
    ORDER BY e.nom
")->fetchAll();
$espaces = $pdo->query("
    SELECT e.id, e.nom_espace, f.nom as formateur_nom 
    FROM espaces_pedagogiques e
    LEFT JOIN formateurs f ON e.id_formateur = f.id
    ORDER BY e.nom_espace
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Assignations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Gestion des Assignations</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Assignez des promotions aux espaces pédagogiques</p>

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

        <div style="max-width: 600px; margin: 0 auto;">
            <!-- Assignation par promotion uniquement -->
            <div style="background: white; padding: 40px; border-radius: 20px; border: 1px solid #edf2f7;">
                <h3 style="margin-top: 0; color: #1e293b; font-size: 20px;">
                    <i class="fas fa-users" style="color: #1a4a8e;"></i> Assigner une Promotion à un Espace
                </h3>
                <p style="color: #64748b; margin-bottom: 30px; font-size: 14px;">
                    Tous les étudiants de la promotion sélectionnée seront automatiquement ajoutés à l'espace pédagogique
                </p>

                <form action="../actions/pedagogique_action.php" method="POST">
                    <input type="hidden" name="type_action" value="assigner_promo">

                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Promotion *</label>
                        <select name="id_promotion" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                            <option value="">-- Sélectionner une promotion --</option>
                            <?php foreach($promotions as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['annee']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Espace pédagogique *</label>
                        <select name="id_espace" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                            <option value="">-- Sélectionner un espace --</option>
                            <?php foreach($espaces as $e): ?>
                                <option value="<?= $e['id'] ?>">
                                    <?= htmlspecialchars($e['nom_espace']) ?>
                                    <?= $e['formateur_nom'] ? ' (' . htmlspecialchars($e['formateur_nom']) . ')' : ' (Sans formateur)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">
                        <i class="fas fa-link"></i> Assigner la promotion
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Auto-hide messages after 10 seconds
    setTimeout(function() {
        const messages = document.querySelectorAll('div[style*="background: #fee"], div[style*="background: #efe"]');
        messages.forEach(function(message) {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        });
    }, 10000);
    </script>
</body>
</html>
