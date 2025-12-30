<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On récupère uniquement le nom et l'email (la matière est gérée dans les espaces)
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);

    // 1. Validation du format de l'email (US 2.1 Cas défavorable)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format d\'email invalide.'); window.history.back();</script>";
        exit();
    }

    // 2. Vérifier si le formateur existe déjà (Unicité de l'email)
    $check = $pdo->prepare("SELECT id FROM formateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        // Cas où l'email est déjà pris
        echo "<script>alert('Erreur : Un formateur possède déjà cet email.'); window.history.back();</script>";
    } else {
        // 3. Insertion du nouveau formateur (Cas favorable)
        try {
            $insert = $pdo->prepare("INSERT INTO formateurs (nom, email) VALUES (?, ?)");
            $insert->execute([$nom, $email]);
            
            // Note : L'envoi de l'email de bienvenue avec identifiants se ferait ici.
            
            echo "<script>alert('Compte formateur S_SETICE créé avec succès !'); window.location.href='../views/dashboard.php';</script>";
        } catch (PDOException $e) {
            // Gestion d'erreur SQL
            echo "Erreur lors de la création du compte : " . $e->getMessage();
        }
    } 
}
?>