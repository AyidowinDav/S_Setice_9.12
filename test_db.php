<?php
include 'config/db.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";

    if (in_array('utilisateur', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateur");
        echo "Utilisateurs count: " . $stmt->fetch()['count'] . "\n";
    }
    if (in_array('espace_pedagogique', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM espace_pedagogique");
        echo "Espaces count: " . $stmt->fetch()['count'] . "\n";
    }
    if (in_array('promotion', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM promotion");
        echo "Promotions count: " . $stmt->fetch()['count'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
