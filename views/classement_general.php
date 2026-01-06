<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

// Récupérer le classement général
$stmt = $pdo->query("
    SELECT 
        e.id,
        e.nom,
        e.prenom,
        p.nom as promo_nom,
        AVG(ev.note) as moyenne_generale,
        COUNT(DISTINCT ev.id) as nb_evaluations,
        MIN(ev.note) as note_min,
        MAX(ev.note) as note_max
    FROM etudiants e
    JOIN promotions p ON e.id_promotion = p.id
    LEFT JOIN assignations_travaux at ON e.id = at.id_etudiant
    LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
    LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
    GROUP BY e.id
    HAVING nb_evaluations > 0
    ORDER BY moyenne_generale DESC
");
$classement = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Classement Général</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 40px;">
            <i class="fas fa-trophy" style="font-size: 48px; color: #f59e0b;"></i>
            <div>
                <h1 style="font-weight: 800; color: #0f172a; margin: 0 0 5px 0;">Classement Général</h1>
                <p style="color: #64748b; margin: 0;">Performances de tous les étudiants</p>
            </div>
        </div>

        <?php if (empty($classement)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px; border: 1px solid #edf2f7;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <p style="color: #64748b;">Aucune évaluation enregistrée pour le moment.</p>
            </div>
        <?php else: ?>
            <!-- Podium -->
            <?php if (count($classement) >= 3): ?>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
                <?php for($i = 0; $i < 3 && $i < count($classement); $i++): 
                    $etu = $classement[$i];
                    $medals = ['#fbbf24', '#94a3b8', '#cd7f32'];
                    $positions = ['🥇 1er', '🥈 2ème', '🥉 3ème'];
                ?>
                <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; border: 3px solid <?= $medals[$i] ?>;">
                    <div style="font-size: 48px; margin-bottom: 15px;"><?= ['🥇', '🥈', '🥉'][$i] ?></div>
                    <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 20px;">
                        <?= htmlspecialchars($etu['nom']) ?> <?= htmlspecialchars($etu['prenom']) ?>
                    </h3>
                    <p style="margin: 0 0 15px 0; color: #64748b; font-size: 14px;"><?= htmlspecialchars($etu['promo_nom']) ?></p>
                    <p style="margin: 0; color: <?= $medals[$i] ?>; font-size: 36px; font-weight: 800;">
                        <?= round($etu['moyenne_generale'], 2) ?>/20
                    </p>
                </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <!-- Tableau complet -->
            <div style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #edf2f7;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #edf2f7;">
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600; width: 80px;">Rang</th>
                            <th style="padding: 20px; text-align: left; color: #64748b; font-weight: 600;">Étudiant</th>
                            <th style="padding: 20px; text-align: left; color: #64748b; font-weight: 600;">Promotion</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Moyenne</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Min</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Max</th>
                            <th style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">Nb notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rang = 1;
                        foreach($classement as $etu): 
                            $couleur_rang = $rang <= 3 ? '#fbbf24' : '#64748b';
                            $couleur_moyenne = $etu['moyenne_generale'] >= 10 ? '#10b981' : '#ef4444';
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 20px; text-align: center;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: <?= $couleur_rang ?>20; color: <?= $couleur_rang ?>; border-radius: 50%; font-weight: 800; font-size: 18px;">
                                    <?= $rang ?>
                                </span>
                            </td>
                            <td style="padding: 20px; font-weight: 600; color: #1e293b;">
                                <?= htmlspecialchars($etu['nom']) ?> <?= htmlspecialchars($etu['prenom']) ?>
                            </td>
                            <td style="padding: 20px; color: #64748b;">
                                <?= htmlspecialchars($etu['promo_nom']) ?>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <span style="background: <?= $couleur_moyenne ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 16px;">
                                    <?= round($etu['moyenne_generale'], 2) ?>/20
                                </span>
                            </td>
                            <td style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">
                                <?= $etu['note_min'] ?>
                            </td>
                            <td style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">
                                <?= $etu['note_max'] ?>
                            </td>
                            <td style="padding: 20px; text-align: center; color: #64748b;">
                                <?= $etu['nb_evaluations'] ?>
                            </td>
                        </tr>
                        <?php 
                        $rang++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>