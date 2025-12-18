<?php
require_once 'config/db.php';

try {
    $tables = ['utilisateur', 'promotion', 'espace_pedagogique', 'espace_pedagogique_utilisateur', 'espace_pedagogique_promotion', 'promotion_utilisateur'];
    foreach ($tables as $table) {
        echo "Table: $table\n";
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Key'] ? "[{$col['Key']}]" : "") . "\n";
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>