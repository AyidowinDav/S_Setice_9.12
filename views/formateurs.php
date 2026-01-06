<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nom LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_query = "SELECT COUNT(*) FROM formateurs $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM formateurs $where_clause ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$query_stmt = $pdo->prepare($query);
$query_stmt->execute($params);
$formateurs = $query_stmt->fetchAll();
if (isset($_GET['debug'])) {
    echo '<pre>' . htmlspecialchars(print_r($formateurs, true)) . '</pre>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Formateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-weight: 800; color: #0f172a;">Gestion des Formateurs</h1>
            <a href="../actions/export_action.php?table=formateurs&format=csv" 
                style="background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>

        <?php $error = getError(); if ($error): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php $success = getSuccess(); if ($success): ?>
        <div style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Recherche -->
        <div style="background: white; padding: 20px; border-radius: 20px; margin-bottom: 30px; border: 1px solid #edf2f7;">
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Rechercher</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nom ou email..."
                        style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Rechercher</button>
                <a href="formateurs.php" style="background:#6b7280; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600;">Réinitialiser</a>
            </form>
        </div>

        <!-- Formulaire création -->
        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 600px; border: 1px solid #edf2f7; margin-bottom: 30px;">
            <h3 style="margin-top: 0; color: #1e293b;">Ajouter un formateur</h3>
            <form action="../actions/formateur_action.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom complet *</label>
                    <input type="text" name="nom" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Email *</label>
                    <input type="email" name="email" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">
                    <i class="fas fa-user-plus"></i> Créer le formateur
                </button>
            </form>
        </div>

        <!-- Liste -->
        <div style="background: white; border-radius: 20px; border: 1px solid #edf2f7; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 20px; text-align: left; color: #64748b;">Nom</th>
                        <th style="padding: 20px; text-align: left; color: #64748b;">Email</th>
                        <th style="padding: 20px; text-align: center; color: #64748b;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($formateurs as $f): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($f['nom']) ?></td>
                        <td style="padding: 20px; color: #64748b;"><?= htmlspecialchars($f['email']) ?></td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_formateur.php?id=<?= $f['id'] ?>" style="color: #1a4a8e; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="../actions/delete_action.php?type=formateur&id=<?= $f['id'] ?>&csrf_token=<?= generateCsrfToken() ?>" 
                                onclick="return confirm('Supprimer ce formateur ?')" style="color: #ef4444;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <div style="padding: 20px; text-align: center; border-top: 1px solid #edf2f7;">
                <?php
                $query_string = http_build_query(array_filter(['search' => $search]));
                $base_url = '?' . ($query_string ? $query_string . '&' : '');
                ?>
                <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>" style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">Précédent</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?= $base_url ?>page=<?= $i ?>" style="background:<?= $i == $page ? '#1a4a8e' : '#e2e8f0' ?>; color:<?= $i == $page ? 'white' : '#475569' ?>; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 2px;"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>" style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">Suivant</a>
                <?php endif; ?>

                <span style="margin-left: 20px; color: #64748b;">Page <?= $page ?> sur <?= $total_pages ?> (<?= $total ?> formateurs)</span>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>