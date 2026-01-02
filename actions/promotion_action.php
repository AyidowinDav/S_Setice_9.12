<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setError('Token CSRF invalide. Rechargez la page et réessayez.');
        header('Location: ../views/promotions.php');
        exit;
    }
    // Nettoyage des données reçues
    $nom = strtoupper(trim($_POST['nom'])); // On met le nom en majuscules pour l'uniformité
    $annee = trim($_POST['annee']);

    // 1. Validation de base
    if (empty($nom) || empty($annee)) {
        setError('Veuillez remplir tous les champs.');
        header('Location: ../views/promotions.php');
        exit;
    }

    try {
        // 2. Vérification d'existence (Éviter les doublons pour une même année)
        $check = $pdo->prepare("SELECT id FROM promotions WHERE nom = ? AND annee = ?");
        $check->execute([$nom, $annee]);

        if ($check->rowCount() > 0) {
            setError('Erreur : Cette promotion existe déjà pour l\'année ' . $annee . '.');
            header('Location: ../views/promotions.php');
            exit;
        } else {
            // 3. Insertion de la nouvelle promotion
            $insert = $pdo->prepare("INSERT INTO promotions (nom, annee) VALUES (?, ?)");
            $insert->execute([$nom, $annee]);
            
            // Redirection vers le dashboard avec un message de succès
            setSuccess('Promotion créée avec succès !');
            header('Location: ../views/promotions.php');
            exit;
        }
    } catch (PDOException $e) {
        // En cas d'erreur SQL, on affiche le message pour le débogage
        setError('Erreur lors de la création : ' . $e->getMessage());
        header('Location: ../views/promotions.php');
        exit;
    }
} else {
    // Si on tente d'accéder au script sans passer par le formulaire
    header('Location: ../views/promotions.php');
    exit();
}
?>