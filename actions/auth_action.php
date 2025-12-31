<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../index.php?error=1');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            header('Location: ../views/dashboard.php');
            exit;
        } else {
            header('Location: ../index.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        // Tu peux loguer l’erreur dans un fichier, mais ne pas l’afficher à l’utilisateur
        header('Location: ../index.php?error=1');
        exit;
    }
}
?>
