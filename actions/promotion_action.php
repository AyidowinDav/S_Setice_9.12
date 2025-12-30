<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nettoyage des données reçues
    $nom = strtoupper(trim($_POST['nom'])); // On met le nom en majuscules pour l'uniformité
    $annee = trim($_POST['annee']);

    // 1. Validation de base
    if (empty($nom) || empty($annee)) {
        echo "<script>alert('Veuillez remplir tous les champs.'); window.history.back();</script>";
        exit();
    }

    try {
        // 2. Vérification d'existence (Éviter les doublons pour une même année)
        $check = $pdo->prepare("SELECT id FROM promotions WHERE nom = ? AND annee = ?");
        $check->execute([$nom, $annee]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Erreur : Cette promotion existe déjà pour l\'année $annee.'); window.history.back();</script>";
        } else {
            // 3. Insertion de la nouvelle promotion
            $insert = $pdo->prepare("INSERT INTO promotions (nom, annee) VALUES (?, ?)");
            $insert->execute([$nom, $annee]);
            
            // Redirection vers le dashboard avec un message de succès
            echo "<script>alert('Promotion créée avec succès !'); window.location.href='../views/dashboard.php';</script>";
        }
    } catch (PDOException $e) {
        // En cas d'erreur SQL, on affiche le message pour le débogage
        echo "Erreur lors de la création : " . $e->getMessage();
    }
} else {
    // Si on tente d'accéder au script sans passer par le formulaire
    header('Location: ../views/promotions.php');
    exit();
}
?>