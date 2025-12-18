<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
include '../config/db.php';

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateur");
    $users_count = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM espace_pedagogique");
    $espaces_count = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM promotion");
    $promos_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $users_count = $espaces_count = $promos_count = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <title>Dashboard Admin</title>
</head>
<body>
    <div class="dashboard">
        <header>
            <h1>Dashboard Administrateur</h1>
            <div class="user-info">
                <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../actions/logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </header>
        <main>
            <div class="stats">
                <div class="stat-card">
                    <h3>Utilisateurs</h3>
                    <p><?php echo $users_count; ?></p>
                    <a href="gestion_utilisateurs.php">Gérer</a>
                </div>
                <div class="stat-card">
                    <h3>Espaces</h3>
                    <p><?php echo $espaces_count; ?></p>
                    <a href="gestion_espaces.php">Gérer</a>
                </div>
                <div class="stat-card">
                    <h3>Promotions</h3>
                    <p><?php echo $promos_count; ?></p>
                    <a href="gestion_promos.php">Gérer</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
