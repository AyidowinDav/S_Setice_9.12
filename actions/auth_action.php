<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {

        if ($user['is_active'] == 0) {
            header('Location: ../index.php?error=not_active');
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']   = strtoupper(trim($user['role'])); // Nettoie et met en majuscules
        $_SESSION['nom']    = $user['nom'];

        if ($_SESSION['role'] === 'DIRECTEUR' || $_SESSION['role'] === 'ADMIN') {
            header('Location: ../views/dashboard.php');
        } elseif ($_SESSION['role'] === 'FORMATEUR') {
            header('Location: ../views/formateur_dashboard.php');
        } elseif ($_SESSION['role'] === 'ETUDIANT') {
            header('Location: ../views/etudiant_dashboard.php');
        } else {
            // Si le rôle est bizarre, on détruit la session et on renvoie à l'accueil
            session_destroy();
            header('Location: ../index.php?error=role_inconnu');
        }
        exit;
    } else {
        header('Location: ../index.php?error=1');
        exit;
    }
}