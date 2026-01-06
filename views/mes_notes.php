<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ETUDIANT') {
    header('Location: ../index.php');
    exit;
}

$id_user = $_SESSION['user_id'];

// Récupérer l'ID étudiant
$stmtEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id_utilisateur = ?");
$stmtEtu->execute([$id_user]);
$etudiant = $stmtEtu->fetch();

if (!$etudiant) {
    die("Erreur : Profil étudiant introuvable.");
}

$id_etudiant = $etudiant['id'];

// Récupérer toutes les notes
$stmt = $pdo->prepare("
    SELECT
        ev.note,
        ev.commentaire,
        ev.date_evaluation,
        t.titre as travail_titre,
        e.nom_espace,
        f.nom as formateur_nom,
        rt.date_rendu,
        rt.est_en_retard
    FROM evaluations ev
    JOIN rendus_travaux rt ON ev.id_rendu = rt.id
    JOIN assignations_travaux at ON rt.id_assignation = at.id
    JOIN travaux t ON at.id_travail = t.id
    JOIN espaces_pedagogiques e ON t.id_espace = e.id
    JOIN formateurs f ON t.id_formateur = f.id
    WHERE at.id_etudiant = ?
    ORDER BY ev.date_evaluation DESC
");
$stmt->execute([$id_etudiant]);
$notes = $stmt->fetchAll();

// Calculer les statistiques
$nb_notes = count($notes);
$moyenne = 0;
$note_min = null;
$note_max = null;

if ($nb_notes > 0) {
    $somme = array_sum(array_column($notes, 'note'));
    $moyenne = round($somme / $nb_notes, 2);
    $note_min = min(array_column($notes, 'note'));
    $note_max = max(array_column($notes, 'note'));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Mes Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Mes Notes</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Consultez toutes vos évaluations et votre progression</p>

        <!-- Statistiques -->
        <?php if ($nb_notes > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #edf2f7;">
                <p style="margin: 0 0 10px 0; color: #64748b; font-size: 13px;">Nombre de notes</p>
                <p style="margin: 0; color: #1a4a8e; font-size: 36px; font-weight: 800;"><?= $nb_notes ?></p>
            </div>
            <div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #edf2f7;">
                <p style="margin: 0 0 10px 0; color: #64748b; font-size: 13px;">Note minimale</p>
                <p style="margin: 0; color: #ef4444; font-size: 36px; font-weight: 800;"><?= $note_min ?>/20</p>
            </div>
            <div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #edf2f7;">
                <p style="margin: 0 0 10px 0; color: #64748b; font-size: 13px;">Note maximale</p>
                <p style="margin: 0; color: #8b5cf6; font-size: 36px; font-weight: 800;"><?= $note_max ?>/20</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des notes -->
        <?php if (empty($notes)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px; border: 1px solid #edf2f7;">
                <i class="fas fa-star" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3 style="color: #1e293b; margin-bottom: 10px;">Aucune note pour le moment</h3>
                <p style="color: #64748b;">Vos évaluations apparaîtront ici une fois que vos formateurs les auront saisies.</p>
            </div>
        <?php else: ?>
            <div style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #edf2f7;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #edf2f7;">
                            <th style="padding: 20px; text-align: left; color: #64748b; font-weight: 600;">Travail</th>
                            <th style="padding: 20px; text-align: left; color: #64748b; font-weight: 600;">Matière</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Note</th>
                            <th style="padding: 20px; text-align: left; color: #64748b; font-weight: 600;">Commentaire</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notes as $n): 
                            $couleur_note = $n['note'] >= 10 ? '#10b981' : '#ef4444';
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 20px;">
                                <p style="margin: 0; font-weight: 600; color: #1e293b;"><?= htmlspecialchars($n['travail_titre']) ?></p>
                                <?php if ($n['est_en_retard']): ?>
                                <span style="font-size: 12px; color: #f59e0b;">
                                    <i class="fas fa-exclamation-triangle"></i> Rendu en retard
                                </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 20px; color: #64748b;">
                                <i class="fas fa-book"></i> <?= htmlspecialchars($n['nom_espace']) ?>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <span style="background: <?= $couleur_note ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 16px;">
                                    <?= $n['note'] ?>/20
                                </span>
                            </td>
                            <td style="padding: 20px; color: #475569; max-width: 300px;">
                                <?= $n['commentaire'] ? htmlspecialchars($n['commentaire']) : '<em style="color:#94a3b8;">Aucun commentaire</em>' ?>
                            </td>
                            <td style="padding: 20px; text-align: center; color: #64748b; font-size: 14px;">
                                <?= date('d/m/Y', strtotime($n['date_evaluation'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>