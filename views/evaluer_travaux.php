<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FORMATEUR') {
    header('Location: ../index.php');
    exit;
}

$id_formateur = $_SESSION['user_id'];
// map user id to formateurs.id if applicable
$id_formateur_real = $id_formateur;
try {
    $stmtMap = $pdo->prepare('SELECT id FROM formateurs WHERE id_utilisateur = ?');
    $stmtMap->execute([$id_formateur]);
    $frow = $stmtMap->fetch();
    if ($frow && !empty($frow['id'])) $id_formateur_real = $frow['id'];
} catch (Exception $e) {
    // ignore and fallback to user id
}
$id_travail = isset($_GET['id_travail']) ? intval($_GET['id_travail']) : 0;

// Si id_travail fourni, filtrer par travail
$where_travail = $id_travail > 0 ? "AND t.id = :id_travail" : "";

// Récupérer les rendus à évaluer
$query = "
    SELECT
        rt.id as rendu_id,
        rt.contenu,
        rt.date_rendu,
        rt.est_en_retard,
        t.titre as travail_titre,
        t.date_limite,
        e.nom_espace,
        et.nom as etudiant_nom,
        et.prenom as etudiant_prenom,
        at.statut,
        ev.id as evaluation_id,
        ev.note,
        ev.commentaire
    FROM rendus_travaux rt
    JOIN assignations_travaux at ON rt.id_assignation = at.id
    JOIN travaux t ON at.id_travail = t.id
    JOIN espaces_pedagogiques e ON t.id_espace = e.id
    JOIN etudiants et ON at.id_etudiant = et.id
    LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
    WHERE e.id_formateur = :id_formateur
    $where_travail
    ORDER BY rt.date_rendu DESC
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_formateur', $id_formateur_real, PDO::PARAM_INT);
if ($id_travail > 0) {
    $stmt->bindParam(':id_travail', $id_travail, PDO::PARAM_INT);
}
$stmt->execute();
$rendus = $stmt->fetchAll();

// Récupérer les travaux pour le filtre
$stmtTravaux = $pdo->prepare("
    SELECT DISTINCT t.id, t.titre
    FROM travaux t
    WHERE t.id_formateur = ?
    ORDER BY t.date_creation DESC
");
$stmtTravaux->execute([$id_formateur]);
$travaux = $stmtTravaux->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Évaluer les Travaux</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Évaluer les Travaux</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Attribuez des notes et commentaires aux rendus</p>

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

        <!-- Filtre par travail -->
        <div style="background: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #edf2f7;">
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Filtrer par travail</label>
                    <select name="id_travail" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                        <option value="0">Tous les travaux</option>
                        <?php foreach($travaux as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $id_travail == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">
                    Filtrer
                </button>
            </form>
        </div>

        <?php if (empty($rendus)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <p style="color: #64748b;">Aucun rendu à évaluer pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach($rendus as $r): ?>
            <div style="background: white; padding: 30px; border-radius: 20px; margin-bottom: 20px; border: 1px solid #edf2f7;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #1e293b; font-size: 20px;">
                            <?= htmlspecialchars($r['etudiant_nom']) ?> <?= htmlspecialchars($r['etudiant_prenom']) ?>
                        </h3>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <span style="color: #64748b; font-size: 14px;">
                                <i class="fas fa-tasks"></i> <?= htmlspecialchars($r['travail_titre']) ?>
                            </span>
                            <span style="color: #64748b; font-size: 14px;">
                                <i class="fas fa-book"></i> <?= htmlspecialchars($r['nom_espace']) ?>
                            </span>
                            <span style="color: #64748b; font-size: 14px;">
                                <i class="fas fa-calendar"></i> Rendu le <?= date('d/m/Y à H:i', strtotime($r['date_rendu'])) ?>
                            </span>
                            <?php if ($r['est_en_retard']): ?>
                            <span style="background: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-exclamation-triangle"></i> EN RETARD
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($r['evaluation_id']): ?>
                    <span style="background: #d1fae5; color: #065f46; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                        <i class="fas fa-check-circle"></i> ÉVALUÉ
                    </span>
                    <?php endif; ?>
                </div>

                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px;">
                        <i class="fas fa-file-alt"></i> Contenu du rendu
                    </h4>
                    <p style="color: #475569; white-space: pre-wrap; line-height: 1.7; margin: 0;">
                        <?= nl2br(htmlspecialchars($r['contenu'])) ?>
                    </p>
                </div>

                <?php
                // Lister les fichiers présents sur disque pour ce rendu (uploads/rendus/{id})
                $renduId = intval($r['rendu_id']);
                $diskDir = __DIR__ . '/../uploads/rendus/' . $renduId;
                if (is_dir($diskDir)) {
                    $items = array_values(array_filter(scandir($diskDir), function($n){ return $n !== '.' && $n !== '..'; }));
                    // base URL root (e.g. /S-SETICE_9.12)
                    $rootUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
                    if (!empty($items)) {
                        echo '<div style="background:#fff; padding:12px; border-radius:10px; margin-bottom:18px;">';
                        echo '<strong style="display:block; margin-bottom:8px; color:#1e293b;">Fichiers joints :</strong>';
                        echo '<ul style="margin:0; padding-left:18px; color:#475569;">';
                        foreach ($items as $it) {
                            $safe = rawurlencode($it);
                            $webPath = $rootUrl . '/uploads/rendus/' . $renduId . '/' . $safe;
                            echo '<li><a href="' . htmlspecialchars($webPath) . '" target="_blank" rel="noopener">' . htmlspecialchars($it) . '</a></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                }
                ?>

                <?php if ($r['evaluation_id']): ?>
                    <!-- Déjà évalué -->
                    <div style="background: #ecfdf5; padding: 20px; border-radius: 12px; border-left: 4px solid #10b981;">
                        <p style="margin: 0 0 10px 0; color: #065f46; font-weight: 700; font-size: 18px;">
                            <i class="fas fa-star"></i> Note attribuée : <?= $r['note'] ?>/20
                        </p>
                        <?php if ($r['commentaire']): ?>
                        <p style="margin: 0; color: #047857; font-size: 14px;">
                            <strong>Commentaire :</strong> <?= htmlspecialchars($r['commentaire']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Formulaire d'évaluation -->
                    <form action="../actions/rendu_action.php" method="POST" onsubmit="return confirm('Confirmer cette évaluation ? Vous ne pourrez plus la modifier.');">
                        <input type="hidden" name="action" value="evaluer_rendu">
                        <input type="hidden" name="id_rendu" value="<?= $r['rendu_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                        <div style="display: grid; grid-template-columns: 200px 1fr; gap: 28px; margin-bottom: 20px; align-items: start;">
                            <div>
                                <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Note (sur 20) *</label>
                                <input type="number" name="note" min="0" max="20" step="0.5" required 
                                    style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 18px; font-weight: 700;">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Commentaire (optionnel)</label>
                                <input type="text" name="commentaire" placeholder="Excellent travail, continuez ainsi..."
                                    style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; margin-top:4px;">
                            </div>
                        </div>

                        <button type="submit" style="background:#8b5cf6; color:white; border:none; padding:15px 30px; border-radius:10px; cursor:pointer; font-weight:700; font-size: 16px;">
                            <i class="fas fa-check"></i> Enregistrer l'évaluation
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>