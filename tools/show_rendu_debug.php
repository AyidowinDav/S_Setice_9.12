<?php
// Debug helper: lister rendus et fichiers associés pour une assignation ou un rendu
require_once(__DIR__ . '/../config/db.php');

$id_assignation = isset($_GET['id_assignation']) ? intval($_GET['id_assignation']) : null;
$id_rendu = isset($_GET['id_rendu']) ? intval($_GET['id_rendu']) : null;

header('Content-Type: text/plain; charset=utf-8');

if (!$id_assignation && !$id_rendu) {
    echo "Usage: ?id_assignation=NN or ?id_rendu=NN\n";
    exit;
}

try {
    if ($id_assignation) {
        $stmt = $pdo->prepare('SELECT id FROM rendus_travaux WHERE id_assignation = ? ORDER BY date_rendu DESC');
        $stmt->execute([$id_assignation]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$rows) { echo "Aucun rendu trouvé pour assignation $id_assignation\n"; exit; }
        $id_rendu = intval($rows[0]);
        echo "Trouvé rendu id=$id_rendu pour assignation=$id_assignation\n";
    }

    echo "--- Fichiers enregistrés en base (fichiers_rendus) ---\n";

    // detect column naming
    $colStmt = $pdo->query("SHOW COLUMNS FROM fichiers_rendus");
    $cols = array_column($colStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $idCol = in_array('id_rendu', $cols) ? 'id_rendu' : (in_array('rendu_id', $cols) ? 'rendu_id' : null);
    $nameCol = in_array('nom_fichier', $cols) ? 'nom_fichier' : (in_array('nom_original', $cols) ? 'nom_original' : null);
    $pathCol = in_array('chemin_fichier', $cols) ? 'chemin_fichier' : (in_array('fichier_url', $cols) ? 'fichier_url' : null);
    $sizeCol = in_array('taille_fichier', $cols) ? 'taille_fichier' : (in_array('taille', $cols) ? 'taille' : null);
    $typeCol = in_array('type_fichier', $cols) ? 'type_fichier' : (in_array('type', $cols) ? 'type' : null);

    if (!$idCol) {
        echo "Table fichiers_rendus ne contient pas de colonne id_rendu/rendu_id\n";
    } else {
        $fstmt = $pdo->prepare("SELECT * FROM fichiers_rendus WHERE {$idCol} = ?");
        $fstmt->execute([$id_rendu]);
        $files = $fstmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$files) {
            echo "Aucun enregistrement dans fichiers_rendus pour {$idCol}={$id_rendu}\n";
        } else {
            foreach ($files as $f) {
                echo sprintf("id=%s nom=%s chemin=%s taille=%s type=%s date=%s\n", $f['id'] ?? '(no id)', ($f[$nameCol] ?? '(no name)'), ($f[$pathCol] ?? '(no path)'), ($f[$sizeCol] ?? '(no size)'), ($f[$typeCol] ?? '(no type)'), $f['created_at'] ?? ($f['date_upload'] ?? '(no date)'));
                $path = ($f[$pathCol] ?? '');
                $pathFs = __DIR__ . '/../' . ltrim($path, '/');
                echo '  -> disk: ' . ($pathFs) . ' => ' . (file_exists($pathFs) ? 'EXISTS' : 'MISSING') . "\n";
            }
        }
    }

    echo "\n--- Contenu du dossier uploads/rendus/$id_rendu ---\n";
    $dir = __DIR__ . '/../uploads/rendus/' . $id_rendu;
    if (!is_dir($dir)) {
        echo "Dossier non trouvé: $dir\n";
    } else {
        $items = scandir($dir);
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $p = $dir . '/' . $it;
            echo ($it) . ' => ' . (is_file($p) ? filesize($p) . ' bytes' : (is_dir($p) ? 'dir' : 'other')) . "\n";
        }
    }

} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . "\n";
}

?>
