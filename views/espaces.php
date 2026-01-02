<?php 
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-weight: 800; color: #0f172a;">Espaces Pédagogiques</h1>
            <a href="../actions/export_action.php?table=espaces_pedagogiques&format=csv" class="btn-export" style="background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>
        
        <?php $error = getError(); if ($error): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fcc;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php $success = getSuccess(); if ($success): ?>
            <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cfc;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7; margin-top:30px;">
            <form action="../actions/pedagogique_action.php" method="POST">
                <input type="hidden" name="type_action" value="creer_espace">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="../actions/pedagogique_action.php"]');
        
        form.addEventListener('submit', function(e) {
            const nomEspace = form.querySelector('input[name="nom_espace"]').value.trim();
            const idFormateur = form.querySelector('select[name="id_formateur"]').value;
            
            // Validation du nom de l'espace
            if (nomEspace.length < 3) {
                alert('Le nom de l\'espace doit contenir au moins 3 caractères.');
                e.preventDefault();
                return;
            }
            
            // Validation du formateur
            if (!idFormateur) {
                alert('Veuillez sélectionner un formateur responsable.');
                e.preventDefault();
                return;
            }
        });
    });
    </script>
</body>
</html>