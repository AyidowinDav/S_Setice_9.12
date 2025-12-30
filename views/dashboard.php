<?php 
require_once('../config/db.php');
// Récupération des statistiques réelles
$countEtu = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$countProf = $pdo->query("SELECT COUNT(*) FROM formateurs")->fetchColumn();
$countPromo = $pdo->query("SELECT COUNT(*) FROM promotions")->fetchColumn();
$countEspaces = $pdo->query("SELECT COUNT(*) FROM espaces_pedagogiques")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Statistiques</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom:40px;">Statistiques Globales</h1>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background:white; padding:20px; border-radius:15px; border:1px solid #eee;">
                <p style="color:#64748b; font-size:13px; margin:0;">Étudiants</p>
                <h2 style="margin:5px 0 0 0; color:#1a4a8e;"><?= $countEtu ?></h2>
            </div>
            <div style="background:white; padding:20px; border-radius:15px; border:1px solid #eee;">
                <p style="color:#64748b; font-size:13px; margin:0;">Formateurs</p>
                <h2 style="margin:5px 0 0 0; color:#1a4a8e;"><?= $countProf ?></h2>
            </div>
            <div style="background:white; padding:20px; border-radius:15px; border:1px solid #eee;">
                <p style="color:#64748b; font-size:13px; margin:0;">Promotions</p>
                <h2 style="margin:5px 0 0 0; color:#1a4a8e;"><?= $countPromo ?></h2>
            </div>
            <div style="background:white; padding:20px; border-radius:15px; border:1px solid #eee;">
                <p style="color:#64748b; font-size:13px; margin:0;">Espaces</p>
                <h2 style="margin:5px 0 0 0; color:#1a4a8e;"><?= $countEspaces ?></h2>
            </div>
        </div>

        <div style="background:white; padding:30px; border-radius:20px; border:1px solid #eee; margin-bottom:40px;">
            <h3 style="margin-top:0; color:#1e293b;">Évolution des enregistrements</h3>
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
                    backgroundColor: '#1a4a8e',
                    borderRadius: 8
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>