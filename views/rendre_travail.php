<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

// Sécurité
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ETUDIANT') {
    header('Location: ../index.php');
    exit;
}

$id_assignation = intval($_GET['id'] ?? 0);

if ($id_assignation === 0) {
    setError('Assignation invalide.');
    header('Location: mes_travaux.php');
    exit;
}

// Récupérer les détails du travail et vérifier que c'est bien pour cet étudiant
$id_user = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT 
        at.*,
        t.titre,
        t.description,
        t.date_limite,
        e.nom_espace,
        COALESCE(f.nom, u.nom) as formateur_nom,
        et.id as etudiant_id,
        t.id as id_travail
    FROM assignations_travaux at
    JOIN travaux t ON at.id_travail = t.id
    JOIN espaces_pedagogiques e ON t.id_espace = e.id
    LEFT JOIN formateurs f ON t.id_formateur = f.id
    LEFT JOIN utilisateur u ON t.id_formateur = u.id
    JOIN etudiants et ON at.id_etudiant = et.id
    WHERE at.id = ? AND et.id_utilisateur = ?
");
$stmt->execute([$id_assignation, $id_user]);
$assignation = $stmt->fetch();

if (!$assignation) {
    setError('Travail introuvable ou non autorisé.');
    header('Location: mes_travaux.php');
    exit;
}

// Vérifier si déjà rendu
$checkRendu = $pdo->prepare("SELECT id FROM rendus_travaux WHERE id_assignation = ?");
$checkRendu->execute([$id_assignation]);
if ($checkRendu->fetch()) {
    setError('Vous avez déjà rendu ce travail.');
    header('Location: mes_travaux.php');
    exit;
}

// Récupérer les fichiers liés au travail (si la table existe)
$files = [];
try {
    $fileStmt = $pdo->prepare("SELECT * FROM fichiers_travaux WHERE id_travail = ?");
    $fileStmt->execute([$assignation['id_travail']]);
    $files = $fileStmt->fetchAll();
} catch (Exception $e) {
    // table inexistante ou autre erreur -> ignorer
    $files = [];
}

// Vérifier si la date limite est dépassée
$maintenant = new DateTime();
$dateLimite = new DateTime($assignation['date_limite']);
$estDepasse = $maintenant > $dateLimite;

// Récupérer les flags vidéo du travail (acceptation / obligation)
$acceptVideos = 0;
$videoObligatoire = 0;
try {
    $flagStmt = $pdo->prepare("SELECT COALESCE(accept_videos,0) AS accept_videos, COALESCE(video_obligatoire,0) AS video_obligatoire FROM travaux WHERE id = ?");
    $flagStmt->execute([$assignation['id_travail']]);
    $flags = $flagStmt->fetch();
    if ($flags) {
        $acceptVideos = (int)$flags['accept_videos'];
        $videoObligatoire = (int)$flags['video_obligatoire'];
    }
} catch (Exception $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE | Rendre un Travail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="margin:0; font-family:'Inter', sans-serif; background:#f8fafc;">
    <?php include('../includes/sidebar.php'); ?>

    <main style="margin-left: 280px; padding: 50px;">
        <header style="margin-bottom: 40px;">
            <a href="mes_travaux.php" style="color: #64748b; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 15px;">
                <i class="fas fa-arrow-left"></i> Retour à mes travaux
            </a>
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Rendre un Travail</h1>
            <p style="color: #64748b;">Soumettez votre production avant la date limite.</p>
        </header>

        <?php 
        $error = getError(); 
        if ($error): 
        ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fcc;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Info du travail -->
        <div style="background: white; padding: 30px; border-radius: 20px; margin-bottom: 30px; border: 1px solid #edf2f7;">
            <h2 style="color: #1e293b; margin-bottom: 20px; font-size: 22px;">
                <?= htmlspecialchars($assignation['titre']) ?>
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
                <div>
                    <p style="color: #64748b; font-size: 13px; margin: 0 0 5px 0;">Matière</p>
                    <p style="color: #1e293b; font-weight: 600; margin: 0;">
                        <i class="fas fa-book" style="color: #1a4a8e;"></i> 
                        <?= htmlspecialchars($assignation['nom_espace']) ?>
                    </p>
                </div>
                <div>
                    <p style="color: #64748b; font-size: 13px; margin: 0 0 5px 0;">Formateur</p>
                    <p style="color: #1e293b; font-weight: 600; margin: 0;">
                        <i class="fas fa-user-tie" style="color: #1a4a8e;"></i> 
                        <?= htmlspecialchars($assignation['formateur_nom']) ?>
                    </p>
                </div>
                <div>
                    <p style="color: #64748b; font-size: 13px; margin: 0 0 5px 0;">Date limite</p>
                    <p style="color: <?= $estDepasse ? '#dc2626' : '#16a34a' ?>; font-weight: 700; margin: 0;">
                        <i class="fas fa-calendar-alt"></i> 
                        <?= date('d/m/Y à H:i', strtotime($assignation['date_limite'])) ?>
                    </p>
                </div>
            </div>

            <?php if ($estDepasse): ?>
                <div style="background: #fee2e2; padding: 15px; border-radius: 10px; border-left: 4px solid #dc2626; margin-bottom: 20px;">
                    <p style="color: #dc2626; font-weight: 700; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ATTENTION : La date limite est dépassée. Votre rendu sera marqué comme étant en retard.
                    </p>
                </div>
            <?php else: ?>
                <?php
                $joursRestants = $dateLimite->diff($maintenant)->days;
                $estUrgent = $joursRestants <= 2;
                ?>
                <?php if ($estUrgent): ?>
                    <div style="background: #fef3c7; padding: 15px; border-radius: 10px; border-left: 4px solid #f59e0b; margin-bottom: 20px;">
                        <p style="color: #92400e; font-weight: 700; margin: 0;">
                            <i class="fas fa-fire"></i> 
                            URGENT : Il reste seulement <?= $joursRestants ?> jour<?= $joursRestants > 1 ? 's' : '' ?> pour rendre ce travail !
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #f8fafc; padding: 20px; border-radius: 12px;">
                <h3 style="color: #1e293b; margin: 0 0 15px 0; font-size: 16px;">
                    <i class="fas fa-align-left"></i> Consignes
                </h3>
                <p style="color: #475569; margin: 0; line-height: 1.7; white-space: pre-wrap;">
                    <?= nl2br(htmlspecialchars($assignation['description'])) ?>
                </p>
                <?php if (!empty($files)): ?>
                    <div style="margin-top:12px;">
                        <p style="margin:0 0 6px 0; color:#475569; font-weight:600;">Fichiers fournis :</p>
                        <ul style="margin:0; padding-left: 18px; color:#475569;">
                            <?php foreach($files as $f): ?>
                                <li><a href="<?= htmlspecialchars($f['chemin_fichier']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($f['nom_fichier']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulaire de rendu -->
        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 800px; border: 1px solid #edf2f7;">
            <h3 style="color: #1e293b; margin-bottom: 25px; font-size: 20px;">
                <i class="fas fa-file-upload"></i> Votre Production
            </h3>

            <form action="../actions/rendu_action.php" method="POST" id="renduForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="soumettre_rendu">
                <input type="hidden" name="id_assignation" value="<?= $id_assignation ?>">
                <?php $csrf_token = generateCsrfToken(); error_log("CSRF render. Session=" . session_id() . " Token=" . substr($csrf_token,0,8) . "..."); ?>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div style="margin-bottom: 30px;">
                    <label style="display:block; margin-bottom:12px; font-weight:700; color:#1e293b; font-size: 16px;">
                        <i class="fas fa-file-alt" style="color: #1a4a8e;"></i>
                        Contenu de votre travail *
                    </label>
                    <textarea name="contenu" 
                              rows="15" 
                              required
                              placeholder="Rédigez votre travail ici... (Minimum 50 caractères)

Vous pouvez :
- Expliquer votre démarche
- Présenter vos résultats
- Coller du code
- Ajouter des liens vers vos fichiers externes si nécessaire"
                              style="width:100%; padding:16px; border:2px solid #e2e8f0; border-radius:12px; font-family: 'Courier New', monospace; font-size:14px; line-height: 1.6; resize: vertical;"></textarea>
                    <p style="font-size: 13px; color: #64748b; margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> 
                        Rédigez un contenu détaillé et structuré. Le formateur évaluera la qualité de votre travail.
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; color:#1e293b;">
                        <i class="fas fa-paperclip" style="color: #1a4a8e;"></i>
                        Joindre des fichiers (optionnel)
                    </label>
                    <input id="fichiersInput" type="file" name="fichiers[]" multiple accept="*/*" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                    <p style="font-size: 12px; color: #64748b; margin-top:8px;">Vous pouvez joindre des archives (.zip, .rar) ou n'importe quel fichier.</p>
                    <?php if ($acceptVideos): ?>
                        <p style="font-size:12px; color:#475569; margin-top:6px;">Vous pouvez également joindre des vidéos de démonstration (ex: .mp4, .mov).</p>
                        <label style="display:block; margin-top:8px; font-weight:700; color:#1e293b;">Fichiers vidéo (optionnel)</label>
                        <input id="videosInput" type="file" name="videos[]" multiple accept="video/*" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-size: 15px;">
                        <p style="font-size:12px; color:#64748b; margin-top:6px;">Joignez des vidéos au format courant (.mp4, .mov, .webm, .avi).</p>
                    <?php endif; ?>
                    <?php if ($videoObligatoire): ?>
                        <div style="margin-top:10px; background:#fff7ed; padding:10px; border-radius:8px; border:1px solid #fde3bf;">
                            <strong style="color:#92400e;">Attention :</strong>
                            Ce travail requiert l'ajout d'au moins une vidéo de démonstration. Si vous ne joignez pas de vidéo, la soumission sera refusée.
                        </div>
                    <?php endif; ?>
                    <div id="fichiersPreview" style="margin-top:10px; font-size:13px; color:#475569;"></div>
                    <?php if ($acceptVideos): ?><div id="videosPreview" style="margin-top:10px; font-size:13px; color:#475569;"></div><?php endif; ?>
                </div>

                <div style="margin-bottom: 30px;">
                    <label>
                        <input type="checkbox" name="confirmation" required style="margin-right: 8px;">
                        <span style="color: #475569; font-size: 14px;">
                            Je confirme que ce travail est entièrement de ma production et que je respecte les consignes données.
                        </span>
                    </label>
                </div>

                <div style="background: #fef3c7; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #fde68a;">
                    <p style="color: #92400e; margin: 0; font-size: 13px; font-weight: 600;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Important :</strong> Une fois soumis, vous ne pourrez plus modifier votre rendu. 
                        Relisez attentivement avant de valider.
                    </p>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" 
                            style="flex: 1; background:#10b981; color:white; border:none; padding:16px; border-radius:12px; cursor:pointer; font-weight:700; font-size:16px; transition: 0.3s;"
                            onmouseover="this.style.background='#059669'" 
                            onmouseout="this.style.background='#10b981'">
                        <i class="fas fa-check-circle"></i> Soumettre mon Travail
                    </button>
                    <a href="mes_travaux.php" 
                       style="flex: 0.3; background:#6b7280; color:white; padding:16px; border-radius:12px; text-decoration:none; font-weight:700; text-align:center; font-size:16px; transition: 0.3s;"
                       onmouseover="this.style.background='#4b5563'" 
                       onmouseout="this.style.background='#6b7280'">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

    </main>

    <script src="../assets/js/chunk_upload.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('renduForm');
        const videoRequired = <?= $videoObligatoire ? 'true' : 'false' ?>;
        
        // Gestion des fichiers sélectionnés côté client (ajout incrémental + suppression)
        const fichiersList = [];
        const videosList = [];

        const updateInputFilesFromArray = (inputEl, arr) => {
            try {
                const dt = new DataTransfer();
                arr.forEach(f => dt.items.add(f));
                inputEl.files = dt.files;
            } catch (err) {
                // Certains environnements/browsers peuvent ne pas supporter DataTransfer
            }
        };

        const renderList = (arr, container, isVideo) => {
            container.innerHTML = '';
            if (!arr.length) return;
            const ul = document.createElement('ul');
            ul.style.paddingLeft = '18px';
            arr.forEach((f, idx) => {
                const li = document.createElement('li');
                li.style.display = 'flex';
                li.style.alignItems = 'center';
                li.style.justifyContent = 'space-between';
                li.style.marginBottom = '6px';
                const nameSpan = document.createElement('span');
                nameSpan.textContent = f.name + ' (' + Math.round(f.size/1024) + ' KB)';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = 'Supprimer';
                btn.style.marginLeft = '12px';
                btn.style.background = '#ef4444';
                btn.style.color = 'white';
                btn.style.border = 'none';
                btn.style.padding = '6px 8px';
                btn.style.borderRadius = '6px';
                btn.style.cursor = 'pointer';
                btn.addEventListener('click', function() {
                    arr.splice(idx, 1);
                    // mettre à jour l'input correspondant
                    if (isVideo) updateInputFilesFromArray(videosInput, videosList); else updateInputFilesFromArray(fichiersInput, fichiersList);
                    renderList(arr, container, isVideo);
                });
                li.appendChild(nameSpan);
                li.appendChild(btn);
                ul.appendChild(li);
            });
            container.appendChild(ul);
        };

        const fichiersPreviewEl = document.getElementById('fichiersPreview');
        const videosPreviewEl = document.getElementById('videosPreview');

        fichiersInput.addEventListener('change', function(e) {
            const selected = Array.from(e.target.files || []);
            selected.forEach(f => fichiersList.push(f));
            // réinitialiser l'input pour permettre une nouvelle sélection
            fichiersInput.value = '';
            updateInputFilesFromArray(fichiersInput, fichiersList);
            renderList(fichiersList, fichiersPreviewEl, false);
        });

        if (videosInput) {
            videosInput.addEventListener('change', function(e) {
                const selected = Array.from(e.target.files || []);
                selected.forEach(f => videosList.push(f));
                videosInput.value = '';
                updateInputFilesFromArray(videosInput, videosList);
                renderList(videosList, videosPreviewEl, true);
            });
        }

        form.addEventListener('submit', function(e) {
            const contenu = form.querySelector('textarea[name="contenu"]').value.trim();
            const confirmation = form.querySelector('input[name="confirmation"]').checked;

            // Validation du contenu
            if (contenu.length < 50) {
                alert('Votre travail doit contenir au moins 50 caractères. Développez davantage votre réponse.');
                e.preventDefault();
                return;
            }

            // Validation de la confirmation
            if (!confirmation) {
                alert('Vous devez cocher la case de confirmation avant de soumettre.');
                e.preventDefault();
                return;
            }

            // Si une vidéo est obligatoire, vérifier côté client qu'au moins un fichier vidéo est présent
            if (videoRequired) {
                let hasVideo = false;
                // vérifier dans videosList et fichiersList
                const checkListForVideo = list => list.some(f => (f.type && f.type.indexOf('video/') === 0) || f.name.match(/\.(mp4|mov|avi|mkv|webm)$/i));
                if (checkListForVideo(videosList) || checkListForVideo(fichiersList)) hasVideo = true;
                if (!hasVideo) {
                    alert('Ce travail nécessite l\'ajout d\'au moins une vidéo. Joignez une vidéo avant de soumettre.');
                    e.preventDefault();
                    return;
                }
            }

            // Confirmation finale
            const message = '⚠️ Êtes-vous certain de vouloir soumettre ce travail ?\n\n' +
                          'Une fois soumis, vous ne pourrez plus le modifier.\n\n' +
                          'Nombre de caractères : ' + contenu.length;

            if (!confirm(message)) {
                e.preventDefault();
                return;
            }

            // If there are files/videos selected, intercept submission and upload them in chunks first
            const allFiles = [].concat(fichiersList || [], videosList || []);
            if (allFiles.length === 0) {
                // no files, allow normal submit
                return;
            }

            e.preventDefault();

            const csrfToken = form.querySelector('input[name="csrf_token"]').value;
            const uploadUrl = '../actions/upload_chunk.php';
            const tmpPaths = [];
            const origNames = [];

            (async function() {
                try {
                    for (let f of allFiles) {
                        const res = await ChunkUploader.uploadFileInChunks(f, { url: uploadUrl, csrfToken: csrfToken, onProgress: (p) => {
                            // optional: update UI progress
                        }});
                        tmpPaths.push(res.tmp_path);
                        origNames.push(res.original_name);
                    }

                    // append hidden inputs for tmp files
                    tmpPaths.forEach(p => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'tmp_files[]';
                        inp.value = p;
                        form.appendChild(inp);
                    });
                    origNames.forEach(n => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'tmp_originals[]';
                        inp.value = n;
                        form.appendChild(inp);
                    });

                    // remove file inputs to avoid sending large files again in the final POST
                    try {
                        if (typeof fichiersInput !== 'undefined' && fichiersInput) {
                            fichiersInput.value = '';
                            fichiersInput.removeAttribute('name');
                        }
                        if (typeof videosInput !== 'undefined' && videosInput) {
                            videosInput.value = '';
                            videosInput.removeAttribute('name');
                        }
                    } catch (e) {
                        // ignore
                    }

                    // Send only textual fields + tmp_files via fetch to avoid multipart with raw files
                    try {
                        const payload = new FormData();
                        payload.append('action', 'soumettre_rendu');
                        payload.append('id_assignation', form.querySelector('input[name="id_assignation"]').value);
                        payload.append('contenu', form.querySelector('textarea[name="contenu"]').value);
                        payload.append('confirmation', form.querySelector('input[name="confirmation"]').checked ? '1' : '0');
                        payload.append('csrf_token', csrfToken);
                        // add tmp files and originals
                        tmpPaths.forEach(p => payload.append('tmp_files[]', p));
                        origNames.forEach(n => payload.append('tmp_originals[]', n));

                        const res = await fetch('../actions/rendu_action.php', { method: 'POST', body: payload, credentials: 'same-origin' });
                        if (res.ok) {
                            // Redirect to mes_travaux (server stored messages in session)
                            window.location = 'mes_travaux.php';
                        } else {
                            const txt = await res.text();
                            alert('Erreur serveur: ' + res.status + '\n' + txt.slice(0,200));
                        }
                    } catch (err) {
                        alert('Échec lors de l’envoi final : ' + err.message);
                    }
                } catch (err) {
                    alert('Échec du transfert de fichiers : ' + err.message);
                }
            })();
        });
        
        // Compteur de caractères
        const textarea = form.querySelector('textarea[name="contenu"]');
        const counterDiv = document.createElement('div');
        counterDiv.style.cssText = 'text-align: right; color: #64748b; font-size: 12px; margin-top: 5px;';
        textarea.parentNode.appendChild(counterDiv);
        
        function updateCounter() {
            const count = textarea.value.length;
            const color = count < 50 ? '#dc2626' : '#10b981';
            counterDiv.innerHTML = '<i class="fas fa-keyboard"></i> ' + count + ' caractères';
            counterDiv.style.color = color;
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();

        // Aperçu des fichiers sélectionnés
        function renderPreview(input, container) {
            container.innerHTML = '';
            if (!input || !input.files || input.files.length === 0) return;
            const ul = document.createElement('ul');
            ul.style.paddingLeft = '18px';
            for (let i = 0; i < input.files.length; i++) {
                const f = input.files[i];
                const li = document.createElement('li');
                li.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
                ul.appendChild(li);
            }
            container.appendChild(ul);
        }

        const fichiersInput = document.getElementById('fichiersInput');
        const fichiersPreview = document.getElementById('fichiersPreview');
        if (fichiersInput && fichiersPreview) {
            fichiersInput.addEventListener('change', function() { renderPreview(fichiersInput, fichiersPreview); });
        }

        const videosInput = document.getElementById('videosInput');
        const videosPreview = document.getElementById('videosPreview');
        if (videosInput && videosPreview) {
            videosInput.addEventListener('change', function() { renderPreview(videosInput, videosPreview); });
        }
    });
    </script>
</body>
</html>