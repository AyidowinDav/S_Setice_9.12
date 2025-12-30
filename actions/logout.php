<?php
session_start();

// 1. On vide toutes les variables de session
$_SESSION = array();

// 2. On détruit le cookie de session si il existe (optionnel mais recommandé)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. On détruit la session
session_destroy();

// 4. Redirection vers la page de connexion à la racine
header("Location: ../index.php");
exit();