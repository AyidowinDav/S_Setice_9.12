<?php
session_start();
require_once('../config/db.php');

$id = $_GET['id'];
// Récupérer les infos de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$id]);
$e = $stmt->fetch();

if(!$e) die("Étudiant introuvable");

// Récupérer les promotions pour le menu déroulant
$promos = $pdo->query("SELECT * FROM promotions ORDER BY nom ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Modifier Étudiant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="font-family:'Inter', sans-serif; background:#f8fafc; padding:50px;">
    <div style="background: white; padding: 40px; border-radius: 20px; max-width: 500px; margin: auto; border: 1px solid #eee; box-shadow: 0 10px 15px rgba(0,0,0,0.05);">
        <h2 style="color:#1a4a8e; margin-bottom: 25px;">Modifier l'Étudiant</h2>
        <form action="../actions/update_action.php" method="POST">
            <input type="hidden" name="type" value="etudiant">
            <input type="hidden" name="id" value="<?= $e['id'] ?>">

            <label style="display:block; margin-bottom:8px; font-weight:600;">Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($e['nom']) ?>" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">

            <label style="display:block; margin-bottom:8px; font-weight:600;">Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($e['prenom']) ?>" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">

            <label style="display:block; margin-bottom:8px; font-weight:600;">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($e['email']) ?>" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">

            <label style="display:block; margin-bottom:8px; font-weight:600;">Promotion</label>
            <select name="id_promotion" required style="width:100%; padding:12px; margin-bottom:25px; border-radius:8px; border:1px solid #ddd; background:white;">
                <?php foreach($promos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == $e['id_promotion']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:10px; width:100%; cursor:pointer; font-weight:600;">Enregistrer</button>
            <p style="text-align:center;"><a href="etudiants.php" style="color:#64748b; text-decoration:none; font-size:14px; margin-top:15px; display:inline-block;">Annuler</a></p>
        </form>
    </div>
</body>
</html>