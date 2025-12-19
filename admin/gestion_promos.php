<?php
session_start();
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-SETICE - Gestion des Promotions</title>
</head>
<body>

// Traitement des actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_promotion'])) {
        $nom_promotion = trim($_POST['nom_promotion']);
        $annee_academique = trim($_POST['annee_academique']);

        if (!empty($nom_promotion) && !empty($annee_academique)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO promotions (nom_promotion, annee_academique) VALUES (?, ?)");
                $stmt->execute([$nom_promotion, $annee_academique]);
                $message = "Promotion ajoutée avec succès.";
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout de la promotion : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    } elseif (isset($_POST['modifier_promotion'])) {
        $id = $_POST['id'];
        $nom_promotion = trim($_POST['nom_promotion']);
        $annee_academique = trim($_POST['annee_academique']);

        if (!empty($nom_promotion) && !empty($annee_academique)) {
            try {
                $stmt = $pdo->prepare("UPDATE promotions SET nom_promotion = ?, annee_academique = ? WHERE id = ?");
                $stmt->execute([$nom_promotion, $annee_academique, $id]);
                $message = "Promotion modifiée avec succès.";
            } catch (PDOException $e) {
                $message = "Erreur lors de la modification de la promotion : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    } elseif (isset($_POST['supprimer_promotion'])) {
        $id = $_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Promotion supprimée avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression de la promotion : " . $e->getMessage();
        }
    }
}

// Récupération des promotions
try {
    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY annee_academique DESC, nom_promotion ASC");
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des promotions : " . $e->getMessage();
    $promotions = [];
}
?>

<div class="dashboard">
    <header>
        <h1>Gestion des Promotions</h1>
        <div class="user-info">
            <span>Connecté en tant que <?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Utilisateur'; ?></span>
            <a href="../actions/logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </header>

    <main>
        <div class="stats">
            <div class="stat-card">
                <h3>Total des Promotions</h3>
                <p><?php echo count($promotions); ?></p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Erreur') === 0 ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Ajouter une Nouvelle Promotion</h2>
            <form method="POST" action="">
                <div class="input-box">
                    <input type="text" name="nom_promotion" required>
                    <label>Nom de la Promotion</label>
                </div>
                <div class="input-box">
                    <input type="text" name="annee_academique" placeholder="Ex: 2023-2024" required>
                    <label>Année Académique</label>
                </div>
                <button type="submit" name="ajouter_promotion" class="btn">Ajouter Promotion</button>
            </form>
        </div>

        <div class="table-section">
            <h2>Liste des Promotions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la Promotion</th>
                        <th>Année Académique</th>
                        <th>Date de Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($promotion['id']); ?></td>
                            <td><?php echo htmlspecialchars($promotion['nom_promotion']); ?></td>
                            <td><?php echo htmlspecialchars($promotion['annee_academique']); ?></td>
                            <td><?php echo htmlspecialchars($promotion['date_creation']); ?></td>
                            <td>
                                <button onclick="editPromotion(<?php echo $promotion['id']; ?>, '<?php echo addslashes($promotion['nom_promotion']); ?>', '<?php echo addslashes($promotion['annee_academique']); ?>')">Modifier</button>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $promotion['id']; ?>">
                                    <button type="submit" name="supprimer_promotion" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour modifier une promotion -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Modifier la Promotion</h2>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editId">
                    <div class="input-box">
                        <input type="text" name="nom_promotion" id="editNom" required>
                        <label>Nom de la Promotion</label>
                    </div>
                    <div class="input-box">
                        <input type="text" name="annee_academique" id="editAnnee" required>
                        <label>Année Académique</label>
                    </div>
                    <button type="submit" name="modifier_promotion" class="btn">Modifier Promotion</button>
                </form>
            </div>
        </div>
    </main>
</div>

<style>
.message {
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
}
.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.form-section, .table-section {
    background-color: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
table th, table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}
table th {
    background-color: rgba(255, 255, 255, 0.1);
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}
.modal-content {
    background-color: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    margin: 15% auto;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    width: 80%;
    max-width: 500px;
}
.close {
    color: #fff;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover {
    color: #ccc;
}
</style>

<script>
function editPromotion(id, nom, annee) {
    document.getElementById('editId').value = id;
    document.getElementById('editNom').value = nom;
    document.getElementById('editAnnee').value = annee;
    document.getElementById('editModal').style.display = 'block';
}

document.querySelector('.close').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
