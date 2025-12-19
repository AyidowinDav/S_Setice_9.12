<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

// Vérifier si l'utilisateur est connecté et est un admin ou directeur
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'directeur'])) {
    header('Location: ../index.php');
    exit();
}

// Traitement des actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_utilisateur'])) {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($_POST['mot_de_passe']) && !empty($role)) {
            try {
                $pdo->beginTransaction();

                // Insérer dans utilisateurs
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $mot_de_passe, $role]);
                $utilisateur_id = $pdo->lastInsertId();

                // Insérer dans la table spécifique selon le rôle
                if ($role === 'etudiant') {
                    $numero_etudiant = trim($_POST['numero_etudiant']);
                    $promotion_id = $_POST['promotion_id'];
                    $date_naissance = $_POST['date_naissance'];
                    $adresse = trim($_POST['adresse']);
                    $telephone = trim($_POST['telephone']);

                    $stmt = $pdo->prepare("INSERT INTO etudiants (utilisateur_id, numero_etudiant, promotion_id, date_naissance, adresse, telephone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$utilisateur_id, $numero_etudiant, $promotion_id ?: null, $date_naissance ?: null, $adresse, $telephone]);
                } elseif ($role === 'directeur') {
                    $specialite = trim($_POST['specialite']);
                    $departement = trim($_POST['departement']);
                    $date_embauche = $_POST['date_embauche'];

                    $stmt = $pdo->prepare("INSERT INTO directeurs (utilisateur_id, specialite, departement, date_embauche) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$utilisateur_id, $specialite, $departement, $date_embauche ?: null]);
                }

                $pdo->commit();
                $message = "Utilisateur ajouté avec succès.";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs obligatoires.";
        }
    } elseif (isset($_POST['modifier_utilisateur'])) {
        $id = $_POST['id'];
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($role)) {
            try {
                $pdo->beginTransaction();

                // Mettre à jour utilisateurs
                $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$nom, $prenom, $email, $role, $id]);

                // Supprimer les anciennes données spécifiques
                $stmt = $pdo->prepare("DELETE FROM etudiants WHERE utilisateur_id = ?");
                $stmt->execute([$id]);
                $stmt = $pdo->prepare("DELETE FROM directeurs WHERE utilisateur_id = ?");
                $stmt->execute([$id]);

                // Insérer les nouvelles données spécifiques
                if ($role === 'etudiant') {
                    $numero_etudiant = trim($_POST['numero_etudiant']);
                    $promotion_id = $_POST['promotion_id'];
                    $date_naissance = $_POST['date_naissance'];
                    $adresse = trim($_POST['adresse']);
                    $telephone = trim($_POST['telephone']);

                    $stmt = $pdo->prepare("INSERT INTO etudiants (utilisateur_id, numero_etudiant, promotion_id, date_naissance, adresse, telephone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $numero_etudiant, $promotion_id ?: null, $date_naissance ?: null, $adresse, $telephone]);
                } elseif ($role === 'directeur') {
                    $specialite = trim($_POST['specialite']);
                    $departement = trim($_POST['departement']);
                    $date_embauche = $_POST['date_embauche'];

                    $stmt = $pdo->prepare("INSERT INTO directeurs (utilisateur_id, specialite, departement, date_embauche) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id, $specialite, $departement, $date_embauche ?: null]);
                }

                $pdo->commit();
                $message = "Utilisateur modifié avec succès.";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Erreur lors de la modification de l'utilisateur : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir tous les champs obligatoires.";
        }
    } elseif (isset($_POST['supprimer_utilisateur'])) {
        $id = $_POST['id'];

        try {
            $pdo->beginTransaction();

            // Supprimer les données spécifiques
            $stmt = $pdo->prepare("DELETE FROM etudiants WHERE utilisateur_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM directeurs WHERE utilisateur_id = ?");
            $stmt->execute([$id]);

            // Supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            $message = "Utilisateur supprimé avec succès.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
        }
    }
}

// Récupération des utilisateurs
try {
    $stmt = $pdo->query("
        SELECT u.*, e.numero_etudiant, e.date_naissance, e.adresse, e.telephone, d.specialite, d.departement, d.date_embauche, p.nom_promotion
        FROM utilisateurs u
        LEFT JOIN etudiants e ON u.id = e.utilisateur_id
        LEFT JOIN directeurs d ON u.id = d.utilisateur_id
        LEFT JOIN promotions p ON e.promotion_id = p.id
        ORDER BY u.nom, u.prenom
    ");
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
    $utilisateurs = [];
}

// Récupération des promotions pour le formulaire
try {
    $stmt = $pdo->query("SELECT id, nom_promotion FROM promotions ORDER BY nom_promotion");
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $promotions = [];
}
?>

<div class="dashboard">
    <header>
        <h1>Gestion des Utilisateurs</h1>
        <div class="user-info">
            <span>Connecté en tant que <?php echo htmlspecialchars($_SESSION['role']); ?></span>
            <a href="../actions/logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </header>

    <main>
        <div class="stats">
            <div class="stat-card">
                <h3>Total des Utilisateurs</h3>
                <p><?php echo count($utilisateurs); ?></p>
            </div>
            <div class="stat-card">
                <h3>Étudiants</h3>
                <p><?php echo count(array_filter($utilisateurs, function($u) { return $u['role'] === 'etudiant'; })); ?></p>
            </div>
            <div class="stat-card">
                <h3>Formateurs</h3>
                <p><?php echo count(array_filter($utilisateurs, function($u) { return $u['role'] === 'formateur'; })); ?></p>
            </div>
            <div class="stat-card">
                <h3>Directeurs</h3>
                <p><?php echo count(array_filter($utilisateurs, function($u) { return $u['role'] === 'directeur'; })); ?></p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Erreur') === 0 ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Ajouter un Nouvel Utilisateur</h2>
            <form method="POST" action="">
                <div class="input-box">
                    <input type="text" name="nom" required>
                    <label>Nom</label>
                </div>
                <div class="input-box">
                    <input type="text" name="prenom" required>
                    <label>Prénom</label>
                </div>
                <div class="input-box">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <input type="password" name="mot_de_passe" required>
                    <label>Mot de passe</label>
                </div>
                <div class="input-box">
                    <select name="role" id="role" required onchange="toggleRoleFields()">
                        <option value="">Sélectionner un rôle</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="formateur">Formateur</option>
                        <option value="directeur">Directeur</option>
                    </select>
                    <label>Rôle</label>
                </div>

                <!-- Champs spécifiques aux étudiants -->
                <div id="etudiant-fields" style="display: none;">
                    <div class="input-box">
                        <input type="text" name="numero_etudiant">
                        <label>Numéro d'étudiant</label>
                    </div>
                    <div class="input-box">
                        <select name="promotion_id">
                            <option value="">Sélectionner une promotion</option>
                            <?php foreach ($promotions as $promotion): ?>
                                <option value="<?php echo $promotion['id']; ?>"><?php echo htmlspecialchars($promotion['nom_promotion']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Promotion</label>
                    </div>
                    <div class="input-box">
                        <input type="date" name="date_naissance">
                        <label>Date de naissance</label>
                    </div>
                    <div class="input-box">
                        <input type="text" name="adresse">
                        <label>Adresse</label>
                    </div>
                    <div class="input-box">
                        <input type="tel" name="telephone">
                        <label>Téléphone</label>
                    </div>
                </div>

                <!-- Champs spécifiques aux directeurs -->
                <div id="directeur-fields" style="display: none;">
                    <div class="input-box">
                        <input type="text" name="specialite">
                        <label>Spécialité</label>
                    </div>
                    <div class="input-box">
                        <input type="text" name="departement">
                        <label>Département</label>
                    </div>
                    <div class="input-box">
                        <input type="date" name="date_embauche">
                        <label>Date d'embauche</label>
                    </div>
                </div>

                <button type="submit" name="ajouter_utilisateur" class="btn">Ajouter Utilisateur</button>
            </form>
        </div>

        <div class="table-section">
            <h2>Liste des Utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Informations Spécifiques</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $utilisateur): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($utilisateur['id']); ?></td>
                            <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                            <td><?php echo htmlspecialchars($utilisateur['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                            <td><?php echo htmlspecialchars($utilisateur['role']); ?></td>
                            <td>
                                <?php if ($utilisateur['role'] === 'etudiant'): ?>
                                    Numéro: <?php echo htmlspecialchars($utilisateur['numero_etudiant']); ?><br>
                                    Promotion: <?php echo htmlspecialchars($utilisateur['nom_promotion'] ?? 'Non assignée'); ?>
                                <?php elseif ($utilisateur['role'] === 'directeur'): ?>
                                    Spécialité: <?php echo htmlspecialchars($utilisateur['specialite']); ?><br>
                                    Département: <?php echo htmlspecialchars($utilisateur['departement']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editUtilisateur(<?php echo htmlspecialchars(json_encode($utilisateur)); ?>)">Modifier</button>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $utilisateur['id']; ?>">
                                    <button type="submit" name="supprimer_utilisateur" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour modifier un utilisateur -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Modifier l'Utilisateur</h2>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editId">
                    <div class="input-box">
                        <input type="text" name="nom" id="editNom" required>
                        <label>Nom</label>
                    </div>
                    <div class="input-box">
                        <input type="text" name="prenom" id="editPrenom" required>
                        <label>Prénom</label>
                    </div>
                    <div class="input-box">
                        <input type="email" name="email" id="editEmail" required>
                        <label>Email</label>
                    </div>
                    <div class="input-box">
                        <select name="role" id="editRole" required onchange="toggleEditRoleFields()">
                            <option value="etudiant">Étudiant</option>
                            <option value="formateur">Formateur</option>
                            <option value="directeur">Directeur</option>
                        </select>
                        <label>Rôle</label>
                    </div>

                    <!-- Champs spécifiques aux étudiants -->
                    <div id="edit-etudiant-fields">
                        <div class="input-box">
                            <input type="text" name="numero_etudiant" id="editNumero">
                            <label>Numéro d'étudiant</label>
                        </div>
                        <div class="input-box">
                            <select name="promotion_id" id="editPromotion">
                                <option value="">Sélectionner une promotion</option>
                                <?php foreach ($promotions as $promotion): ?>
                                    <option value="<?php echo $promotion['id']; ?>"><?php echo htmlspecialchars($promotion['nom_promotion']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label>Promotion</label>
                        </div>
                        <div class="input-box">
                            <input type="date" name="date_naissance" id="editNaissance">
                            <label>Date de naissance</label>
                        </div>
                        <div class="input-box">
                            <input type="text" name="adresse" id="editAdresse">
                            <label>Adresse</label>
                        </div>
                        <div class="input-box">
                            <input type="tel" name="telephone" id="editTelephone">
                            <label>Téléphone</label>
                        </div>
                    </div>

                    <!-- Champs spécifiques aux directeurs -->
                    <div id="edit-directeur-fields" style="display: none;">
                        <div class="input-box">
                            <input type="text" name="specialite" id="editSpecialite">
                            <label>Spécialité</label>
                        </div>
                        <div class="input-box">
                            <input type="text" name="departement" id="editDepartement">
                            <label>Département</label>
                        </div>
                        <div class="input-box">
                            <input type="date" name="date_embauche" id="editEmbauche">
                            <label>Date d'embauche</label>
                        </div>
                    </div>

                    <button type="submit" name="modifier_utilisateur" class="btn">Modifier Utilisateur</button>
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
    max-width: 600px;
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
function toggleRoleFields() {
    const role = document.getElementById('role').value;
    document.getElementById('etudiant-fields').style.display = role === 'etudiant' ? 'block' : 'none';
    document.getElementById('directeur-fields').style.display = role === 'directeur' ? 'block' : 'none';
}

function toggleEditRoleFields() {
    const role = document.getElementById('editRole').value;
    document.getElementById('edit-etudiant-fields').style.display = role === 'etudiant' ? 'block' : 'none';
    document.getElementById('edit-directeur-fields').style.display = role === 'directeur' ? 'block' : 'none';
}

function editUtilisateur(utilisateur) {
    document.getElementById('editId').value = utilisateur.id;
    document.getElementById('editNom').value = utilisateur.nom;
    document.getElementById('editPrenom').value = utilisateur.prenom;
    document.getElementById('editEmail').value = utilisateur.email;
    document.getElementById('editRole').value = utilisateur.role;

    if (utilisateur.role === 'etudiant') {
        document.getElementById('editNumero').value = utilisateur.numero_etudiant || '';
        document.getElementById('editPromotion').value = utilisateur.promotion_id || '';
        document.getElementById('editNaissance').value = utilisateur.date_naissance || '';
        document.getElementById('editAdresse').value = utilisateur.adresse || '';
        document.getElementById('editTelephone').value = utilisateur.telephone || '';
        document.getElementById('edit-etudiant-fields').style.display = 'block';
        document.getElementById('edit-directeur-fields').style.display = 'none';
    } else if (utilisateur.role === 'directeur') {
        document.getElementById('editSpecialite').value = utilisateur.specialite || '';
        document.getElementById('editDepartement').value = utilisateur.departement || '';
        document.getElementById('editEmbauche').value = utilisateur.date_embauche || '';
        document.getElementById('edit-etudiant-fields').style.display = 'none';
        document.getElementById('edit-directeur-fields').style.display = 'block';
    } else {
        // For formateur and any other roles, hide specific fields
        document.getElementById('edit-etudiant-fields').style.display = 'none';
        document.getElementById('edit-directeur-fields').style.display = 'none';
    }

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
