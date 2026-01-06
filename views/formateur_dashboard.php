 <?php 
session_start();
require_once('../config/db.php');

// Sécurité : Vérifier si c'est bien un formateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FORMATEUR') { 
    header('Location: ../index.php'); 
    exit; 
}

$id_formateur = $_SESSION['user_id'];

// Récupérer l'ID réel du formateur depuis la table formateurs
$getFormateurId = $pdo->prepare("SELECT id FROM formateurs WHERE id_utilisateur = ?");
$getFormateurId->execute([$id_formateur]);
$formateur = $getFormateurId->fetch();

if (!$formateur) {
    // Si le formateur n'existe pas, afficher des statistiques vides
    $stats = ['nb_travaux' => 0];
    $stats_rendus = ['nb_rendus' => 0];
    $stats_eval = ['nb_evalues' => 0];
    $travaux = [];
} else {
    $id_formateur_real = $formateur['id'];

    // Récupération des statistiques
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT t.id) as nb_travaux
        FROM travaux t
        WHERE t.id_formateur = ?
    ");
    $stmt->execute([$id_formateur_real]);
    $stats = $stmt->fetch();

    $stmt2 = $pdo->prepare("
        SELECT COUNT(*) as nb_rendus
        FROM rendus_travaux rt
        JOIN assignations_travaux at ON rt.id_assignation = at.id
        JOIN travaux t ON at.id_travail = t.id
        WHERE t.id_formateur = ?
    ");
    $stmt2->execute([$id_formateur_real]);
    $stats_rendus = $stmt2->fetch();

    $stmt3 = $pdo->prepare("
        SELECT COUNT(*) as nb_evalues
        FROM evaluations ev
        JOIN rendus_travaux rt ON ev.id_rendu = rt.id
        JOIN assignations_travaux at ON rt.id_assignation = at.id
        JOIN travaux t ON at.id_travail = t.id
        WHERE t.id_formateur = ?
    ");
    $stmt3->execute([$id_formateur_real]);
    $stats_eval = $stmt3->fetch();

    // Statistiques de taux de rendement
    $stmt4 = $pdo->prepare("
        SELECT
            COUNT(CASE WHEN rt.est_en_retard = 0 THEN 1 END) as rendus_a_temps,
            COUNT(CASE WHEN rt.est_en_retard = 1 THEN 1 END) as rendus_en_retard,
            COUNT(rt.id) as total_rendus,
            COUNT(DISTINCT at.id) as total_assignations
        FROM assignations_travaux at
        JOIN travaux t ON at.id_travail = t.id
        LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
        WHERE t.id_formateur = ?
    ");
    $stmt4->execute([$id_formateur_real]);
    $stats_taux = $stmt4->fetch();

    // Calcul des taux
    $total_assignations = $stats_taux['total_assignations'] ?: 1; // éviter division par zéro
    $total_rendus = $stats_taux['total_rendus'];

    $taux_rendu_a_temps = $total_rendus > 0 ? round(($stats_taux['rendus_a_temps'] / $total_rendus) * 100, 1) : 0;
    $taux_rendu_en_retard = $total_rendus > 0 ? round(($stats_taux['rendus_en_retard'] / $total_rendus) * 100, 1) : 0;
    $taux_etudiants_rendus = round(($total_rendus / $total_assignations) * 100, 1);
    $taux_etudiants_non_rendus = round((($total_assignations - $total_rendus) / $total_assignations) * 100, 1);

    // Récupération des travaux récents
    $stmtTravaux = $pdo->prepare("
        SELECT t.*, e.nom_espace,
               COUNT(DISTINCT at.id) as nb_assignations,
               COUNT(DISTINCT rt.id) as nb_rendus
        FROM travaux t
        JOIN espaces_pedagogiques e ON t.id_espace = e.id
        LEFT JOIN assignations_travaux at ON t.id = at.id_travail
        LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
        WHERE t.id_formateur = ?
        GROUP BY t.id
        ORDER BY t.date_creation DESC
        LIMIT 5
    ");
    $stmtTravaux->execute([$id_formateur_real]);
    $travaux = $stmtTravaux->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Espace Formateur</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <h1 style="font-weight: 800; color: #0f172a; font-size: 32px;">
                Bonjour, <?= htmlspecialchars($_SESSION['nom']) ?> ! 👋
            </h1>
            <p style="color: #64748b; font-size: 16px;">Gérez vos cours, travaux et évaluations</p>
        </header>

        <!-- Statistiques -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tasks" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Travaux créés</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $stats['nb_travaux'] ?></h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-alt" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Rendus reçus</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $stats_rendus['nb_rendus'] ?></h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Évaluations</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $stats_eval['nb_evalues'] ?></h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Taux rendu à temps</p>
                        <h2 style="margin:5px 0 0 0; color:#10b981; font-size: 32px; font-weight: 800;"><?= $taux_rendu_a_temps ?>%</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques de taux de rendement -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-exclamation-triangle" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Taux rendu en retard</p>
                        <h2 style="margin:5px 0 0 0; color:#ef4444; font-size: 32px; font-weight: 800;"><?= $taux_rendu_en_retard ?>%</h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Taux étudiants rendus</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $taux_etudiants_rendus ?>%</h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-times" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Taux étudiants non rendus</p>
                        <h2 style="margin:5px 0 0 0; color:#dc2626; font-size: 32px; font-weight: 800;"><?= $taux_etudiants_non_rendus ?>%</h2>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Total assignations</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $total_assignations ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- Travaux récents -->
        <div style="margin-top: 40px; background:white; border-radius:20px; padding:30px; border:1px solid #eee;">
            <h3 style="margin-top:0; color: #1e293b; font-size: 20px;">Vos travaux récents</h3>
            
            <?php if (empty($travaux)): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <p style="color: #64748b;">Aucun travail créé. Commencez par créer votre premier travail !</p>
                    <a href="nouveau_travail.php" style="display: inline-block; margin-top: 15px; background: #1a4a8e; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                        <i class="fas fa-plus"></i> Créer un travail
                    </a>
                </div>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left; border-bottom:2px solid #eee;">
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Titre</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Espace</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Date limite</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Rendus</th>
                            <th style="padding:15px; color: #64748b; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($travaux as $t): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:15px; font-weight: 500;"><?= htmlspecialchars($t['titre']) ?></td>
                            <td style="padding:15px; color: #64748b;"><?= htmlspecialchars($t['nom_espace']) ?></td>
                            <td style="padding:15px; color: #ef4444; font-weight: 600;"><?= date('d/m/Y', strtotime($t['date_limite'])) ?></td>
                            <td style="padding:15px;">
                                <span style="background: #e0e7ff; color: #1a4a8e; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                                    <?= $t['nb_rendus'] ?> / <?= $t['nb_assignations'] ?>
                                </span>
                            </td>
                            <td style="padding:15px;">
                                <a href="mes_travaux_formateur.php" style="color: #1a4a8e; text-decoration: none; font-weight: 600;">
                                    Voir <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="mes_travaux_formateur.php" style="color: #1a4a8e; text-decoration: none; font-weight: 600;">
                        Voir tous mes travaux <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>