<?php
include 'config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM utilisateur WHERE role = 'DIRECTEUR'");
    $user = $stmt->fetch();
    if ($user) {
        echo "Directeur user exists:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Nom: " . $user['nom'] . "\n";
        echo "Prenom: " . $user['prenom'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
    } else {
        echo "No Directeur user found.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
