<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

// Paramètres de recherche
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Construction de la requête avec filtres
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nom LIKE ? OR annee LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Compter le total pour la pagination
$count_query = "SELECT COUNT(*) FROM promotions $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_promotions = $count_stmt->fetchColumn();
$total_pages = ceil($total_promotions / $per_page);

// Récupérer les promotions avec pagination
$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM promotions $where_clause ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$query_stmt = $pdo->prepare($query);
$query_stmt->execute($params);
$promos = $query_stmt->fetchAll();
if (isset($_GET['debug'])) {
    echo '<pre>' . htmlspecialchars(print_r($promos, true)) . '</pre>';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Promotions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    <main style="margin-left: 280px; padding: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-weight: 800; color: #0f172a;">Gestion des Promotions</h1>
            <a href="../actions/export_action.php?table=promotions&format=csv" class="btn-export"
                style="background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>

        <?php $error = getError(); if ($error): ?>
        <div
            style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fcc;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php $success = getSuccess(); if ($success): ?>
        <div
            style="background: #efe; color: #363; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cfc;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <!-- Recherche -->
        <div
            style="background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #edf2f7; margin-bottom: 30px;">
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Rechercher</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Nom ou année..."
                        style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <button type="submit"
                    style="background:#1a4a8e; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Rechercher</button>
                <a href="promotions.php"
                    style="background:#6b7280; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600;">Réinitialiser</a>
            </form>
        </div>

        <div
            style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 600px; border: 1px solid #edf2f7; margin-top:30px;">
            <form action="../actions/promotion_action.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Nom de la
                        Promotion</label>
                    <input type="text" name="nom" placeholder="Ex: Licence 3 INFO" required
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Année
                        Académique</label>
                    <input type="text" name="annee" placeholder="Ex: 2024-2025" required
                        style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:10px;">
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <button type="submit"
                    style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:600; width:100%;">Enregistrer
                    la promotion</button>
            </form>
        </div>

        <div
            style="background: white; border-radius: 20px; border: 1px solid #edf2f7; margin-top: 40px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                        <th style="padding: 20px; color: #64748b;">Nom</th>
                        <th style="padding: 20px; color: #64748b;">Année</th>
                        <th style="padding: 20px; color: #64748b; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($promos as $p): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 20px; font-weight: 500;"><?= htmlspecialchars($p['nom']) ?></td>
                        <td style="padding: 20px; color: #64748b;"><?= htmlspecialchars($p['annee']) ?></td>
                        <td style="padding: 20px; text-align: center;">
                            <a href="edit_promotion.php?id=<?= $p['id'] ?>"
                                style="color: #1a4a8e; margin-right: 15px;"><i class="fas fa-edit"></i></a>
                            <a href="../actions/delete_action.php?type=promotion&id=<?= $p['id'] ?>&csrf_token=<?= generateCsrfToken() ?>"
                                onclick="return confirm('Supprimer cette promotion ?')" style="color: #ef4444;"><i
                                    class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div style="padding: 20px; text-align: center; border-top: 1px solid #edf2f7;">
                <?php
                $query_string = http_build_query(array_filter(['search' => $search]));
                $base_url = '?' . ($query_string ? $query_string . '&' : '');
                ?>
                <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>"
                    style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">Précédent</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?= $base_url ?>page=<?= $i ?>"
                    style="background:<?= $i == $page ? '#1a4a8e' : '#e2e8f0' ?>; color:<?= $i == $page ? 'white' : '#475569' ?>; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 2px;"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>"
                    style="background:#e2e8f0; color:#475569; padding:8px 12px; border-radius:6px; text-decoration:none; margin:0 5px;">Suivant</a>
                <?php endif; ?>

                <span style="margin-left: 20px; color: #64748b;">Page <?= $page ?> sur <?= $total_pages ?>
                    (<?= $total_promotions ?> promotions)</span>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="../actions/promotion_action.php"]');

        form.addEventListener('submit', function(e) {
            const nom = form.querySelector('input[name="nom"]').value.trim();
            const annee = form.querySelector('input[name="annee"]').value.trim();

            // Validation du nom
            if (nom.length < 2) {
                alert('Le nom de la promotion doit contenir au moins 2 caractères.');
                e.preventDefault();
                return;
            }

            // Validation de l'année (format YYYY-YYYY ou YYYY)
            const anneeRegex = /^(\d{4})(-\d{4})?$/;
            if (!anneeRegex.test(annee)) {
                alert('L\'année doit être au format YYYY ou YYYY-YYYY (ex: 2024 ou 2024-2025).');
                e.preventDefault();
                return;
            }

            // Extraire la première année
            const premierAnnee = parseInt(annee.split('-')[0]);
            const currentYear = new Date().getFullYear();

            if (premierAnnee < 2000 || premierAnnee > currentYear + 10) {
                alert('L\'année doit être entre 2000 et ' + (currentYear + 10) + '.');
                e.preventDefault();
                return;
            }

            // Si format YYYY-YYYY, vérifier la cohérence
            if (annee.includes('-')) {
                const deuxiemeAnnee = parseInt(annee.split('-')[1]);
                if (deuxiemeAnnee !== premierAnnee + 1) {
                    alert('Les années doivent être consécutives (ex: 2024-2025).');
                    e.preventDefault();
                    return;
                }
            }
        });
    });
    </script>
</body>

</html>