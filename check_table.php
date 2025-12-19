<?php
include 'config/db.php';

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'espace_pedagogique'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'espace_pedagogique' exists.\n";
    } else {
        echo "Table 'espace_pedagogique' does not exist.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
