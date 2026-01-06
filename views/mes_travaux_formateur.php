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

// Récupérer l'ID réel du formateur depuis la table formateurs
$getFormateurId = $pdo->prepare("SELECT id FROM formateurs WHERE id_utilisateur = ?");
$getFormateurId->execute([$id_formateur]);
$formateur = $getFormateurId->fetch();

if (!$formateur) {
    $travaux = [];
} else {
    $id_formateur_real = $formateur['id'];

    // Récupérer tous les travaux du formateur avec statistiques
    $stmt = $pdo->prepare("
        SELECT
            t.id,
            t.titre,
            t.description,
            t.date_limite,
            t.type_travail,
            t.date_creation,
            e.nom_espace,
            COUNT(DISTINCT at.id) as nb_assignations,
            COUNT(DISTINCT rt.id) as nb_rendus,
            COUNT(DISTINCT ev.id) as nb_evalues,
            AVG(ev.note) as moyenne_notes
        FROM travaux t
        JOIN espaces_pedagogiques e ON t.id_espace = e.id
        LEFT JOIN assignations_travaux at ON t.id = at.id_travail
        LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
        LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
        WHERE t.id_formateur = ?
        GROUP BY t.id
        ORDER BY t.date_creation DESC
    ");
    $stmt->execute([$id_formateur_real]);
    $travaux = $stmt->fetchAll();
}
// Préparer la requête de fichiers (si la table existe)
$fileStmt = null;
try {
    $fileStmt = $pdo->prepare("SELECT * FROM fichiers_travaux WHERE id_travail = ?");
} catch (Exception $e) {
    $fileStmt = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Mes Travaux Créés</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 5px;">Mes Travaux Créés</h1>
                <p style="color: #64748b;">Suivez les rendus et évaluations de vos travaux</p>
            </div>
            <a href="nouveau_travail.php" style="background: #1a4a8e; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-plus"></i> Nouveau travail
            </a>
        </div>

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

        <?php if (empty($travaux)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3 style="color: #1e293b; margin-bottom: 10px;">Aucun travail créé</h3>
                <p style="color: #64748b; margin-bottom: 30px;">Commencez par créer votre premier travail</p>
                <a href="nouveau_travail.php" style="display: inline-block; background: #1a4a8e; color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-plus"></i> Créer un travail
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 20px;">
                <?php foreach($travaux as $tr):
                    $pourcentage_rendus = $tr['nb_assignations'] > 0 ? round(($tr['nb_rendus'] / $tr['nb_assignations']) * 100) : 0;
                    $pourcentage_evalues = $tr['nb_rendus'] > 0 ? round(($tr['nb_evalues'] / $tr['nb_rendus']) * 100) : 0;

                    $dateLimite = new DateTime($tr['date_limite']);
                    $maintenant = new DateTime();
                    $estDepasse = $maintenant > $dateLimite;
                ?>
                <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #edf2f7;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; color: #1e293b; font-size: 20px;"><?= htmlspecialchars($tr['titre']) ?></h3>
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <span style="background: #e0e7ff; color: #1a4a8e; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <?= $tr['type_travail'] ?>
                                </span>
                                <span style="color: #64748b; font-size: 14px;">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($tr['nom_espace']) ?>
                                </span>
                                <span style="color: <?= $estDepasse ? '#dc2626' : '#10b981' ?>; font-size: 14px; font-weight: 600;">
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y à H:i', strtotime($tr['date_limite'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p style="color: #475569; margin: 15px 0; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars(substr($tr['description'], 0, 150))) ?><?= strlen($tr['description']) > 150 ? '...' : '' ?>
                    </p>

                    <?php if ($fileStmt):
                        try {
                            $fileStmt->execute([$tr['id']]);
                            $fichiers = $fileStmt->fetchAll();
                        } catch (Exception $e) {
                            $fichiers = [];
                        }
                    else:
                        $fichiers = [];
                    endif;

                    if (!empty($fichiers)): ?>
                        <div style="background:#fff; border:1px solid #edf2f7; padding:12px; border-radius:8px; margin-bottom:12px;">
                            <p style="margin:0 0 8px 0; color:#475569; font-weight:600;">Fichiers fournis :</p>
                            <ul style="margin:0; padding-left:18px; color:#475569;">
                                <?php foreach($fichiers as $f): ?>
                                    <li><a href="<?= htmlspecialchars($f['chemin_fichier']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($f['nom_fichier']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Statistiques -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 12px;">
                        <div>
                            <p style="margin: 0; color: #64748b; font-size: 12px;">Assignés</p>
                            <p style="margin: 5px 0 0 0; color: #1e293b; font-size: 24px; font-weight: 800;"><?= $tr['nb_assignations'] ?></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #64748b; font-size: 12px;">Rendus</p>
                            <p style="margin: 5px 0 0 0; color: #10b981; font-size: 24px; font-weight: 800;"><?= $tr['nb_rendus'] ?></p>
                            <div style="width: 100%; height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 5px;">
                                <div style="width: <?= $pourcentage_rendus ?>%; height: 100%; background: #10b981; border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div>
                            <p style="margin: 0; color: #64748b; font-size: 12px;">Évalués</p>
                            <p style="margin: 5px 0 0 0; color: #8b5cf6; font-size: 24px; font-weight: 800;"><?= $tr['nb_evalues'] ?></p>
                            <div style="width: 100%; height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 5px;">
                                <div style="width: <?= $pourcentage_evalues ?>%; height: 100%; background: #8b5cf6; border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div>
                            <p style="margin: 0; color: #64748b; font-size: 12px;">Moyenne</p>
                            <p style="margin: 5px 0 0 0; color: #f59e0b; font-size: 24px; font-weight: 800;">
                                <?= $tr['moyenne_notes'] ? round($tr['moyenne_notes'], 2) . '/20' : 'N/A' ?>
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="evaluer_travaux.php?id_travail=<?= $tr['id'] ?>"
                           style="flex: 1; background: #1a4a8e; color: white; padding: 12px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-marker"></i> Évaluer les rendus (<?= $tr['nb_rendus'] - $tr['nb_evalues'] ?> en attente)
                        </a>
                        <form method="POST" action="../actions/delete_action.php" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce travail ? Cette action est irréversible.');">
                            <input type="hidden" name="action" value="supprimer_travail">
                            <input type="hidden" name="id_travail" value="<?= $tr['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" style="background: #dc2626; color: white; border: none; padding: 12px 20px; border-radius: 10px; cursor: pointer; font-weight: 600;" title="Supprimer ce travail">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // Auto-hide messages after 10 seconds
    setTimeout(function() {
        const messages = document.querySelectorAll('div[style*="background: #fee"], div[style*="background: #efe"]');
        messages.forEach(function(message) {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        });
    }, 10000);
    </script>
</body>
</html>
