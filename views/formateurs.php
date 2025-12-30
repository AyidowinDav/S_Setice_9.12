<?php
session_start();
require_once('../config/db.php');
// Récupération de tous les formateurs
$formateurs = $pdo->query("SELECT * FROM formateurs ORDER BY nom ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Formateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Gestion des Formateurs</h1>
            <p style="color: #64748b;">Gérez les accès des enseignants.</p>
        </header>

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7;">
            <form action="../actions/formateur_action.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom & Prenom</label>
                    <input type="text" name="nom" placeholder="Ex: Eliel DOSSOU" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px; outline:none;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Adresse Email</label>
                    <input type="email" name="email" placeholder="elieldossou11@gmail.com" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px 30px; border-radius:12px; cursor:pointer; font-weight:600; width:100%; font-size:16px;">
                    Créer le compte formateur
                </button>
            </form>
        </div>

        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; margin-top: 40px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b;">Nom</th>
                        <th style="padding: 20px; color: #64748b;">Email</th>
                        <th style="padding: 20px; color: #64748b; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($formateurs as $f): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($f['nom']) ?></td>
                        <td style="padding: 20px; color: #64748b;"><?= htmlspecialchars($f['email']) ?></td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_formateur.php?id=<?= $f['id'] ?>" style="color: #1a4a8e; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="../actions/delete_action.php?type=formateur&id=<?= $f['id'] ?>" onclick="return confirm('Supprimer ce formateur ?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>