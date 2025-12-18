<?php
include 'config/db.php';

try {
    $stmt = $pdo->query('DESCRIBE utilisateur');
    echo "Table utilisateur columns:\n";
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
