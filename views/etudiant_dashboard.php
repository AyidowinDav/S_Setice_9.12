<?php 
session_start();
require_once('../config/db.php');

if ($_SESSION['role'] !== 'ETUDIANT') { header('Location: ../index.php'); exit; }

$id_user = $_SESSION['user_id'];

// On récupère les devoirs assignés à cet étudiant (via sa promo ou en individuel)
$stmt = $pdo->prepare("
    SELECT t.*, f.nom as formateur_nom 
    FROM travaux t
    JOIN utilisateur f ON t.id_formateur = f.id
    WHERE t.id_promotion = (SELECT id_promotion FROM etudiants WHERE id_utilisateur = ?)
    OR t.id_etudiant = (SELECT id FROM etudiants WHERE id_utilisateur = ?)
    ORDER BY t.date_limite ASC
");
$stmt->execute([$id_user, $id_user]);
$mes_travaux = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Mon Espace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f1f5f9;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800;">Bonjour, <?= $_SESSION['nom'] ?> ! 👋</h1>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top:30px;">
            <div style="background:white; border-radius:20px; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-top:0;"><i class="fas fa-clock" style="color:#1a4a8e;"></i> Travaux à rendre</h3>
                <?php foreach($mes_travaux as $mt): ?>
                <div style="border:1px solid #eee; padding:15px; border-radius:12px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h4 style="margin:0;"><?= htmlspecialchars($mt['titre']) ?></h4>
                        <p style="margin:5px 0; font-size:12px; color:#64748b;">Par : M. <?= $mt['formateur_nom'] ?></p>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:12px; display:block; color:red;">Limite : <?= $mt['date_limite'] ?></span>
                        <a href="rendre_travail.php?id=<?= $mt['id'] ?>" style="color:#1a4a8e; font-weight:600; text-decoration:none; font-size:14px;">Rendre le travail →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="background:white; border-radius:20px; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-top:0;"><i class="fas fa-star" style="color:#f1c40f;"></i> Dernières Notes</h3>
                <p style="font-size:14px; color:#64748b;">Consultez vos évaluations et les commentaires de vos formateurs.</p>
                <a href="mes_notes.php" class="btn" style="display:block; text-align:center; margin-top:20px;">Voir tout mon carnet</a>
            </div>
        </div>
    </main>
</body>
</html>