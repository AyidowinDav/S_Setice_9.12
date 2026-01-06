<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

/**
 * SSS-14 : LIVRAISON DE PRODUCTION (Rendu)
 * SSS-15 : ÉVALUATION DU TRAVAIL (Note + Commentaire)
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // Debug output for local development: show session and tokens to diagnose mismatch
    $posted = $_POST['csrf_token'] ?? '';
    $sessionToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '(none)';
    error_log("CSRF validation failed. SessionID=" . session_id() . " PostedToken=" . substr($posted,0,8) . "... SessionToken=" . (is_string($sessionToken) ? substr($sessionToken,0,8) . '...' : $sessionToken));

    // Collect headers and cookies
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $cookies = $_COOKIE;

    // Render a simple debug page (temporary)
    http_response_code(400);
    echo "<html><head><meta charset='utf-8'><title>CSRF debug</title></head><body style='font-family:Inter,Arial,sans-serif;padding:20px;'>";
    echo "<h2>CSRF validation failed (debug)</h2>";
    echo "<p><strong>Session ID:</strong> " . htmlspecialchars(session_id()) . "</p>";
    echo "<p><strong>Session token:</strong> " . htmlspecialchars($sessionToken) . "</p>";
    echo "<p><strong>Posted token:</strong> " . htmlspecialchars($posted) . "</p>";
    echo "<h3>Cookies</h3><pre>" . htmlspecialchars(json_encode($cookies, JSON_PRETTY_PRINT)) . "</pre>";
    echo "<h3>Headers</h3><pre>" . htmlspecialchars(json_encode($headers, JSON_PRETTY_PRINT)) . "</pre>";
    echo "<p>If the session token and posted token differ, ensure cookies (PHPSESSID) are sent and identical between page load and form submit.</p>";
    echo "</body></html>";
    exit;
}

$action = $_POST['action'] ?? '';

try {
    // Ensure table for rendu files exists (outside transaction)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS fichiers_rendus (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_rendu INT NOT NULL,
            nom_fichier VARCHAR(255) NOT NULL,
            chemin_fichier VARCHAR(500) NOT NULL,
            taille_fichier INT NOT NULL,
            type_fichier VARCHAR(100),
            date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_rendu) REFERENCES rendus_travaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Exception $e) {
        // ignore
    }

    // Detect colonne réelle dans `fichiers_rendus` (compatibilité schéma)
    $colMap = [
        'id_rendu' => 'id_rendu',
        'nom_fichier' => 'nom_fichier',
        'chemin_fichier' => 'chemin_fichier',
        'taille_fichier' => 'taille_fichier',
        'type_fichier' => 'type_fichier'
    ];
    try {
        $colStmt = $pdo->query("SHOW COLUMNS FROM fichiers_rendus");
        $cols = array_column($colStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        if (in_array('rendu_id', $cols)) {
            // ancien schéma
            $colMap = [
                'id_rendu' => 'rendu_id',
                'nom_fichier' => 'nom_original',
                'chemin_fichier' => 'fichier_url',
                'taille_fichier' => 'taille',
                'type_fichier' => 'type'
            ];
        }
    } catch (Exception $e) {
        // ignore, keep defaults
    }

    $pdo->beginTransaction();
    
    // ==================== SSS-14 : SOUMETTRE UN RENDU ====================
    if ($action === 'soumettre_rendu') {
        // Vérifier que c'est un étudiant
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ETUDIANT') {
            throw new Exception('Accès non autorisé.');
        }
        
        $id_assignation = intval($_POST['id_assignation'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        $id_user = $_SESSION['user_id'];
        
        // === VALIDATIONS ===
        
        // VALIDATION 1 : Assignation existe et appartient à cet étudiant
        $checkAssignation = $pdo->prepare(
            "SELECT at.*, t.date_limite, t.titre, COALESCE(t.accept_videos,0) AS accept_videos, COALESCE(t.video_obligatoire,0) AS video_obligatoire
             FROM assignations_travaux at
             JOIN travaux t ON at.id_travail = t.id
             JOIN etudiants et ON at.id_etudiant = et.id
             WHERE at.id = ? AND et.id_utilisateur = ? AND at.statut = 'EN_ATTENTE'"
        );
        $checkAssignation->execute([$id_assignation, $id_user]);
        $assignation = $checkAssignation->fetch();
        
        if (!$assignation) {
            throw new Exception('Travail introuvable, non autorisé ou déjà rendu.');
        }
        
        // VALIDATION 2 : Contenu minimum 50 caractères
        if (strlen($contenu) < 50) {
            throw new Exception('Le contenu doit contenir au moins 50 caractères.');
        }
        
        // VALIDATION 3 : Contenu maximum 50000 caractères
        if (strlen($contenu) > 50000) {
            throw new Exception('Le contenu ne peut pas dépasser 50 000 caractères.');
        }
        
        // VALIDATION 4 : Pas de doublon de rendu
        $checkRendu = $pdo->prepare("SELECT id FROM rendus_travaux WHERE id_assignation = ?");
        $checkRendu->execute([$id_assignation]);
        
        if ($checkRendu->fetch()) {
            throw new Exception('Vous avez déjà rendu ce travail.');
        }
        
        // VALIDATION 5 : Calculer si en retard (CAS DÉFAVORABLE SSS-14)
        $maintenant = new DateTime();
        $dateLimite = new DateTime($assignation['date_limite']);
        $estEnRetard = $maintenant > $dateLimite;
        
        // === INSERTION DU RENDU (CAS FAVORABLE SSS-14) ===
        $insertRendu = $pdo->prepare("
            INSERT INTO rendus_travaux (id_assignation, contenu, est_en_retard, date_rendu)
            VALUES (?, ?, ?, NOW())
        ");
        $insertRendu->execute([$id_assignation, $contenu, $estEnRetard ? 1 : 0]);
        $id_rendu = $pdo->lastInsertId();

        // Handle uploaded files (optional) and record in fichiers_rendus
        $uploadDir = __DIR__ . '/../uploads/rendus/' . $id_rendu;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $insertFile = null;
        try {
            $insertFile = $pdo->prepare("INSERT INTO fichiers_rendus ({id_rendu}, {nom_fichier}, {chemin_fichier}, {taille_fichier}, {type_fichier}) VALUES (?, ?, ?, ?, ?)");
            // replace placeholders with actual column names
            $insertFileSql = strtr($insertFile->queryString ?? '', [
                '{id_rendu}' => $colMap['id_rendu'],
                '{nom_fichier}' => $colMap['nom_fichier'],
                '{chemin_fichier}' => $colMap['chemin_fichier'],
                '{taille_fichier}' => $colMap['taille_fichier'],
                '{type_fichier}' => $colMap['type_fichier']
            ]);
            $insertFile = $pdo->prepare($insertFileSql);
        } catch (Exception $e) {
            $insertFile = null;
        }

        $collectedTypes = [];

        // Process native file uploads (from form) - keep existing behavior
        $processInput = function($inputName) use (&$collectedTypes, $uploadDir, $id_rendu, $insertFile) {
            if (empty($_FILES[$inputName]) || !is_array($_FILES[$inputName]['name'])) return;
            for ($i = 0; $i < count($_FILES[$inputName]['name']); $i++) {
                $error = $_FILES[$inputName]['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($error !== UPLOAD_ERR_OK) continue;

                $tmpName = $_FILES[$inputName]['tmp_name'][$i];
                $originalName = basename($_FILES[$inputName]['name'][$i]);
                $mimeType = $_FILES[$inputName]['type'][$i] ?? '';
                $size = intval($_FILES[$inputName]['size'][$i] ?? 0);

                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $targetName = $safeBase . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
                $targetPath = $uploadDir . '/' . $targetName;

                if (@move_uploaded_file($tmpName, $targetPath)) {
                    $publicPath = 'uploads/rendus/' . $id_rendu . '/' . $targetName;
                    if ($insertFile) {
                        try { $insertFile->execute([$id_rendu, $originalName, $publicPath, $size, $mimeType]); } catch (Exception $e) { }
                    }
                    $collectedTypes[] = $mimeType;
                }
            }
        };

        // Process native inputs
        $processInput('fichiers');
        $processInput('videos');

        // Also process pre-uploaded temporary files (uploaded via chunk endpoint)
        if (!empty($_POST['tmp_files']) && is_array($_POST['tmp_files'])) {
            $tmpFiles = $_POST['tmp_files'];
            $origNames = $_POST['tmp_originals'] ?? [];
            for ($i = 0; $i < count($tmpFiles); $i++) {
                $webTmp = $tmpFiles[$i];
                // Sanitize and map to filesystem path
                $basename = basename($webTmp);
                $fsPath = __DIR__ . '/../uploads/tmp/' . $basename;
                if (!file_exists($fsPath)) continue;
                $originalName = $origNames[$i] ?? $basename;
                $mimeType = mime_content_type($fsPath) ?: '';
                $size = filesize($fsPath);

                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $targetName = $safeBase . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
                $targetPath = $uploadDir . '/' . $targetName;

                if (@rename($fsPath, $targetPath)) {
                    $publicPath = 'uploads/rendus/' . $id_rendu . '/' . $targetName;
                    if ($insertFile) {
                        try { $insertFile->execute([$id_rendu, $originalName, $publicPath, $size, $mimeType]); } catch (Exception $e) { }
                    }
                    $collectedTypes[] = $mimeType;
                }
            }
        }

        // Si la vidéo est obligatoire, vérifier qu'au moins un fichier vidéo a été fourni
        if (!empty($assignation['video_obligatoire'])) {
            $hasVideo = false;
            // vérifier parmi les fichiers uploadés (types collectés lors du traitement)
            if (!empty($collectedTypes) && is_array($collectedTypes)) {
                foreach ($collectedTypes as $t) {
                    if (stripos($t, 'video/') === 0) { $hasVideo = true; break; }
                }
            }
            // fallback : vérifier dans la table si files were stored
            if (!$hasVideo) {
                try {
                    $colType = $colMap['type_fichier'];
                    $colIdRendu = $colMap['id_rendu'];
                    $stmtV = $pdo->prepare("SELECT {$colType} FROM fichiers_rendus WHERE {$colIdRendu} = ?");
                    $stmtV->execute([$id_rendu]);
                    $types = $stmtV->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($types as $t) { if (stripos($t, 'video/') === 0) { $hasVideo = true; break; } }
                } catch (Exception $e) { }
            }
            if (!$hasVideo) {
                throw new Exception('Ce travail nécessite l\'ajout d\'au moins une vidéo de démonstration.');
            }
        }
        
        // === MISE À JOUR DU STATUT ===
        $nouveauStatut = $estEnRetard ? 'EN_RETARD' : 'RENDU';
        $updateStatut = $pdo->prepare("UPDATE assignations_travaux SET statut = ? WHERE id = ?");
        $updateStatut->execute([$nouveauStatut, $id_assignation]);
        
        $pdo->commit();
        
        // Messages différenciés
        if ($estEnRetard) {
            setWarning("Travail soumis mais MARQUÉ EN RETARD car la date limite est dépassée.");
        } else {
            setSuccess("Travail soumis avec succès ! Votre formateur sera notifié.");
        }
        
        header('Location: ../views/mes_travaux.php');
        exit;
    }
    
    // ==================== SSS-15 : ÉVALUER UN RENDU ====================
    elseif ($action === 'evaluer_rendu') {
        // Vérifier que c'est un formateur
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FORMATEUR') {
            throw new Exception('Accès non autorisé.');
        }
        
        $id_rendu = intval($_POST['id_rendu'] ?? 0);
        $note = floatval($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $id_formateur = $_SESSION['user_id'];
        
        // === VALIDATIONS ===
        
        // VALIDATION 1 : Rendu existe et appartient à un travail du formateur
        $checkRendu = $pdo->prepare("
            SELECT rt.id, t.id_formateur, t.titre, e.nom, e.prenom
            FROM rendus_travaux rt
            JOIN assignations_travaux at ON rt.id_assignation = at.id
            JOIN travaux t ON at.id_travail = t.id
            JOIN etudiants e ON at.id_etudiant = e.id
            WHERE rt.id = ? AND t.id_formateur = ?
        ");
        $checkRendu->execute([$id_rendu, $id_formateur]);
        $rendu = $checkRendu->fetch();
        
        if (!$rendu) {
            throw new Exception('Rendu introuvable ou non autorisé.');
        }
        
        // VALIDATION 2 : Note dans le barème (CAS DÉFAVORABLE SSS-15)
        if ($note < 0 || $note > 20) {
            throw new Exception('La note doit être entre 0 et 20.');
        }
        
        // VALIDATION 3 : Commentaire (si présent, minimum 5 caractères)
        if (!empty($commentaire) && strlen($commentaire) < 5) {
            throw new Exception('Le commentaire doit contenir au moins 5 caractères.');
        }
        
        // VALIDATION 4 : Pas de double évaluation
        $checkEval = $pdo->prepare("SELECT id FROM evaluations WHERE id_rendu = ?");
        $checkEval->execute([$id_rendu]);
        
        if ($checkEval->fetch()) {
            throw new Exception('Ce travail a déjà été évalué. Vous ne pouvez pas modifier la note.');
        }
        
        // === INSERTION DE L'ÉVALUATION (CAS FAVORABLE SSS-15) ===
        $insertEval = $pdo->prepare("
            INSERT INTO evaluations (id_rendu, note, commentaire, id_formateur, date_evaluation)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertEval->execute([
            $id_rendu, 
            $note, 
            empty($commentaire) ? null : $commentaire, 
            $id_formateur
        ]);
        
        // === MISE À JOUR DU STATUT ===
        $updateStatut = $pdo->prepare("
            UPDATE assignations_travaux at
            JOIN rendus_travaux rt ON at.id = rt.id_assignation
            SET at.statut = 'EVALUE'
            WHERE rt.id = ?
        ");
        $updateStatut->execute([$id_rendu]);
        
        $pdo->commit();
        
        setSuccess("Évaluation enregistrée avec succès ! Note attribuée : $note/20");
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/evaluer_travaux.php'));
        exit;
    }
    
    else {
        throw new Exception('Action non reconnue.');
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur rendu/évaluation : ' . $e->getMessage());
    setError('Erreur : ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}
?>