<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $id_promotion = $_POST['id_promotion'];

    // 1. Vérifier si l'étudiant existe déjà (par email) - Critère US 2.3
    $check = $pdo->prepare("SELECT id FROM etudiants WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        // Cas Défavorable
        echo "<script>alert('Erreur : Un étudiant avec cet email existe déjà.'); window.history.back();</script>";
    } else {
        // 2. Insertion dans la base de données - Cas Favorable
        try {
            $insert = $pdo->prepare("INSERT INTO etudiants (nom, prenom, email, id_promotion) VALUES (?, ?, ?, ?)");
            $insert->execute([$nom, $prenom, $email, $id_promotion]);
            
            // Redirection vers le dashboard avec succès
            echo "<script>alert('Étudiant ajouté avec succès et assigné à sa promotion !'); window.location.href='../views/dashboard.php';</script>";
        } catch (PDOException $e) {
            echo "Erreur lors de l'insertion : " . $e->getMessage();
        }
    }
}