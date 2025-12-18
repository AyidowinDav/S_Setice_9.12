<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DIRECTEUR') {
    header('Location: ../index.php'); exit();
}

// Récupérer les promotions pour le menu déroulant
$promos = $pdo->query("SELECT * FROM promotion ORDER BY annee DESC")->fetchAll();

// Récupérer la liste des étudiants déjà inscrits
$query = "SELECT u.*, p.nom_promotion 
          FROM utilisateur u 
          LEFT JOIN promotion_etudiant pe ON u.id = pe.id_utilisateur
          LEFT JOIN promotion p ON pe.id_promotion = p.id
          WHERE u.role = 'ETUDIANT' ORDER BY u.nom ASC";
$etudiants = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Inscrire un nouvel Étudiant</h1>
    
    <form action="../actions/admin_actions.php" method="POST">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe par défaut" required>
        
        <label>Assigner à une Promotion :</label>
        <select name="id_promotion" required>
            <option value="">-- Choisir une promo --</option>
            <?php foreach($promos as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom_promotion']) ?> (<?= $p['annee'] ?>)</option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" name="create_student">Créer l'étudiant</button>
    </form>

    <h2>Liste des Étudiants</h2>
    <table border="1">
        <tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Promotion</th></tr>
        <?php foreach($etudiants as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['nom']) ?></td>
            <td><?= htmlspecialchars($e['prenom']) ?></td>
            <td><?= htmlspecialchars($e['email']) ?></td>
            <td><?= htmlspecialchars($e['nom_promotion'] ?? 'Aucune') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>