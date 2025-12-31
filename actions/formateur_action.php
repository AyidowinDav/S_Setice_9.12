<?php
session_start();
require_once('../config/db.php');
require_once('../includes/mailer.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom   = trim($_POST['nom']);
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Email invalide'); history.back();</script>";
        exit;
    }

    // Vérification unicité
    $check = $pdo->prepare("SELECT id FROM formateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo "<script>alert('Ce formateur existe déjà'); history.back();</script>";
        exit;
    }

    // Génération mot de passe
    $password_clair = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 8);
    $password_hash = password_hash($password_clair, PASSWORD_DEFAULT);

    // Insertion
    $insert = $pdo->prepare("
        INSERT INTO formateurs (nom, email, password, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $insert->execute([$nom, $email, $password_hash]);

    // Envoi mail
    sendMail(
        $email,
        'Compte formateur S-SETICE',
        "Bonjour $nom,

Votre compte formateur a été créé avec succès.

Identifiants de connexion :
Email : $email
Mot de passe : $password_clair

Merci de modifier votre mot de passe après connexion.

— Administration S-SETICE"
    );

    echo "<script>
        alert('Formateur créé avec succès et email envoyé.');
        window.location.href='../views/dashboard.php';
    </script>";
}
