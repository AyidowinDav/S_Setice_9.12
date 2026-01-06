<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: etudiants.php');
    exit;
}

// Récupérer l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$id]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    setError('Étudiant introuvable.');
    header('Location: etudiants.php');
    exit;
}

// Récupérer les promotions
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Modifier Étudiant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <a href="etudiants.php" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Retour aux étudiants
        </a>

        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 40px;">Modifier l'Étudiant</h1>

        <?php $error = getError(); if ($error): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 700px; border: 1px solid #edf2f7;">
            <form action="../actions/update_action.php" method="POST">
                <input type="hidden" name="type" value="etudiant">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom *</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required 
                            style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Prénom *</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required 
                            style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($etudiant['email']) ?>" required 
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Promotion *</label>
                    <select name="id_promotion" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $etudiant['id_promotion'] == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['annee']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="flex: 1; background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600;">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="etudiants.php" style="flex: 0.4; background:#6b7280; color:white; padding:15px; border-radius:12px; text-align:center; text-decoration:none; font-weight:600;">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>