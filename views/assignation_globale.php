<?php 
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages
$espaces = $pdo->query("SELECT * FROM espaces_pedagogiques ORDER BY nom_espace ASC")->fetchAll();
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom ASC")->fetchAll();
$etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nom ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Assignations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 40px;">Assignations Pédagogiques</h1>
        
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            
            <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #edf2f7; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h3 style="margin-bottom: 20px; color: #1a4a8e;"><i class="fas fa-users"></i> Par Promotion Entière</h3>
                <form action="../actions/pedagogique_action.php" method="POST">
                    <input type="hidden" name="type_action" value="assigner_promo">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#64748b;">Promotion</label>
                    <select name="id_promotion" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px;">
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nom'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#64748b;">Espace (Matière)</label>
                    <select name="id_espace" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px;">
                        <?php foreach($espaces as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= $e['nom_espace'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" style="width:100%; background:#1a4a8e; color:white; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Assigner la Promotion</button>
                </form>
            </div>

            <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #edf2f7; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h3 style="margin-bottom: 20px; color: #10b981;"><i class="fas fa-user"></i> Par Étudiant Individuel</h3>
                <form action="../actions/pedagogique_action.php" method="POST">
                    <input type="hidden" name="type_action" value="assigner_etudiant_seul">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#64748b;">Sélectionner l'Étudiant</label>
                    <select name="id_etudiant" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px;">
                        <?php foreach($etudiants as $et): ?>
                            <option value="<?= $et['id'] ?>"><?= $et['nom'] ?> <?= $et['prenom'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#64748b;">Espace (Matière)</label>
                    <select name="id_espace" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px;">
                        <?php foreach($espaces as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= $e['nom_espace'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" style="width:100%; background:#10b981; color:white; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Assigner l'Étudiant</button>
                </form>
            </div>

        </div>
    </main>
</body>
</html>