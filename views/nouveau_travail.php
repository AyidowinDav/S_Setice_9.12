
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

// Récupérer les espaces du formateur
$stmtEspaces = $pdo->prepare("
    SELECT e.id, e.nom_espace
    FROM espaces_pedagogiques e
    JOIN formateurs f ON e.id_formateur = f.id
    WHERE f.id_utilisateur = ?
");
$stmtEspaces->execute([$id_formateur]);
$espaces = $stmtEspaces->fetchAll();

// Récupérer toutes les promotions
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY nom")->fetchAll();

// Récupérer tous les étudiants
$etudiants = $pdo->query("SELECT e.*, p.nom as promo_nom FROM etudiants e JOIN promotions p ON e.id_promotion = p.id ORDER BY e.nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S-SETICE | Nouveau Travail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>
    
    <main style="margin-left: 280px; padding: 50px;">
        <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Créer un Travail</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Assignez un travail individuel ou collectif à vos étudiants</p>

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
            <div style="background: white; padding: 60px; text-align: center; border-radius: 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                <p style="color: #64748b;">Vous n'avez aucun espace pédagogique assigné. Contactez le directeur.</p>
            </div>
        <?php else: ?>
        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 800px; border: 1px solid #edf2f7;">
            <form action="../actions/travail_action.php" method="POST" enctype="multipart/form-data" id="travailForm">
                <input type="hidden" name="action" value="creer_travail">

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Type de travail *</label>
                    <select name="type_travail" id="typeTravail" required style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <option value="">-- Sélectionner --</option>
                        <option value="COLLECTIF">Collectif (tous les étudiants d'une promotion)</option>
                        <option value="GROUPE">Groupe (spécifier le nombre d'étudiants par groupe)</option>
                        <option value="INDIVIDUEL">Individuel (un seul étudiant)</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Espace pédagogique *</label>
                    <select name="id_espace" required style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($espaces as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom_espace']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="promotionDiv" style="display:none; margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Promotion *</label>
                    <select name="id_promotion" id="idPromotion" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['annee']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <label style="display:block; margin-top:15px; margin-bottom:8px; font-weight:700; color:#1e293b;">Créer des groupes manuellement</label>
                    <input type="checkbox" name="creer_groupes_manuellement" id="creerGroupesManuellement" style="margin-right:10px;">
                    <div id="definitionGroupesDiv" style="display:none; margin-top:10px;">
                        <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Définition des groupes</label>
                        <textarea name="definition_groupes" id="definitionGroupes" rows="6" placeholder="Groupe 1: nom1 prenom1, nom2 prenom2&#10;Groupe 2: nom3 prenom3, nom4 prenom4" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;"></textarea>
                        <small style="color: #64748b; font-size: 12px;">Entrez les groupes au format "Groupe nom: étudiant1, étudiant2; Groupe nom2: étudiant3"</small>
                    </div>
                </div>

                <div id="groupeDiv" style="display:none; margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Promotion pour les groupes *</label>
                    <select name="id_promotion_groupe" id="idPromotionGroupe" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($promotions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['annee']) ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <div id="groupFormationDiv" style="display:none; margin-top:20px;">
                        <h4 style="color:#1e293b; margin-bottom:15px;">Formation des groupes</h4>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                            <!-- Liste des étudiants disponibles -->
                            <div>
                                <h5 style="color:#475569; margin-bottom:10px;">Étudiants disponibles</h5>
                                <div id="availableStudents" style="border:2px solid #e2e8f0; border-radius:10px; min-height:200px; padding:15px; background:#f8fafc;">
                                    <p style="color:#64748b; margin:0;">Sélectionnez une promotion pour voir les étudiants</p>
                                </div>
                            </div>

                            <!-- Groupes en cours de formation -->
                            <div>
                                <h5 style="color:#475569; margin-bottom:10px;">Groupes formés</h5>
                                <div id="formedGroups" style="border:2px solid #e2e8f0; border-radius:10px; min-height:200px; padding:15px; background:#f8fafc;">
                                    <p style="color:#64748b; margin:0;">Aucun groupe formé</p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:20px; text-align:center;">
                            <button type="button" id="createGroupBtn" style="background:#10b981; color:white; border:none; padding:12px 25px; border-radius:10px; cursor:pointer; font-weight:600; display:none;">
                                <i class="fas fa-plus"></i> Créer un nouveau groupe
                            </button>
                        </div>

                        <!-- Hidden inputs for groups -->
                        <input type="hidden" name="groupes_data" id="groupesData">
                    </div>
                </div>

                <div id="etudiantDiv" style="display:none; margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Étudiant *</label>
                    <select name="id_etudiant" id="idEtudiant" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($etudiants as $et): ?>
                            <option value="<?= $et['id'] ?>"><?= htmlspecialchars($et['nom']) ?> <?= htmlspecialchars($et['prenom']) ?> (<?= htmlspecialchars($et['promo_nom']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Titre du travail *</label>
                    <input type="text" name="titre" required minlength="5" placeholder="Ex: Projet PHP - Gestion de base de données" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Description / Consignes *</label>
                    <textarea name="description" rows="6" required minlength="10" placeholder="Décrivez le travail demandé, les consignes à respecter, les objectifs..." style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;"></textarea>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Date limite de rendu *</label>
                    <input type="datetime-local" name="date_limite" required style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">Fichiers joints (optionnel)</label>
                    <input type="file" name="fichiers[]" multiple accept="*" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                    <small style="color: #64748b; font-size: 12px;">Vous pouvez sélectionner plusieurs fichiers de n'importe quel format</small>
                </div>

                <div style="margin-bottom: 20px; display:flex; gap:12px; align-items:center;">
                    <label style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="autoriser_videos" value="1">
                        <span style="color:#475569; font-size:14px;">Autoriser les vidéos de démonstration</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="video_obligatoire" value="1">
                        <span style="color:#475569; font-size:14px;">Vidéo requise pour ce travail</span>
                    </label>
                </div>

                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <button type="submit" style="background:#1a4a8e; color:white; border:none; padding:15px; border-radius:12px; cursor:pointer; font-weight:700; width:100%; font-size: 16px;">
                    <i class="fas fa-paper-plane"></i> Créer et assigner le travail
                </button>
            </form>
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

    document.addEventListener('DOMContentLoaded', function() {
        const typeTravail = document.getElementById('typeTravail');
        const promotionDiv = document.getElementById('promotionDiv');
        const groupeDiv = document.getElementById('groupeDiv');
        const etudiantDiv = document.getElementById('etudiantDiv');
        const idPromotion = document.getElementById('idPromotion');
        const idPromotionGroupe = document.getElementById('idPromotionGroupe');
        const idEtudiant = document.getElementById('idEtudiant');
        const creerGroupesManuellement = document.getElementById('creerGroupesManuellement');
        const definitionGroupesDiv = document.getElementById('definitionGroupesDiv');
        const definitionGroupes = document.getElementById('definitionGroupes');
        const groupFormationDiv = document.getElementById('groupFormationDiv');
        const availableStudents = document.getElementById('availableStudents');
        const formedGroups = document.getElementById('formedGroups');
        const createGroupBtn = document.getElementById('createGroupBtn');
        const groupesData = document.getElementById('groupesData');

        let currentGroups = [];
        let draggedStudent = null;

        typeTravail.addEventListener('change', function() {
            if (this.value === 'COLLECTIF') {
                promotionDiv.style.display = 'block';
                groupeDiv.style.display = 'none';
                etudiantDiv.style.display = 'none';
                idPromotion.required = true;
                idPromotionGroupe.required = false;
                idEtudiant.required = false;
                idPromotion.value = '';
                definitionGroupes.required = false;
            } else if (this.value === 'GROUPE') {
                promotionDiv.style.display = 'none';
                groupeDiv.style.display = 'block';
                etudiantDiv.style.display = 'none';
                idPromotion.required = false;
                idPromotionGroupe.required = true;
                idEtudiant.required = false;
                idPromotion.value = '';
                definitionGroupes.required = false;
            } else if (this.value === 'INDIVIDUEL') {
                promotionDiv.style.display = 'none';
                groupeDiv.style.display = 'none';
                etudiantDiv.style.display = 'block';
                idPromotion.required = false;
                idPromotionGroupe.required = false;
                idEtudiant.required = true;
                idPromotion.value = '';
                definitionGroupes.required = false;
            } else {
                promotionDiv.style.display = 'none';
                groupeDiv.style.display = 'none';
                etudiantDiv.style.display = 'none';
                idPromotion.required = false;
                idPromotionGroupe.required = false;
                idEtudiant.required = false;
                definitionGroupes.required = false;
            }
        });

        creerGroupesManuellement.addEventListener('change', function() {
            if (this.checked) {
                definitionGroupesDiv.style.display = 'block';
                definitionGroupes.required = true;
            } else {
                definitionGroupesDiv.style.display = 'none';
                definitionGroupes.required = false;
                definitionGroupes.value = '';
            }
        });

        // Charger les étudiants quand une promotion est sélectionnée pour les groupes
        idPromotionGroupe.addEventListener('change', function() {
            const promotionId = this.value;
            if (promotionId) {
                loadStudentsForPromotion(promotionId);
                groupFormationDiv.style.display = 'block';
                createGroupBtn.style.display = 'inline-block';
            } else {
                groupFormationDiv.style.display = 'none';
                createGroupBtn.style.display = 'none';
                availableStudents.innerHTML = '<p style="color:#64748b; margin:0;">Sélectionnez une promotion pour voir les étudiants</p>';
                formedGroups.innerHTML = '<p style="color:#64748b; margin:0;">Aucun groupe formé</p>';
                currentGroups = [];
                updateGroupesData();
            }
        });

        // Fonction pour charger les étudiants d'une promotion
        function loadStudentsForPromotion(promotionId) {
            fetch(`../api/get_students_by_promotion.php?promotion_id=${promotionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAvailableStudents(data.students);
                    } else {
                        availableStudents.innerHTML = '<p style="color:#dc2626; margin:0;">Erreur lors du chargement des étudiants</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    availableStudents.innerHTML = '<p style="color:#dc2626; margin:0;">Erreur lors du chargement des étudiants</p>';
                });
        }

        // Afficher les étudiants disponibles
        function displayAvailableStudents(students) {
            availableStudents.innerHTML = '';
            students.forEach(student => {
                const studentDiv = document.createElement('div');
                studentDiv.className = 'student-item';
                studentDiv.draggable = true;
                studentDiv.dataset.studentId = student.id;
                studentDiv.innerHTML = '<div style="display:flex; align-items:center; gap:10px; padding:8px; background:white; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:5px; cursor:move;">' +
                    '<i class="fas fa-user" style="color:#64748b;"></i>' +
                    '<span>' + student.nom + ' ' + student.prenom + '</span>' +
                    '</div>';

                // Événements drag & drop
                studentDiv.addEventListener('dragstart', function(e) {
                    draggedStudent = student;
                    e.dataTransfer.effectAllowed = 'move';
                });

                studentDiv.addEventListener('dragend', function(e) {
                    draggedStudent = null;
                });

                availableStudents.appendChild(studentDiv);
            });
        }

        // Créer un nouveau groupe
        createGroupBtn.addEventListener('click', function() {
            const groupName = prompt('Nom du groupe:');
            if (groupName && groupName.trim()) {
                currentGroups.push({
                    name: groupName.trim(),
                    students: []
                });
                updateFormedGroupsDisplay();
            }
        });

        // Mettre à jour l'affichage des groupes formés
        function updateFormedGroupsDisplay() {
            formedGroups.innerHTML = '';

            if (currentGroups.length === 0) {
                formedGroups.innerHTML = '<p style="color:#64748b; margin:0;">Aucun groupe formé</p>';
                return;
            }

            currentGroups.forEach((group, groupIndex) => {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'group-container';
                groupDiv.innerHTML = '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">' +
                    '<h6 style="margin:0; color:#1e293b; font-weight:600;">' + group.name + '</h6>' +
                    '<button type="button" class="delete-group-btn" data-group-index="' + groupIndex + '" style="background:#dc2626; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:12px;">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                    '</div>' +
                    '<div class="group-students" data-group-index="' + groupIndex + '" style="min-height:60px; border:2px dashed #cbd5e1; border-radius:6px; padding:10px; background:#f8fafc;">' +
                        (group.students.length === 0 ? '<p style="color:#64748b; margin:0; font-size:14px;">Glissez les étudiants ici</p>' : '') +
                    '</div>';

                // Événements drag & drop pour les groupes
                const groupStudentsDiv = groupDiv.querySelector('.group-students');
                groupStudentsDiv.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    this.style.backgroundColor = '#e0f2fe';
                });

                groupStudentsDiv.addEventListener('dragleave', function(e) {
                    this.style.backgroundColor = '#f8fafc';
                });

                groupStudentsDiv.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '#f8fafc';

                    if (draggedStudent) {
                        addStudentToGroup(draggedStudent, groupIndex);
                    }
                });

                // Bouton supprimer groupe
                groupDiv.querySelector('.delete-group-btn').addEventListener('click', function() {
                    const index = parseInt(this.dataset.groupIndex);
                    // Remettre les étudiants dans la liste disponible
                    currentGroups[index].students.forEach(student => {
                        const studentDiv = document.querySelector(`[data-student-id="${student.id}"]`);
                        if (studentDiv) {
                            studentDiv.style.display = 'block';
                        }
                    });
                    currentGroups.splice(index, 1);
                    updateFormedGroupsDisplay();
                    updateGroupesData();
                });

                formedGroups.appendChild(groupDiv);
            });

            updateGroupesData();
        }

        // Ajouter un étudiant à un groupe
        function addStudentToGroup(student, groupIndex) {
            // Vérifier si l'étudiant n'est pas déjà dans un groupe
            const alreadyInGroup = currentGroups.some(group =>
                group.students.some(s => s.id === student.id)
            );

            if (alreadyInGroup) {
                alert('Cet étudiant est déjà dans un groupe.');
                return;
            }

            currentGroups[groupIndex].students.push(student);

            // Masquer l'étudiant de la liste disponible
            const studentDiv = document.querySelector('[data-student-id="' + student.id + '"]');
            if (studentDiv) {
                studentDiv.style.display = 'none';
            }

            updateFormedGroupsDisplay();
            updateGroupesData();
        }

        // Mettre à jour les données des groupes dans le champ caché
        function updateGroupesData() {
            groupesData.value = JSON.stringify(currentGroups);
        }

        // Validation date limite
        document.getElementById('travailForm').addEventListener('submit', function(e) {
            const dateLimite = new Date(document.querySelector('input[name="date_limite"]').value);
            const maintenant = new Date();

            if (dateLimite <= maintenant) {
                alert('La date limite doit être dans le futur !');
                e.preventDefault();
                return false;
            }

            // Validation pour les groupes
            if (typeTravail.value === 'GROUPE') {
                if (currentGroups.length === 0) {
                    alert('Vous devez former au moins un groupe.');
                    e.preventDefault();
                    return false;
                }

                const hasEmptyGroup = currentGroups.some(group => group.students.length === 0);
                if (hasEmptyGroup) {
                    alert('Tous les groupes doivent contenir au moins un étudiant.');
                    e.preventDefault();
                    return false;
                }

                const totalStudents = currentGroups.reduce((total, group) => total + group.students.length, 0);
                if (totalStudents === 0) {
                    alert('Au moins un étudiant doit être assigné à un groupe.');
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
    </script>
</body>
</html>