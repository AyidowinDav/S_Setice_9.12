<?php
require_once(__DIR__ . '/../config/db.php');
try {
    $stmt = $pdo->query('SHOW COLUMNS FROM fichiers_rendus');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in fichiers_rendus:\n";
    foreach ($cols as $c) {
        echo " - {$c['Field']} ({$c['Type']})\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}

?>
