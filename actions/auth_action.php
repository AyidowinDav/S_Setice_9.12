<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']); // trim() enlève les espaces accidentels
    $password = $_POST['password'];

    // Nouvelles initiales du directeur
    if ($email === "gloriaabatti60@gmail.com" && $password === "Gloire2000") {
        $_SESSION['user'] = "Directeur";
        header('Location: ../views/dashboard.php');
        exit();
    } else {
        // Message d'erreur élégant avec retour à l'index
        echo "<script>alert('Identifiants incorrects.'); window.location.href='../index.php';</script>";
        exit();
    }
}
?>