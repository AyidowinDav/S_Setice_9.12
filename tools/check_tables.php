<?php
require __DIR__ . '/../config/db.php';
try {
    echo "Vérification des tables...\n";

    $tables = ['fichiers_travaux', 'fichiers_rendus'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE '" . $t . "'");
        $exists = (bool)$stmt->fetch();
        echo "Table $t : " . ($exists ? "EXISTE" : "N'EXISTE PAS") . "\n";
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) AS c FROM `$t`")->fetch();
            echo "  Nombre de lignes: " . ($count['c'] ?? 0) . "\n";
            $rows = $pdo->query("SELECT * FROM `$t` ORDER BY id DESC LIMIT 5")->fetchAll();
            if ($rows) {
                echo "  Dernières lignes (max 5):\n";
                foreach ($rows as $r) {
                    echo "    id=" . ($r['id'] ?? '') . " ";
                    if (isset($r['travail_id'])) echo "travail_id=" . $r['travail_id'] . " ";
                    if (isset($r['rendu_id'])) echo "rendu_id=" . $r['rendu_id'] . " ";
                    echo "fichier_url=" . ($r['fichier_url'] ?? '') . " nom=" . ($r['nom_original'] ?? '') . " taille=" . ($r['taille'] ?? '') . "\n";
                }
            } else {
                echo "  (aucune ligne)\n";
            }
        }
    }
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . "\n";
}
