<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>S_SETICE - Connexion</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <img src="assets/cssimg/logo.jfif" alt="Logo">
        <h2>S_SETICE</h2>
        <p>Connectez-vous à votre espace</p>
        
        <form action="actions/auth_action.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
        
        <div class="links">
            <a href="#">Mot de passe oublié ?</a>
            <a href="#">Créer un compte</a>
        </div>
    </div>
</body>
</html>