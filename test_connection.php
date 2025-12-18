<?php
include 'config/db.php';

try {
    echo "Connection successful!\n";
    echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
