<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');
require_once('../includes/mailer_config.php');

/**
 * Parse la définition manuelle des groupes
 * Format attendu: "Groupe nom: étudiant1, étudiant2; Groupe nom2: étudiant3"
 */
function parseGroupesManuels($definition, $pdo, $id_promotion) {
    $groupes = [];

    // Séparer les groupes par ";"
    $groupes_str = explode(';', $definition);

    foreach ($groupes_str as $groupe_str) {
        $groupe_str = trim($groupe_str);
        if (empty($groupe_str)) continue;

        // Séparer le nom du groupe et les étudiants par ":"
        $parts = explode(':', $groupe_str, 2);
        if (count($parts) !== 2) continue;

        $nom_groupe = trim($parts[0]);
        $etudiants_str = trim($parts[1]);

        // Séparer les étudiants par ","
        $etudiants_noms = array_map('trim', explode(',', $etudiants_str));

        $etudiants_ids = [];
        foreach ($etudiants_noms as $nom_prenom) {
            if (empty($nom_prenom)) continue;

            // Séparer nom et prénom
            $parts_nom = explode(' ', $nom_prenom, 2);
            if (count($parts_nom) !== 2) continue;

            $nom = trim($parts_nom[0]);
            $prenom = trim($parts_nom[1]);

            // Trouver l'étudiant dans la promotion
            $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE nom = ? AND prenom = ? AND id_promotion = ?");
            $stmt->execute([$nom, $prenom, $id_promotion]);
            $etudiant = $stmt->fetch();

            if ($etudiant) {
                $etudiants_ids[] = $etudiant['id'];
            } else {
                return []; // Erreur si étudiant non trouvé
            }
        }

        if (!empty($etudiants_ids)) {
            $groupes[] = $etudiants_ids;
        }
    }

    return $groupes;
}



// Vérifier que c'est un formateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FORMATEUR') {
    setError('Accès non autorisé.');
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/nouveau_travail.php');
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setError('Token CSRF invalide.');
    header('Location: ../views/nouveau_travail.php');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'creer_travail') {
        // Récupération données
        $type_travail = strtoupper(trim($_POST['type_travail'] ?? ''));
        $id_espace = intval($_POST['id_espace'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_limite = $_POST['date_limite'] ?? '';
        $id_formateur = $_SESSION['user_id'];
        
        // ========== VALIDATIONS ==========
        
        // VALIDATION 1 : Type de travail valide
        if (!in_array($type_travail, ['COLLECTIF', 'GROUPE', 'INDIVIDUEL'])) {
            setError('Type de travail invalide.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }
        
        // VALIDATION 2 : Espace existe et appartient au formateur
        $checkEspace = $pdo->prepare("
            SELECT e.id
            FROM espaces_pedagogiques e
            JOIN formateurs f ON e.id_formateur = f.id
            WHERE e.id = ? AND f.id_utilisateur = ?
        ");
        $checkEspace->execute([$id_espace, $id_formateur]);

        $espaceAutorise = $checkEspace->fetch();

        // Si pas trouvé via id_formateur, vérifier via table d'assignation
        if (!$espaceAutorise) {
            $checkEspaceAssignation = $pdo->prepare("
                SELECT e.id
                FROM espaces_pedagogiques e
                JOIN formateur_espace fe ON e.id = fe.id_espace
                JOIN formateurs f ON fe.id_formateur = f.id
                WHERE e.id = ? AND f.id_utilisateur = ?
            ");
            $checkEspaceAssignation->execute([$id_espace, $id_formateur]);
            $espaceAutorise = $checkEspaceAssignation->fetch();
        }

        if (!$espaceAutorise) {
            setError('Espace pédagogique invalide ou non autorisé.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }
        
        // VALIDATION 3 : Titre minimum 5 caractères
        if (strlen($titre) < 5) {
            setError('Le titre doit contenir au moins 5 caractères.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }
        
        // VALIDATION 4 : Description minimum 10 caractères
        if (strlen($description) < 10) {
            setError('La description doit contenir au moins 10 caractères.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }
        
        // VALIDATION 5 : Date limite dans le futur (CAS DÉFAVORABLE)
        $dateLimiteObj = new DateTime($date_limite);
        $maintenant = new DateTime();
        
        if ($dateLimiteObj <= $maintenant) {
            setError('La date limite doit être dans le futur.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }
        
        // VALIDATION 6 : Assignation selon le type
        $id_promotion = null;
        $id_etudiant = null;
        $nb_assignations = 0;
        $nb_groupes = 0;
        $groupes = [];

        if ($type_travail === 'GROUPE') {
            $id_promotion_groupe = intval($_POST['id_promotion_groupe'] ?? 0);
            $groupes_data = json_decode($_POST['groupes_data'] ?? '[]', true);

            // Vérifier que la promotion existe
            $checkPromo = $pdo->prepare("SELECT id FROM promotions WHERE id = ?");
            $checkPromo->execute([$id_promotion_groupe]);

            if (!$checkPromo->fetch()) {
                setError('Promotion invalide.');
                header('Location: ../views/nouveau_travail.php');
                exit;
            }

            // Vérifier que les groupes sont valides
            if (empty($groupes_data) || !is_array($groupes_data)) {
                setError('Aucun groupe n\'a été formé.');
                header('Location: ../views/nouveau_travail.php');
                exit;
            }

            $nb_groupes = count($groupes_data);
            $nb_assignations = 0;

            // Vérifier chaque groupe
            foreach ($groupes_data as $groupe) {
                if (!isset($groupe['name']) || !isset($groupe['students']) || !is_array($groupe['students'])) {
                    setError('Structure de groupe invalide.');
                    header('Location: ../views/nouveau_travail.php');
                    exit;
                }

                if (count($groupe['students']) < 2) {
                    setError('Chaque groupe doit contenir au moins 2 étudiants.');
                    header('Location: ../views/nouveau_travail.php');
                    exit;
                }

                $nb_assignations += count($groupe['students']);

                // Vérifier que tous les étudiants existent et appartiennent à la promotion
                foreach ($groupe['students'] as $student_id) {
                    $checkStudent = $pdo->prepare("SELECT id FROM etudiants WHERE id = ? AND id_promotion = ?");
                    $checkStudent->execute([$student_id, $id_promotion_groupe]);
                    if (!$checkStudent->fetch()) {
                        setError('Étudiant invalide ou n\'appartenant pas à la promotion sélectionnée.');
                        header('Location: ../views/nouveau_travail.php');
                        exit;
                    }
                }
            }

            $id_promotion = $id_promotion_groupe;

        } elseif ($type_travail === 'COLLECTIF') {
            $id_promotion = intval($_POST['id_promotion'] ?? 0);
            $creer_groupes_manuellement = isset($_POST['creer_groupes_manuellement']) && $_POST['creer_groupes_manuellement'] === 'on';
            $groupes_manuels = [];

            // Vérifier que la promotion existe
            $checkPromo = $pdo->prepare("SELECT id FROM promotions WHERE id = ?");
            $checkPromo->execute([$id_promotion]);

            if (!$checkPromo->fetch()) {
                setError('Promotion invalide.');
                header('Location: ../views/nouveau_travail.php');
                exit;
            }

            if ($creer_groupes_manuellement) {
                $definition_groupes = trim($_POST['definition_groupes'] ?? '');

                if (empty($definition_groupes)) {
                    setError('La définition des groupes est requise.');
                    header('Location: ../views/nouveau_travail.php');
                    exit;
                }

                $groupes_manuels = parseGroupesManuels($definition_groupes, $pdo, $id_promotion);

                if (empty($groupes_manuels)) {
                    setError('Erreur dans la définition des groupes. Vérifiez le format et que tous les étudiants appartiennent à la promotion.');
                    header('Location: ../views/nouveau_travail.php');
                    exit;
                }

                $nb_groupes = count($groupes_manuels);
                $nb_assignations = array_sum(array_map('count', $groupes_manuels));
            } else {
                // Compter les étudiants dans la promotion
                $countEtu = $pdo->prepare("SELECT COUNT(*) as nb FROM etudiants WHERE id_promotion = ?");
                $countEtu->execute([$id_promotion]);
                $result = $countEtu->fetch();
                $nb_assignations = $result['nb'];
                $nb_groupes = 0;
            }

        } else { // INDIVIDUEL
            $id_etudiant = intval($_POST['id_etudiant'] ?? 0);

            // Vérifier que l'étudiant existe
            $checkEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id = ?");
            $checkEtu->execute([$id_etudiant]);

            if (!$checkEtu->fetch()) {
                setError('Étudiant invalide.');
                header('Location: ../views/nouveau_travail.php');
                exit;
            }

            $nb_assignations = 1;
        }
        
        // S'assurer que la table de fichiers existe (hors transaction)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS fichiers_travaux (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_travail INT NOT NULL,
                nom_fichier VARCHAR(255) NOT NULL,
                chemin_fichier VARCHAR(500) NOT NULL,
                taille_fichier INT NOT NULL,
                type_fichier VARCHAR(100),
                date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_travail) REFERENCES travaux(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // ignore
        }

        // S'assurer que les colonnes pour la gestion vidéo existent
        try {
            $pdo->exec("ALTER TABLE travaux ADD COLUMN IF NOT EXISTS accept_videos TINYINT(1) DEFAULT 0");
        } catch (Exception $e) { }
        try {
            $pdo->exec("ALTER TABLE travaux ADD COLUMN IF NOT EXISTS video_obligatoire TINYINT(1) DEFAULT 0");
        } catch (Exception $e) { }

        // ========== INSERTION DU TRAVAIL (CAS FAVORABLE) ==========
        $pdo->beginTransaction();

        // Récupérer l'ID du formateur depuis la table formateurs
        $getFormateurId = $pdo->prepare("SELECT id FROM formateurs WHERE id_utilisateur = ?");
        $getFormateurId->execute([$id_formateur]);
        $formateur = $getFormateurId->fetch();

        if (!$formateur) {
            setError('Formateur non trouvé.');
            header('Location: ../views/nouveau_travail.php');
            exit;
        }

        $id_formateur_real = $formateur['id'];

        $autoriser_videos = isset($_POST['autoriser_videos']) ? 1 : 0;
        $video_obligatoire = isset($_POST['video_obligatoire']) ? 1 : 0;

       $insertTravail = $pdo->prepare("
    INSERT INTO travaux (
        titre, description, date_limite, type_travail,
        id_formateur, id_espace, id_promotion, id_etudiant,
        accept_videos, video_obligatoire
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");


        $insertTravail->execute([
            $titre,
            $description,
            $date_limite,
            $type_travail,
            $id_formateur_real,
            $id_espace,
            $id_promotion,
            $id_etudiant,
            $autoriser_videos,
            $video_obligatoire
        ]);
        
        $id_travail = $pdo->lastInsertId();
        
        // ====== Gestion des fichiers joints (s'il y en a) ======
        if (!empty($_FILES['fichiers']) && is_array($_FILES['fichiers']['name'])) {
            // Créer le dossier de stockage
            $uploadDir = __DIR__ . '/../uploads/travaux/' . $id_travail;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $insertFile = $pdo->prepare("INSERT INTO fichiers_travaux (id_travail, nom_fichier, chemin_fichier, taille_fichier, type_fichier) VALUES (?, ?, ?, ?, ?)");

            for ($i = 0; $i < count($_FILES['fichiers']['name']); $i++) {
                $error = $_FILES['fichiers']['error'][$i];
                if ($error !== UPLOAD_ERR_OK) continue;

                $tmpName = $_FILES['fichiers']['tmp_name'][$i];
                $originalName = basename($_FILES['fichiers']['name'][$i]);
                $mimeType = $_FILES['fichiers']['type'][$i] ?? '';
                $size = intval($_FILES['fichiers']['size'][$i] ?? 0);

                // Générer nom unique
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $targetName = $safeBase . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
                $targetPath = $uploadDir . '/' . $targetName;

                if (@move_uploaded_file($tmpName, $targetPath)) {
                    $publicPath = 'uploads/travaux/' . $id_travail . '/' . $targetName;
                    try {
                        $insertFile->execute([$id_travail, $originalName, $publicPath, $size, $mimeType]);
                    } catch (Exception $e) {
                        // ignore insertion errors for files
                    }
                }
            }
        }
        
        // ========== CRÉATION DES GROUPES ET ASSIGNATIONS ==========
        if ($type_travail === 'GROUPE') {
            // Récupérer les informations des étudiants pour les emails
            $getEtudiants = $pdo->prepare("SELECT id, nom, prenom, email FROM etudiants WHERE id_promotion = ?");
            $getEtudiants->execute([$id_promotion]);
            $etudiants = $getEtudiants->fetchAll();
            $etudiants_map = array_column($etudiants, null, 'id');

            // Créer les groupes selon les données du formulaire
            $groupes_crees = [];
            foreach ($groupes_data as $groupe_data) {
                $nom_groupe = $groupe_data['name'];
                $insertGroupe = $pdo->prepare("INSERT INTO groupes (id_travail, nom_groupe) VALUES (?, ?)");
                $insertGroupe->execute([$id_travail, $nom_groupe]);
                $id_groupe = $pdo->lastInsertId();

                $groupe_etudiants = [];
                foreach ($groupe_data['students'] as $student_id) {
                    $insertGroupeEtudiant = $pdo->prepare("INSERT INTO groupe_etudiants (id_groupe, id_etudiant) VALUES (?, ?)");
                    $insertGroupeEtudiant->execute([$id_groupe, $student_id]);

                    if (isset($etudiants_map[$student_id])) {
                        $groupe_etudiants[] = $etudiants_map[$student_id];
                    }
                }

                $groupes_crees[] = ['nom' => $nom_groupe, 'etudiants' => $groupe_etudiants];
            }

            // Créer assignations pour tous les étudiants
            $insertAssignation = $pdo->prepare("
                INSERT INTO assignations_travaux (id_travail, id_etudiant, statut, date_assignation)
                VALUES (?, ?, 'EN_ATTENTE', NOW())
            ");

            foreach ($etudiants as $etu) {
                $insertAssignation->execute([$id_travail, $etu['id']]);
            }

            // Envoyer les emails de notification
            $espaceInfo = $pdo->prepare("SELECT nom_espace FROM espaces_pedagogiques WHERE id = ?");
            $espaceInfo->execute([$id_espace]);
            $espace = $espaceInfo->fetch();

            $formateurInfo = $pdo->prepare("SELECT nom FROM formateurs WHERE id_utilisateur = ?");
            $formateurInfo->execute([$id_formateur]);
            $formateur = $formateurInfo->fetch();

            foreach ($groupes_crees as $groupe) {
                foreach ($groupe['etudiants'] as $etudiant) {
                    sendGroupNotificationEmail(
                        $etudiant['email'],
                        $etudiant['nom'] . ' ' . $etudiant['prenom'],
                        $groupe['nom'],
                        $titre,
                        $description,
                        $espace['nom_espace'],
                        $formateur['nom']
                    );
                }
            }
        } elseif ($type_travail === 'COLLECTIF') {
            // Récupérer les informations des étudiants pour les emails
            $getEtudiants = $pdo->prepare("SELECT id, nom, prenom, email FROM etudiants WHERE id_promotion = ?");
            $getEtudiants->execute([$id_promotion]);
            $etudiants = $getEtudiants->fetchAll();

            if ($creer_groupes_manuellement) {
                // Créer les groupes manuellement
                $groupes_crees = [];
                foreach ($groupes_manuels as $index => $groupe_etudiants) {
                    $nom_groupe = "Groupe " . ($index + 1);
                    $insertGroupe = $pdo->prepare("INSERT INTO groupes (id_travail, nom_groupe) VALUES (?, ?)");
                    $insertGroupe->execute([$id_travail, $nom_groupe]);
                    $id_groupe = $pdo->lastInsertId();

                    $groupe_etudiants_info = [];
                    foreach ($groupe_etudiants as $student_id) {
                        $insertGroupeEtudiant = $pdo->prepare("INSERT INTO groupe_etudiants (id_groupe, id_etudiant) VALUES (?, ?)");
                        $insertGroupeEtudiant->execute([$id_groupe, $student_id]);

                        // Trouver les infos de l'étudiant pour l'email
                        foreach ($etudiants as $etu) {
                            if ($etu['id'] == $student_id) {
                                $groupe_etudiants_info[] = $etu;
                                break;
                            }
                        }
                    }

                    $groupes_crees[] = ['nom' => $nom_groupe, 'etudiants' => $groupe_etudiants_info];
                }

                // Envoyer les emails de notification pour les groupes
                $espaceInfo = $pdo->prepare("SELECT nom_espace FROM espaces_pedagogiques WHERE id = ?");
                $espaceInfo->execute([$id_espace]);
                $espace = $espaceInfo->fetch();

                $formateurInfo = $pdo->prepare("SELECT nom FROM formateurs WHERE id_utilisateur = ?");
                $formateurInfo->execute([$id_formateur]);
                $formateur = $formateurInfo->fetch();

                foreach ($groupes_crees as $groupe) {
                    foreach ($groupe['etudiants'] as $etudiant) {
                        sendGroupNotificationEmail(
                            $etudiant['email'],
                            $etudiant['nom'] . ' ' . $etudiant['prenom'],
                            $groupe['nom'],
                            $titre,
                            $description,
                            $espace['nom_espace'],
                            $formateur['nom']
                        );
                    }
                }
            } else {
                // Pas de groupes, assigner tous les étudiants individuellement et envoyer emails
                // Récupérer les informations de l'espace et du formateur pour les emails
                $espaceInfo = $pdo->prepare("SELECT nom_espace FROM espaces_pedagogiques WHERE id = ?");
                $espaceInfo->execute([$id_espace]);
                $espace = $espaceInfo->fetch();

                $formateurInfo = $pdo->prepare("SELECT nom FROM formateurs WHERE id_utilisateur = ?");
                $formateurInfo->execute([$id_formateur]);
                $formateur = $formateurInfo->fetch();

                // Envoyer les emails individuels
                foreach ($etudiants as $etudiant) {
                    sendIndividualNotificationEmail(
                        $etudiant['email'],
                        $etudiant['nom'] . ' ' . $etudiant['prenom'],
                        $titre,
                        $description,
                        $espace['nom_espace'],
                        $formateur['nom']
                    );
                }
            }

            // Créer assignations pour tous les étudiants de la promotion
            $insertAssignation = $pdo->prepare("
                INSERT INTO assignations_travaux (id_travail, id_etudiant, statut, date_assignation)
                VALUES (?, ?, 'EN_ATTENTE', NOW())
            ");

            foreach ($etudiants as $etu) {
                $insertAssignation->execute([$id_travail, $etu['id']]);
            }
        } else { // INDIVIDUEL
            // Créer assignation pour l'étudiant unique
            $insertAssignation = $pdo->prepare("
                INSERT INTO assignations_travaux (id_travail, id_etudiant, statut, date_assignation)
                VALUES (?, ?, 'EN_ATTENTE', NOW())
            ");
            $insertAssignation->execute([$id_travail, $id_etudiant]);

            // Envoyer l'email de notification individuelle
            $espaceInfo = $pdo->prepare("SELECT nom_espace FROM espaces_pedagogiques WHERE id = ?");
            $espaceInfo->execute([$id_espace]);
            $espace = $espaceInfo->fetch();

            $formateurInfo = $pdo->prepare("SELECT nom FROM formateurs WHERE id_utilisateur = ?");
            $formateurInfo->execute([$id_formateur]);
            $formateur = $formateurInfo->fetch();

            $etudiantInfo = $pdo->prepare("SELECT nom, prenom, email FROM etudiants WHERE id = ?");
            $etudiantInfo->execute([$id_etudiant]);
            $etudiant = $etudiantInfo->fetch();

            if ($etudiant) {
                sendIndividualNotificationEmail(
                    $etudiant['email'],
                    $etudiant['nom'] . ' ' . $etudiant['prenom'],
                    $titre,
                    $description,
                    $espace['nom_espace'],
                    $formateur['nom']
                );
            }
        }
        
        $pdo->commit();

        // Message de succès
        $message = "Travail créé avec succès ! ";
        if ($type_travail === 'COLLECTIF') {
            if (!empty($groupes_manuels)) {
                $message .= "$nb_groupes groupes ont été créés avec $nb_assignations étudiants.";
            } else {
                $message .= "$nb_assignations étudiants ont été notifiés.";
            }
        } elseif ($type_travail === 'GROUPE') {
            $message .= "$nb_groupes groupes ont été créés avec $nb_assignations étudiants.";
        } else {
            $message .= "L'étudiant a été notifié.";
        }

        setSuccess($message);
        header('Location: ../views/mes_travaux_formateur.php');
        exit;
    }
    
    else {
        setError('Action non reconnue.');
        header('Location: ../views/nouveau_travail.php');
        exit;
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur création travail : ' . $e->getMessage());
    setError('Erreur : ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/nouveau_travail.php'));
    exit;
}
?>