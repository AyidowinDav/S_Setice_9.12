<?php 
session_start();
require_once('../config/db.php');

// Sécurité : Vérifier si c'est bien un étudiant
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

// Statistiques
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM assignations_travaux 
    WHERE id_etudiant = ?
");
$stmt->execute([$id_etudiant]);
$total_travaux = $stmt->fetchColumn();

$stmt2 = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM assignations_travaux 
    WHERE id_etudiant = ? AND statut = 'EN_ATTENTE'
");
$stmt2->execute([$id_etudiant]);
$en_attente = $stmt2->fetchColumn();

$stmt3 = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM assignations_travaux
    WHERE id_etudiant = ? AND statut IN ('RENDU', 'EN_RETARD', 'EVALUE')
");
$stmt3->execute([$id_etudiant]);
$rendus = $stmt3->fetchColumn();

$stmt4 = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM assignations_travaux 
    WHERE id_etudiant = ? AND statut = 'EVALUE'
");
$stmt4->execute([$id_etudiant]);
$evalues = $stmt4->fetchColumn();

// Moyenne générale
$stmtMoyenne = $pdo->prepare("
    SELECT AVG(ev.note) as moyenne
    FROM evaluations ev
    JOIN rendus_travaux rt ON ev.id_rendu = rt.id
    JOIN assignations_travaux at ON rt.id_assignation = at.id
    WHERE at.id_etudiant = ?
");
$stmtMoyenne->execute([$id_etudiant]);
$moyenne = $stmtMoyenne->fetchColumn();
$moyenne = $moyenne ? round($moyenne, 2) : null;

// Données pour le graphique des notes
$stmtNotes = $pdo->prepare("
    SELECT ev.note, ev.date_evaluation
    FROM evaluations ev
    JOIN rendus_travaux rt ON ev.id_rendu = rt.id
    JOIN assignations_travaux at ON rt.id_assignation = at.id
    WHERE at.id_etudiant = ?
    ORDER BY ev.date_evaluation ASC
");
$stmtNotes->execute([$id_etudiant]);
$notes_data = $stmtNotes->fetchAll();

// Travaux urgents (à rendre dans moins de 3 jours)
$stmtUrgent = $pdo->prepare("
    SELECT t.titre, t.date_limite, e.nom_espace, at.id
    FROM assignations_travaux at
    JOIN travaux t ON at.id_travail = t.id
    JOIN espaces_pedagogiques e ON t.id_espace = e.id
    WHERE at.id_etudiant = ? 
      AND at.statut = 'EN_ATTENTE'
      AND t.date_limite BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
    ORDER BY t.date_limite ASC
    LIMIT 5
");
$stmtUrgent->execute([$id_etudiant]);
$travaux_urgents = $stmtUrgent->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Espace Étudiant</title>
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
            <p style="color: #64748b; font-size: 16px;">Voici un aperçu de vos travaux et notes</p>
        </header>

        <!-- Statistiques -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tasks" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Total travaux</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $total_travaux ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">En attente</p>
                        <h2 style="margin:5px 0 0 0; color:#ef4444; font-size: 32px; font-weight: 800;"><?= $en_attente ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Rendus</p>
                        <h2 style="margin:5px 0 0 0; color:#1a4a8e; font-size: 32px; font-weight: 800;"><?= $rendus ?></h2>
                    </div>
                </div>
            </div>
            
            <div style="background:white; padding:30px; border-radius:15px; border:1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-star" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="color:#64748b; font-size:13px; margin:0;">Moyenne</p>
                        <h2 style="margin:5px 0 0 0; color:#10b981; font-size: 32px; font-weight: 800;">
                            <?= $moyenne ? $moyenne . '/20' : 'N/A' ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Travaux urgents -->
        <?php if (!empty($travaux_urgents)): ?>
        <div style="background: #fef3c7; padding: 25px; border-radius: 15px; margin-bottom: 40px; border-left: 4px solid #f59e0b;">
            <h3 style="margin: 0 0 15px 0; color: #92400e; font-size: 18px;">
                <i class="fas fa-exclamation-triangle"></i> Travaux urgents (< 3 jours)
            </h3>
            <?php foreach ($travaux_urgents as $tu): ?>
                <?php 
                $date_limite = new DateTime($tu['date_limite']);
                $maintenant = new DateTime();
                $diff = $maintenant->diff($date_limite);
                $jours_restants = $diff->days;
                ?>
                <div style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="font-weight: 700; color: #1e293b; margin: 0 0 5px 0;"><?= htmlspecialchars($tu['titre']) ?></p>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">
                                <i class="fas fa-book"></i> <?= htmlspecialchars($tu['nom_espace']) ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <p style="color: #dc2626; font-weight: 700; margin: 0 0 5px 0;">
                                <i class="fas fa-fire"></i> <?= $jours_restants ?> jour<?= $jours_restants > 1 ? 's' : '' ?>
                            </p>
                            <a href="rendre_travail.php?id=<?= $tu['id'] ?>" style="background: #dc2626; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">
                                Rendre maintenant
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Graphique de progression des notes -->
        <?php if (!empty($notes_data)): ?>
        <div style="background: white; padding: 30px; border-radius: 20px; margin-bottom: 40px; border: 1px solid #edf2f7;">
            <h3 style="margin-top: 0; color: #1e293b;">Évolution de vos notes</h3>
            <canvas id="notesChart" style="max-height: 300px;"></canvas>
        </div>
        <?php endif; ?>

        <!-- Conseils -->
        <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #eee;">
            <h3 style="margin-top: 0; color: #1e293b; font-size: 20px;">
                <i class="fas fa-lightbulb" style="color: #f59e0b;"></i> Conseils pour réussir
            </h3>
            <ul style="color: #475569; line-height: 2;">
                <li>Rendez vos travaux <strong>avant la date limite</strong> pour éviter les pénalités</li>
                <li>Soignez la <strong>qualité de votre rédaction</strong> (minimum 50 caractères)</li>
                <li>Consultez régulièrement vos notes pour <strong>suivre votre progression</strong></li>
                <li>En cas de difficulté, <strong>contactez votre formateur</strong> rapidement</li>
            </ul>
        </div>
    </main>

    <?php if (!empty($notes_data)): ?>
    <script>
        const ctx = document.getElementById('notesChart');
        const notesData = <?php echo json_encode($notes_data); ?>;

        // Préparer les données pour le graphique
        const labels = notesData.map(item => {
            const date = new Date(item.date_evaluation);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        });

        const notes = notesData.map(item => parseFloat(item.note));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Notes obtenues',
                    data: notes,
                    borderColor: '#1a4a8e',
                    backgroundColor: 'rgba(26, 74, 142, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1a4a8e',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Note: ' + context.parsed.y + '/20';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20,
                        ticks: {
                            stepSize: 2,
                            callback: function(value) {
                                return value + '/20';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBorderWidth: 3
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
