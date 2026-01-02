<?php 
session_start();
require_once('../config/db.php');

// Sécurité : Vérifier si c'est bien un formateur
if ($_SESSION['role'] !== 'FORMATEUR') { header('Location: ../index.php'); exit; }

$id_formateur = $_SESSION['user_id'];

// Récupération des travaux déjà postés par ce formateur
$stmt = $pdo->prepare("SELECT * FROM travaux WHERE id_formateur = ? ORDER BY date_limite ASC");
$stmt->execute([$id_formateur]);
$travaux = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Espace Formateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a;">Espace Pédagogique</h1>
        <p style="color: #64748b;">Gérez vos cours, devoirs et évaluations.</p>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px;">
            <a href="nouveau_travail.php" style="text-decoration:none; background:#1a4a8e; color:white; padding:30px; border-radius:20px; text-align:center;">
                <i class="fas fa-file-upload fa-2x"></i>
                <h3 style="margin:15px 0 5px 0;">Définir un travail</h3>
                <p style="font-size:14px; opacity:0.8;">Assigner un devoir individuel ou collectif</p>
            </a>
            <a href="notation.php" style="text-decoration:none; background:white; color:#1a4a8e; padding:30px; border-radius:20px; text-align:center; border:1px solid #1a4a8e;">
                <i class="fas fa-marker fa-2x"></i>
                <h3 style="margin:15px 0 5px 0;">Noter les rendus</h3>
                <p style="font-size:14px; color:#64748b;">Évaluer et commenter les copies</p>
            </a>
        </div>

        <div style="margin-top: 40px; background:white; border-radius:20px; padding:30px; border:1px solid #eee;">
            <h3 style="margin-top:0;">Vos travaux demandés</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #eee;">
                        <th style="padding:15px;">Titre</th>
                        <th style="padding:15px;">Cible</th>
                        <th style="padding:15px;">Deadline</th>
                        <th style="padding:15px;">Rendus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($travaux as $t): ?>
                    <tr style="border-bottom:1px solid #f9f9f9;">
                        <td style="padding:15px;"><?= htmlspecialchars($t['titre']) ?></td>
                        <td style="padding:15px;"><?= $t['type'] === 'COLLECTIF' ? 'Promotion' : 'Individuel' ?></td>
                        <td style="padding:15px; color:red;"><?= $t['date_limite'] ?></td>
                        <td style="padding:15px;"><a href="view_copies.php?id=<?= $t['id'] ?>">Voir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>