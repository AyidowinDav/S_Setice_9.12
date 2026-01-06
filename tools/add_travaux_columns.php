<?php
// Script de migration léger : ajoute accept_videos et video_obligatoire si absents
chdir(__DIR__ . '/..');
require_once('config/db.php');

try {
    $pdo->beginTransaction();
    $schemaStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'travaux' AND COLUMN_NAME = ?");

    $toAdd = [
        'accept_videos' => "TINYINT(1) NOT NULL DEFAULT 0",
        'video_obligatoire' => "TINYINT(1) NOT NULL DEFAULT 0",
    ];

    foreach ($toAdd as $col => $definition) {
        $schemaStmt->execute([$col]);
        $exists = (int)$schemaStmt->fetchColumn();
        if ($exists === 0) {
            echo "Ajout de la colonne $col...\n";
            $pdo->exec("ALTER TABLE travaux ADD COLUMN $col $definition");
            echo "Colonne $col ajoutée.\n";
        } else {
            echo "Colonne $col déjà présente.\n";
        }
    }

    $pdo->commit();

    // Afficher l'état final des colonnes
    $cols = $pdo->query("SHOW COLUMNS FROM travaux")->fetchAll(PDO::FETCH_ASSOC);
    echo "Colonnes de la table travaux:\n";
    foreach ($cols as $c) {
        echo " - {$c['Field']} ({$c['Type']})\n";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration terminée.\n";

?>
