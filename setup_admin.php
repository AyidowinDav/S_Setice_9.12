<?php
require_once 'config/db.php';

// On vérifie si $pdo existe vraiment avant de continuer
if (!isset($pdo) || $pdo === null) {
    die("Désolé, la connexion à la base de données n'est pas établie. Vérifiez config/db.php.");
}

$email = "gloriaabatti60@gmail.com"; 
$password = "Gloire2000"; 
$nom = "Admin";
$prenom = "Principal";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'DIRECTEUR')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
    
    echo "Succès : Compte Directeur créé !";
} catch (Exception $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}