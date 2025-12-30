<?php
session_start();
require_once('../config/db.php');

// On récupère les promotions pour le menu déroulant
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom ASC")->fetchAll();

// On récupère les étudiants avec le nom de leur promotion
$queryEtudiants = $pdo->query("
    SELECT e.*, p.nom as promo_nom 
    FROM etudiants e 
    LEFT JOIN promotions p ON e.id_promotion = p.id 
    ORDER BY e.nom ASC
");
$etudiants = $queryEtudiants->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Étudiants</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Gestion des Étudiants</h1>
            <p style="color: #64748b;">Enregistrez et gérez les élèves.</p>
        </header>

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7;">
            <form action="../actions/student_action.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Nom</label>
                    <input type="text" name="nom" required style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Prénom</label>
                    <input type="text" name="prenom" required style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Email</label>
                    <input type="email" name="email" required style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Promotion</label>
                    <select name="id_promotion" required style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white;">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">Enregistrer l'étudiant</button>
            </form>
        </div>

        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; margin-top: 40px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b;">Nom & Prénom</th>
                        <th style="padding: 20px; color: #64748b;">Promotion</th>
                        <th style="padding: 20px; color: #64748b; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($etudiants as $e): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?></td>
                        <td style="padding: 20px; color: #1a4a8e; font-weight: 600;"><?= htmlspecialchars($e['promo_nom'] ?? 'Non assigné') ?></td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_etudiant.php?id=<?= $e['id'] ?>" style="color: #1a4a8e; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="../actions/delete_action.php?type=etudiant&id=<?= $e['id'] ?>" onclick="return confirm('Supprimer cet étudiant ?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>