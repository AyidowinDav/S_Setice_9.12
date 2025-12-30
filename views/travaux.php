<?php
session_start();
require_once('../config/db.php');

// Récupération des étudiants et de leur promotion pour l'affichage
$sql = "SELECT etudiants.id, etudiants.nom, etudiants.prenom, promotions.nom AS promo_nom 
        FROM etudiants 
        JOIN promotions ON etudiants.id_promotion = promotions.id 
        ORDER BY promotions.nom, etudiants.nom";
$etudiants = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Travaux & Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">

    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Travaux & Notes</h1>
            <p style="color: #64748b;">Attribuez des notes et des appréciations aux étudiants.</p>
        </header>

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 700px; border: 1px solid #edf2f7;">
            <form action="../actions/note_action.php" method="POST">
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Sélectionner l'Étudiant</label>
                    <select name="id_etudiant" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:white;">
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach($etudiants as $e): ?>
                            <option value="<?= $e['id'] ?>">
                                <?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?> (<?= $e['promo_nom'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Note (sur 20)</label>
                        <input type="number" name="note" step="0.5" min="0" max="20" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                    <div style="flex: 2;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Libellé du devoir</label>
                        <input type="text" name="titre_travail" placeholder="Ex: Projet PHP Final" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Appréciation</label>
                    <textarea name="appreciation" rows="4" placeholder="Excellent travail, continuez ainsi..." style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px; font-family: inherit;"></textarea>
                </div>

                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px 30px; border-radius:12px; cursor:pointer; font-weight:600; width:100%; font-size:16px;">
                    Enregistrer la note
                </button>
            </form>
        </div>
    </main>
</body>
</html>