<?php 
session_start();
require_once('../config/db.php');
// Récupération de toutes les promotions
$promos = $pdo->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Promotions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a;">Gestion des Promotions</h1>
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7; margin-top:30px;">
            <form action="../actions/promotion_action.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom de la Promotion</label>
                    <input type="text" name="nom" placeholder="Ex: Licence 3 INFO" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Année Académique</label>
                    <input type="text" name="annee" placeholder="Ex: 2024-2025" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">Enregistrer la promotion</button>
            </form>
        </div>

        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; margin-top: 40px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b;">Nom</th>
                        <th style="padding: 20px; color: #64748b;">Année</th>
                        <th style="padding: 20px; color: #64748b; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($promos as $p): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($p['nom']) ?></td>
                        <td style="padding: 20px; color: #64748b;"><?= htmlspecialchars($p['annee']) ?></td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_promotion.php?id=<?= $p['id'] ?>" style="color: #1a4a8e; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="../actions/delete_action.php?type=promotion&id=<?= $p['id'] ?>" onclick="return confirm('Supprimer cette promotion ?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>