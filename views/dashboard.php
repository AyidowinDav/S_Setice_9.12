<?php
session_start();

// Protection
if (!isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit;
}

// Redirection vers le bon dashboard
$role = $_SESSION['role'];
if ($role !== 'DIRECTEUR' && $role !== 'ADMIN') {
    if ($role === 'ETUDIANT') {
        header('Location: etudiant_dashboard.php');
    } elseif ($role === 'FORMATEUR') {
        header('Location: formateur_dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

require_once('../config/db.php');

// Récupération des statistiques de base
$countEtu = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$countProf = $pdo->query("SELECT COUNT(*) FROM formateurs")->fetchColumn();
$countPromo = $pdo->query("SELECT COUNT(*) FROM promotions")->fetchColumn();
$countEspaces = $pdo->query("SELECT COUNT(*) FROM espaces_pedagogiques")->fetchColumn();

// Statistiques d'activité globale
$countTravaux = $pdo->query("SELECT COUNT(*) FROM travaux")->fetchColumn();
$countRendus = $pdo->query("SELECT COUNT(*) FROM rendus_travaux")->fetchColumn();
$countEvaluations = $pdo->query("SELECT COUNT(*) FROM evaluations")->fetchColumn();

// Statistiques par espace pédagogique
$stmtEspacesStats = $pdo->prepare("
    SELECT
        ep.nom_espace,
        COUNT(DISTINCT t.id) as nb_travaux,
        COUNT(DISTINCT at.id) as nb_assignations,
        COUNT(DISTINCT rt.id) as nb_rendus,
        COUNT(DISTINCT ev.id) as nb_evaluations,
        ROUND(
            (COUNT(DISTINCT rt.id) * 100.0 / NULLIF(COUNT(DISTINCT at.id), 0)), 1
        ) as taux_rendu
    FROM espaces_pedagogiques ep
    LEFT JOIN travaux t ON ep.id = t.id_espace
    LEFT JOIN assignations_travaux at ON t.id = at.id_travail
    LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
    LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
    GROUP BY ep.id, ep.nom_espace
    ORDER BY nb_travaux DESC
    LIMIT 10
");
$stmtEspacesStats->execute();
$espaces_stats = $stmtEspacesStats->fetchAll();

// Statistiques d'activité des formateurs
$stmtFormateursActivity = $pdo->prepare("
    SELECT
        f.id,
        f.nom,
        COUNT(DISTINCT t.id) as nb_travaux_crees,
        COUNT(DISTINCT rt.id) as nb_rendus_recus,
        COUNT(DISTINCT ev.id) as nb_evaluations_faites,
        COUNT(DISTINCT ep.id) as nb_espaces
    FROM formateurs f
    LEFT JOIN travaux t ON f.id = t.id_formateur
    LEFT JOIN assignations_travaux at ON t.id = at.id_travail
    LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
    LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
    LEFT JOIN espaces_pedagogiques ep ON t.id_espace = ep.id
    GROUP BY f.id, f.nom
    ORDER BY nb_travaux_crees DESC
    LIMIT 10
");
$stmtFormateursActivity->execute();
$formateurs_activity = $stmtFormateursActivity->fetchAll();

// Statistiques de réactivité des étudiants (travaux rendus dans les délais)
$stmtStudentReactivity = $pdo->prepare("
    SELECT
        ep.nom_espace,
        COUNT(CASE WHEN rt.est_en_retard = 0 THEN 1 END) as rendus_a_temps,
        COUNT(CASE WHEN rt.est_en_retard = 1 THEN 1 END) as rendus_en_retard,
        COUNT(rt.id) as total_rendus,
        ROUND(
            AVG(CASE
                WHEN rt.date_rendu <= t.date_limite THEN 100
                WHEN rt.date_rendu > t.date_limite THEN
                    GREATEST(0, 100 - DATEDIFF(rt.date_rendu, t.date_limite))
                ELSE 0
            END), 1
        ) as score_reactvite_moyen
    FROM espaces_pedagogiques ep
    JOIN travaux t ON ep.id = t.id_espace
    JOIN assignations_travaux at ON t.id = at.id_travail
    JOIN rendus_travaux rt ON at.id = rt.id_assignation
    GROUP BY ep.id, ep.nom_espace
    HAVING total_rendus > 0
    ORDER BY score_reactvite_moyen DESC
    LIMIT 10
");
$stmtStudentReactivity->execute();
$student_reactivity = $stmtStudentReactivity->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Dashboard Directeur</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-weight: 800; color: #0f172a; font-size: 32px;">
                Bonjour, <?= htmlspecialchars($_SESSION['nom']) ?> ! 👋
            </h1>
            <p style="color: #64748b; font-size: 16px;">Bienvenue sur votre tableau de bord</p>
        </header>

        <!-- Statistiques -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-graduate" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Étudiants</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $countEtu ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chalkboard-teacher" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Formateurs</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $countProf ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-graduation-cap" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Promotions</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $countPromo ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Espaces</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $countEspaces ?></h2>
                    </div>
                </div>
            </div>
        </div>



        <!-- Statistiques par espace pédagogique -->
        <div style="background:white; padding:30px; border-radius:20px; border:1px solid #eee; margin-bottom:40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#1e293b; font-size: 20px; font-weight: 700;">Activité par Espace Pédagogique</h3>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; margin-top: 20px;">
                    <thead>
                        <tr style="text-align:left; border-bottom:2px solid #eee;">
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Espace</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Travaux</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Assignations</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Rendus</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Évaluations</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Taux rendu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($espaces_stats as $espace): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:15px; font-weight: 500;"><?= htmlspecialchars($espace['nom_espace']) ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $espace['nb_travaux'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $espace['nb_assignations'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $espace['nb_rendus'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $espace['nb_evaluations'] ?></td>
                            <td style="padding:15px;">
                                <span style="background: #e0e7ff; color: #1a4a8e; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                                    <?= $espace['taux_rendu'] ?? 0 ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activité des formateurs -->
        <div style="background:white; padding:30px; border-radius:20px; border:1px solid #eee; margin-bottom:40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#1e293b; font-size: 20px; font-weight: 700;">Activité des Formateurs</h3>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; margin-top: 20px;">
                    <thead>
                        <tr style="text-align:left; border-bottom:2px solid #eee;">
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Formateur</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Travaux créés</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Rendus reçus</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Évaluations</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Espaces</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($formateurs_activity as $formateur): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:15px; font-weight: 500;"><?= htmlspecialchars($formateur['nom']) ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $formateur['nb_travaux_crees'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $formateur['nb_rendus_recus'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $formateur['nb_evaluations_faites'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $formateur['nb_espaces'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Réactivité des étudiants -->
        <div style="background:white; padding:30px; border-radius:20px; border:1px solid #eee; margin-bottom:40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#1e293b; font-size: 20px; font-weight: 700;">Réactivité des Étudiants par Espace</h3>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; margin-top: 20px;">
                    <thead>
                        <tr style="text-align:left; border-bottom:2px solid #eee;">
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Espace</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Rendus à temps</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Rendus en retard</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Total rendus</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Score réactivité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($student_reactivity as $reactivity): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:15px; font-weight: 500;"><?= htmlspecialchars($reactivity['nom_espace']) ?></td>
                            <td style="padding:15px; color: #10b981; font-weight: 600;"><?= $reactivity['rendus_a_temps'] ?></td>
                            <td style="padding:15px; color: #ef4444; font-weight: 600;"><?= $reactivity['rendus_en_retard'] ?></td>
                            <td style="padding:15px; color: #64748b;"><?= $reactivity['total_rendus'] ?></td>
                            <td style="padding:15px;">
                                <span style="background: <?= $reactivity['score_reactvite_moyen'] >= 80 ? '#dcfce7' : ($reactivity['score_reactvite_moyen'] >= 60 ? '#fef3c7' : '#fee2e2') ?>; color: <?= $reactivity['score_reactvite_moyen'] >= 80 ? '#166534' : ($reactivity['score_reactvite_moyen'] >= 60 ? '#92400e' : '#dc2626') ?>; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                                    <?= $reactivity['score_reactvite_moyen'] ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Graphique -->
        <div style="background:white; padding:30px; border-radius:20px; border:1px solid #eee; margin-bottom:40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#1e293b; font-size: 20px; font-weight: 700;">Évolution des enregistrements</h3>
            <canvas id="myChart" style="max-height: 300px;"></canvas>
        </div>


    </main>

    <script>
        const ctx = document.getElementById('myChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Étudiants', 'Formateurs', 'Promotions', 'Espaces'],
                datasets: [{
                    label: 'Volume Total',
                    data: [<?= $countEtu ?>, <?= $countProf ?>, <?= $countPromo ?>, <?= $countEspaces ?>],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(67, 233, 123, 0.8)'
                    ],
                    borderRadius: 8
                }]
            },
            options: { 
                scales: { 
                    y: { beginAtZero: true } 
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>