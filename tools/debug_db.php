<?php
require_once(__DIR__ . '/../config/db.php');

header('Content-Type: text/html; charset=utf-8');

function dumpTable($pdo, $table, $limit = 10) {
    echo "<h2>$table</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM $table LIMIT $limit");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            echo "<p><em>Aucun enregistrement dans $table</em></p>";
            return;
        }
        echo '<table border="1" cellpadding="6" style="border-collapse:collapse;">';
        // header
        echo '<tr>'; foreach (array_keys($rows[0]) as $col) { echo '<th>' . htmlspecialchars($col) . '</th>'; } echo '</tr>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($r as $c) { echo '<td>' . htmlspecialchars((string)$c) . '</td>'; }
            echo '</tr>';
        }
        echo '</table>';
    } catch (Exception $e) {
        echo '<pre>Erreur: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}

echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Debug DB</title></head><body>';

$tables = ['promotions', 'etudiants', 'formateurs', 'espaces_pedagogiques'];
foreach ($tables as $t) dumpTable($pdo, $t, 50);

echo '<p>Vérifiez les colonnes nom / prenom / nom_espace / nom dans les résultats ci-dessus.</p>';

echo '</body></html>';
