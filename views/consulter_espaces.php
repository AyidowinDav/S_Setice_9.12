<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

// Récupérer tous les espaces avec stats
$stmt = $pdo->query("
    SELECT 
        e.id,
        e.nom_espace,
        f.nom as formateur_nom,
        COUNT(DISTINCT ee.id_etudiant) as nb_etudiants
    FROM espaces_pedagogiques e
    LEFT JOIN formateurs f ON e.id_formateur = f.id
    LEFT JOIN etudiant_espace ee ON e.id = ee.id_espace
    GROUP BY e.id
    ORDER BY e.nom_espace
");
$espaces = $stmt->fetchAll();

// Récupérer les promotions pour le formulaire d'assignation
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Consultation Espaces</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Consultation des Espaces</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Vue d'ensemble de tous les espaces pédagogiques</p>

        <?php $error = getError(); if ($error): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php $success = getSuccess(); if ($success): ?>
        <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($espaces)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px; border: 1px solid #edf2f7;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3 style="color: #1e293b; margin-bottom: 10px;">Aucun espace pédagogique</h3>
                <p style="color: #64748b; margin-bottom: 30px;">Commencez par créer votre premier espace pédagogique</p>
                <a href="espaces.php" style="display: inline-block; background: #1a4a8e; color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-plus"></i> Créer un espace
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach($espaces as $esp): ?>
                <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #edf2f7; transition: 0.3s;" onmouseover="this.style.boxShadow='0 10px 20px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book" style="color: white; font-size: 20px;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; color: #1e293b; font-size: 18px;"><?= htmlspecialchars($esp['nom_espace']) ?></h3>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <p style="color: #64748b; font-size: 14px; margin: 0 0 5px 0;">
                            <i class="fas fa-chalkboard-teacher" style="color: #1a4a8e;"></i>
                            <strong>Formateur:</strong> <?= $esp['formateur_nom'] ? htmlspecialchars($esp['formateur_nom']) : '<em>Non assigné</em>' ?>
                        </p>
                        <p style="color: #64748b; font-size: 14px; margin: 0;">
                            <i class="fas fa-users" style="color: #10b981;"></i>
                            <strong><?= $esp['nb_etudiants'] ?></strong> étudiant(s)
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <a href="edit_espace.php?id=<?= $esp['id'] ?>" style="flex: 1; background: #f1f5f9; color: #1a4a8e; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px;">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="#" onclick="assignPromo(<?= $esp['id'] ?>); return false;" style="flex: 1; background: #1a4a8e; color: white; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px;">
                            <i class="fas fa-link"></i> Assigner
                        </a>
                        <a href="#" onclick="deleteEspace(<?= $esp['id'] ?>, '<?= htmlspecialchars($esp['nom_espace']) ?>'); return false;" style="flex: 0.8; background: #dc2626; color: white; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px;">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal assignation (simple) -->
            <div id="modalAssign" style="display:none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
                <div style="background: white; padding: 40px; border-radius: 20px; max-width: 500px; width: 90%;">
                    <h3 style="margin-top: 0;">Assigner une promotion</h3>
                    <form action="../actions/pedagogique_action.php" method="POST">
                        <input type="hidden" name="type_action" value="assigner_promo">
                        <input type="hidden" name="id_espace" id="modal_id_espace">
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600;">Promotion</label>
                            <select name="id_promotion" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                                <?php foreach($promotions as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" style="flex: 1; background:#1a4a8e; color:white; border:none; padding:15px; border-radius:10px; cursor:pointer; font-weight:600;">Valider</button>
                            <button type="button" onclick="closeModal()" style="flex: 1; background:#6b7280; color:white; border:none; padding:15px; border-radius:10px; cursor:pointer; font-weight:600;">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function assignPromo(id_espace) {
        document.getElementById('modal_id_espace').value = id_espace;
        document.getElementById('modalAssign').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modalAssign').style.display = 'none';
    }

    function deleteEspace(id, nom) {
        if (confirm('Êtes-vous sûr de vouloir supprimer l\'espace "' + nom + '" ? Cette action est irréversible.')) {
            window.location.href = '../actions/delete_action.php?type=espace&id=' + id + '&csrf_token=<?= generateCsrfToken() ?>';
        }
    }
    </script>
</body>
</html>