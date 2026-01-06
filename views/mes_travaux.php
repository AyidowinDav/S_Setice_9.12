<?php
session_start();
require_once('../config/db.php');
require_once('../includes/messages.php');

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

// Filtre statut
$statut_filter = $_GET['statut'] ?? 'all';
$where_statut = "";

if ($statut_filter !== 'all') {
    $where_statut = "AND at.statut = :statut";
}

// Récupérer les travaux assignés
$query = "
    SELECT
        at.id as id_assignation,
        at.statut,
        at.date_assignation,
        t.id as id_travail,
        t.titre,
        t.description,
        t.date_limite,
        t.type_travail,
        e.nom_espace,
        f.nom as formateur_nom,
        rt.id as rendu_id,
        rt.date_rendu,
        ev.note,
        ev.commentaire,
        GROUP_CONCAT(DISTINCT CONCAT(ft.nom_fichier, '|', ft.chemin_fichier, '|', ft.taille_fichier) SEPARATOR ';;') as fichiers
    FROM assignations_travaux at
    JOIN travaux t ON at.id_travail = t.id
    JOIN espaces_pedagogiques e ON t.id_espace = e.id
    JOIN formateurs f ON t.id_formateur = f.id
    LEFT JOIN rendus_travaux rt ON at.id = rt.id_assignation
    LEFT JOIN evaluations ev ON rt.id = ev.id_rendu
    LEFT JOIN fichiers_travaux ft ON t.id = ft.id_travail
    WHERE at.id_etudiant = :id_etudiant
    $where_statut
    GROUP BY at.id, t.id, rt.id, ev.id
    ORDER BY t.date_limite ASC
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
if ($statut_filter !== 'all') {
    $stmt->bindParam(':statut', $statut_filter, PDO::PARAM_STR);
}
$stmt->execute();
$travaux = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Mes Travaux</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Mes Travaux</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Consultez et rendez vos travaux assignés</p>

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

        <!-- Filtres -->
        <div style="background: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #edf2f7;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="?statut=all" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; <?= $statut_filter === 'all' ? 'background:#1a4a8e; color:white;' : 'background:#f1f5f9; color:#64748b;' ?>">
                    <i class="fas fa-list"></i> Tous (<?= count($travaux) ?>)
                </a>
                <a href="?statut=EN_ATTENTE" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; <?= $statut_filter === 'EN_ATTENTE' ? 'background:#ef4444; color:white;' : 'background:#f1f5f9; color:#64748b;' ?>">
                    <i class="fas fa-clock"></i> À rendre
                </a>
                <a href="?statut=RENDU" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; <?= $statut_filter === 'RENDU' ? 'background:#10b981; color:white;' : 'background:#f1f5f9; color:#64748b;' ?>">
                    <i class="fas fa-check"></i> Rendus
                </a>
                <a href="?statut=EVALUE" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; <?= $statut_filter === 'EVALUE' ? 'background:#8b5cf6; color:white;' : 'background:#f1f5f9; color:#64748b;' ?>">
                    <i class="fas fa-star"></i> Évalués
                </a>
                <a href="?statut=EN_RETARD" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; <?= $statut_filter === 'EN_RETARD' ? 'background:#f59e0b; color:white;' : 'background:#f1f5f9; color:#64748b;' ?>">
                    <i class="fas fa-exclamation-triangle"></i> En retard
                </a>
            </div>
        </div>

        <!-- Liste des travaux -->
        <?php if (empty($travaux)): ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <p style="color: #64748b;">Aucun travail trouvé pour ce filtre.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 20px;">
                <?php foreach($travaux as $tr): 
                    $dateLimite = new DateTime($tr['date_limite']);
                    $maintenant = new DateTime();
                    $diff = $maintenant->diff($dateLimite);
                    $estUrgent = $tr['statut'] === 'EN_ATTENTE' && $diff->days <= 2 && $maintenant < $dateLimite;
                    
                    $couleur_statut = [
                        'EN_ATTENTE' => '#ef4444',
                        'RENDU' => '#10b981',
                        'EVALUE' => '#8b5cf6',
                        'EN_RETARD' => '#f59e0b'
                    ];
                ?>
                <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #edf2f7; <?= $estUrgent ? 'border-left: 4px solid #ef4444;' : '' ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; color: #1e293b; font-size: 20px;"><?= htmlspecialchars($tr['titre']) ?></h3>
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <span style="color: #64748b; font-size: 14px;">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($tr['nom_espace']) ?>
                                </span>
                                <span style="color: #64748b; font-size: 14px;">
                                    <i class="fas fa-user-tie"></i> <?= htmlspecialchars($tr['formateur_nom']) ?>
                                </span>
                                <span style="color: #64748b; font-size: 14px;">
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y à H:i', strtotime($tr['date_limite'])) ?>
                                </span>
                            </div>
                        </div>
                        <span style="background: <?= $couleur_statut[$tr['statut']] ?>; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 13px; white-space: nowrap;">
                            <?= $tr['statut'] ?>
                        </span>
                    </div>

                    <?php if ($estUrgent): ?>
                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #f59e0b;">
                        <p style="color: #92400e; margin: 0; font-weight: 700; font-size: 14px;">
                            <i class="fas fa-fire"></i> URGENT : Plus que <?= $diff->days ?> jour<?= $diff->days > 1 ? 's' : '' ?> !
                        </p>
                    </div>
                    <?php endif; ?>

                    <p style="color: #475569; line-height: 1.7; margin: 15px 0;">
                        <?= nl2br(htmlspecialchars(substr($tr['description'], 0, 200))) ?><?= strlen($tr['description']) > 200 ? '...' : '' ?>
                    </p>

                    <?php if (!empty($tr['fichiers'])): ?>
                    <div style="margin: 15px 0; padding: 15px; background: #f8fafc; border-radius: 10px;">
                        <h4 style="margin: 0 0 10px 0; color: #1e293b; font-size: 16px;">
                            <i class="fas fa-paperclip"></i> Fichiers attachés
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <?php
                            $fichiers = explode(';;', $tr['fichiers']);
                            foreach ($fichiers as $fichier):
                                list($nom, $chemin, $taille) = explode('|', $fichier);
                                $taille_mb = round($taille / 1024 / 1024, 2);
                            ?>
                            <a href="../<?= htmlspecialchars($chemin) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #1a4a8e; font-weight: 500; font-size: 14px;">
                                <i class="fas fa-file"></i>
                                <?= htmlspecialchars($nom) ?>
                                <span style="color: #64748b; font-size: 12px;">(<?= $taille_mb ?> MB)</span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($tr['statut'] === 'EVALUE' && $tr['note'] !== null): ?>
                    <div style="background: #f0fdf4; padding: 15px; border-radius: 10px; margin-top: 15px; border-left: 3px solid #10b981;">
                        <p style="margin: 0 0 5px 0; color: #166534; font-weight: 700;">
                            <i class="fas fa-star"></i> Note : <?= $tr['note'] ?>/20
                        </p>
                        <?php if ($tr['commentaire']): ?>
                        <p style="margin: 5px 0 0 0; color: #15803d; font-size: 14px;">
                            <strong>Commentaire :</strong> <?= htmlspecialchars($tr['commentaire']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <?php if ($tr['statut'] === 'EN_ATTENTE'): ?>
                            <a href="rendre_travail.php?id=<?= $tr['id_assignation'] ?>" style="flex: 1; background: #10b981; color: white; padding: 12px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600;">
                                <i class="fas fa-upload"></i> Rendre le travail
                            </a>
                        <?php elseif ($tr['rendu_id']): ?>
                            <span style="flex: 1; background: #e0e7ff; color: #3730a3; padding: 12px; border-radius: 10px; text-align: center; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Travail rendu le <?= date('d/m/Y', strtotime($tr['date_rendu'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>