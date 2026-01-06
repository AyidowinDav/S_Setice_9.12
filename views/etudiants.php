<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

// Protection
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

// Paramètres de recherche
$search = trim($_GET['search'] ?? '');
$promo_filter = intval($_GET['promo'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Construction de la requête avec filtres
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(e.nom LIKE ? OR e.prenom LIKE ? OR e.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($promo_filter > 0) {
    $where[] = "e.id_promotion = ?";
    $params[] = $promo_filter;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Compter le total
$count_query = "SELECT COUNT(*) FROM etudiants e $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_etudiants = $count_stmt->fetchColumn();
$total_pages = ceil($total_etudiants / $per_page);

// Récupérer les étudiants avec pagination
$offset = ($page - 1) * $per_page;
$query = "
    SELECT e.*, p.nom AS promo_nom, p.annee 
    FROM etudiants e
    LEFT JOIN promotions p ON e.id_promotion = p.id
    $where_clause
    ORDER BY e.id DESC 
    LIMIT $per_page OFFSET $offset
";
$query_stmt = $pdo->prepare($query);
$query_stmt->execute($params);
$etudiants = $query_stmt->fetchAll();

// Récupérer toutes les promotions pour le formulaire
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom")->fetchAll();
if (isset($_GET['debug'])) {
    echo '<h2>DEBUG: $etudiants</h2>';
    echo '<pre>' . htmlspecialchars(print_r($etudiants, true)) . '</pre>';
    echo '<h2>DEBUG: $promotions</h2>';
    echo '<pre>' . htmlspecialchars(print_r($promotions, true)) . '</pre>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Étudiants</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-weight: 800; color: #0f172a;">Gestion des Étudiants</h1>
            <a href="../actions/export_action.php?table=etudiants&format=csv" class="btn-export" 
                style="background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>

        <?php $error = getError(); if ($error): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fcc;">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php $success = getSuccess(); if ($success): ?>
        <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cfc;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Recherche et filtres -->
        <div style="background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #edf2f7; margin-bottom: 30px;">
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Rechercher</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                        placeholder="Nom, prénom ou email..."
                        style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Promotion</label>
                    <select name="promo" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                        <option value="0">Toutes les promotions</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $promo_filter == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" 
                    style="background:#1a4a8e; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">
                    Rechercher
                </button>
                <a href="etudiants.php" 
                    style="background:#6b7280; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600;">
                    Réinitialiser
                </a>
            </form>
        </div>

        <!-- Formulaire de création -->
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 700px; border: 1px solid #edf2f7; margin-bottom: 30px;">
            <h3 style="margin-top: 0; color: #1e293b; font-size: 20px;">Ajouter un étudiant</h3>
            <form action="../actions/student_action.php" method="POST">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom *</label>
                        <input type="text" name="nom" required 
                            style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Prénom *</label>
                        <input type="text" name="prenom" required 
                            style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Email *</label>
                    <input type="email" name="email" required 
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Promotion *</label>
                    <select name="id_promotion" required 
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                        <option value="">-- Sélectionner une promotion --</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['annee']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <button type="submit" 
                    style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">
                    <i class="fas fa-user-plus"></i> Créer l'étudiant
                </button>
            </form>
        </div>

        <!-- Liste des étudiants -->
        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b;">Nom</th>
                        <th style="padding: 20px; color: #64748b;">Prénom</th>
                        <th style="padding: 20px; color: #64748b;">Email</th>
                        <th style="padding: 20px; color: #64748b;">Promotion</th>
                        <th style="padding: 20px; color: #64748b; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($etudiants)): ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                Aucun étudiant trouvé
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($etudiants as $e): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($e['nom']) ?></td>
                            <td style="padding: 20px;"><?= htmlspecialchars($e['prenom']) ?></td>
                            <td style="padding: 20px; color: #64748b;"><?= htmlspecialchars($e['email']) ?></td>
                            <td style="padding: 20px;">
                                <span style="background: #dbeafe; color: #1e40af; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <?php if (!empty($e['promo_nom'])): ?>
                                        <?= htmlspecialchars($e['promo_nom'] . (!empty($e['annee']) ? ' (' . $e['annee'] . ')' : '')) ?>
                                    <?php else: ?>
                                        Non assigné
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <a href="edit_etudiant.php?id=<?= $e['id'] ?>" style="color: #1a4a8e; margin-right: 15px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../actions/delete_action.php?type=etudiant&id=<?= $e['id'] ?>&csrf_token=<?= generateCsrfToken() ?>" 
                                    onclick="return confirm('Supprimer cet étudiant ?')" style="color: #ef4444;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div style="padding: 20px; text-align: center; border-top: 1px solid #edf2f7;">
                <?php
                $query_params = array_filter(['search' => $search, 'promo' => $promo_filter]);
                $query_string = http_build_query($query_params);
                $base_url = '?' . ($query_string ? $query_string . '&' : '');
                ?>
                <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>" 
                    style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">
                    Précédent
                </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?= $base_url ?>page=<?= $i ?>" 
                    style="background:<?= $i == $page ? '#1a4a8e' : '#e2e8f0' ?>; color:<?= $i == $page ? 'white' : '#475569' ?>; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 2px;">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>" 
                    style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">
                    Suivant
                </a>
                <?php endif; ?>

                <span style="margin-left: 20px; color: #64748b;">
                    Page <?= $page ?> sur <?= $total_pages ?> (<?= $total_etudiants ?> étudiants)
                </span>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>