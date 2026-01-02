<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

$sql = "SELECT 
            e.id, 
            e.nom_espace, 
            f.nom AS nom_formateur,
            (SELECT COUNT(*) FROM etudiant_espace ee WHERE ee.id_espace = e.id) AS nb_etudiants
        FROM espaces_pedagogiques e 
        LEFT JOIN formateurs f ON e.id_formateur = f.id 
        ORDER BY e.nom_espace ASC";

$espaces = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Consultation Espaces</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">

    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Espaces Pédagogiques</h1>
                <p style="color: #64748b;">Vue d'ensemble des effectifs par matière.</p>
            </div>
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

        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b; font-weight: 600;">Matière</th>
                        <th style="padding: 20px; color: #64748b; font-weight: 600;">Formateur</th>
                        <th style="padding: 20px; color: #64748b; font-weight: 600;">Effectif</th>
                        <th style="padding: 20px; color: #64748b; font-weight: 600; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($espaces as $espace): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#fcfcfd'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 20px; font-weight: 600; color: #1e293b;"><?= htmlspecialchars($espace['nom_espace']) ?></td>
                        <td style="padding: 20px; color: #475569;">
                            <span style="background: #f1f5f9; padding: 5px 12px; border-radius: 20px; font-size: 13px;">
                                <i class="fas fa-user-tie" style="margin-right: 5px; color: #1a4a8e;"></i>
                                <?= htmlspecialchars($espace['nom_formateur'] ?? 'Non assigné') ?>
                            </span>
                        </td>
                        <td style="padding: 20px;">
                            <div style="display: flex; align-items: center;">
                                <span style="font-weight: 800; color: #1a4a8e; font-size: 16px;"><?= $espace['nb_etudiants'] ?></span>
                                <span style="margin-left: 8px; color: #94a3b8; font-size: 13px;">étudiant(s)</span>
                            </div>
                        </td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_espace.php?id=<?= $espace['id'] ?>" style="color: #64748b; margin-right: 15px;"><i class="fas fa-cog"></i></a>
                            <a href="../actions/delete_action.php?type=espace&id=<?= $espace['id'] ?>&csrf_token=<?= generateCsrfToken() ?>" onclick="return confirm('Supprimer cet espace ?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>