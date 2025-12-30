<?php 
session_start();
require_once('../config/db.php');
// Récupérer les formateurs pour l'assignation immédiate
$formateurs = $pdo->query("SELECT * FROM formateurs")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Espaces Pédagogiques</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a;">Espaces Pédagogiques</h1>
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7; margin-top:30px;">
            <form action="../actions/pedagogique_action.php" method="POST">
                <input type="hidden" name="type_action" value="creer_espace">
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom de l'Espace (Matière)</label>
                    <input type="text" name="nom_espace" placeholder="Ex: Algorithmique Avancée" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Responsable (Formateur)</label>
                    <select name="id_formateur" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:white;">
                        <?php foreach($formateurs as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= $f['nom'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">Créer l'Espace</button>
            </form>
        </div>
    </main>
</body>
</html>