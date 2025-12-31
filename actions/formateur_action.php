<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    /* =========================
       1. VALIDATIONS
    ========================= */

    if ($nom === '' || $email === '') {
        echo "<script>alert('Tous les champs sont obligatoires.'); window.history.back();</script>";
        exit();
    }

    // Cas défavorable : email erroné
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format d\'email invalide.'); window.history.back();</script>";
        exit();
    }

    /* =========================
       2. EMAIL DÉJÀ EXISTANT ?
    ========================= */

    $check = $pdo->prepare("SELECT id FROM formateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        // Cas défavorable : email existant
        echo "<script>alert('Erreur : un formateur utilise déjà cet email.'); window.history.back();</script>";
        exit();
    }

    /* =========================
       3. CRÉATION DES IDENTIFIANTS
    ========================= */

    // Génération d’un mot de passe aléatoire
    $password_clair = substr(str_shuffle(
        'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'
    ), 0, 8);

    $password_hash = password_hash($password_clair, PASSWORD_DEFAULT);

    /* =========================
       4. INSERTION (CAS FAVORABLE)
    ========================= */

    try {
        $insert = $pdo->prepare("
            INSERT INTO formateurs (nom, email, mot_de_passe, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insert->execute([$nom, $email, $password_hash]);

        /* =========================
           5. ENVOI EMAIL IDENTIFIANTS
        ========================= */

        $subject = "Vos identifiants – S_SETICE";
        $message = "
Bonjour $nom,

Votre compte formateur a été créé avec succès.

Identifiants de connexion :
Email : $email
Mot de passe : $password_clair

Nous vous recommandons de changer votre mot de passe après la première connexion.

Cordialement,
La Direction S_SETICE
        ";

        $headers = "From: noreply@s-setice.com";

        @mail($email, $subject, $message, $headers);

        /* =========================
           6. MESSAGE DE SUCCÈS
        ========================= */

        echo "<script>
            alert('Compte formateur créé avec succès. Un email avec les identifiants a été envoyé.');
            window.location.href='../views/dashboard.php';
        </script>";

    } catch (PDOException $e) {
        echo "Erreur lors de la création du compte : " . $e->getMessage();
    }
}
?>
