<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <title>S-SETICE - Connexion</title>
    <style>
:root {
    --primary: #1a4a8e;
    --bg-body: #f8f9fc;
    --sidebar-white: #ffffff;
    --text-grey: #6c757d;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #eceef4ff 0%, #f7f3faff 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #1e293b;
}

.login {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.wrapper {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 400px;
    border: 1px solid #f1f5f9;
}

#login-form h1 {
    text-align: center;
    margin-bottom: 10px;
    color: #0f172a;
    font-size: 28px;
    font-weight: 800;
}

.subtitle {
    text-align: center;
    color: #64748b;
    font-size: 14px;
    margin-bottom: 30px;
}

.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.input-box {
    position: relative;
    margin-bottom: 30px;
}

.input-box input {
    width: 100%;
    padding: 15px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s;
    background: #f8fafc;
}

.input-box input:focus {
    border-color: #1a4a8e;
    background: white;
}

.input-box label {
    position: absolute;
    top: 15px;
    left: 20px;
    color: #64748b;
    pointer-events: none;
    transition: all 0.3s;
}

.input-box input:focus + label,
.input-box input:valid + label {
    top: -10px;
    left: 15px;
    font-size: 12px;
    color: #1a4a8e;
    background: white;
    padding: 0 5px;
}

.input-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.remember-forgot label {
    font-size: 14px;
    color: #64748b;
}

.remember-forgot a {
    color: #1a4a8e;
    text-decoration: none;
    font-size: 14px;
}

.remember-forgot a:hover {
    text-decoration: underline;
}

.btn {
    width: 100%;
    padding: 15px;
    background: #1a4a8e;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn:hover {
    background: #0f3460;
}

.footer-text {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: #94a3b8;
}
    </style>
</head>
<body>
    <div class="login">
        <div class="wrapper">
            <form id="login-form" action="actions/auth_action.php" method="post">
                <h1>S-SETICE</h1>
                <p class="subtitle">Système de Gestion de Formation</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <strong>⚠️ Erreur :</strong>
                        <?php
                        switch ($_GET['error']) {
                            case 'empty_fields':
                                echo "Veuillez remplir tous les champs.";
                                break;
                            case 'invalid_email':
                                echo "Format d'email invalide.";
                                break;
                            case 'not_active':
                                echo "Votre compte n'est pas encore activé. Vérifiez vos emails.";
                                break;
                            case 'role_inconnu':
                                echo "Rôle utilisateur inconnu. Contactez l'administrateur.";
                                break;
                            case 'system':
                                echo "Erreur système. Contactez l'administrateur.";
                                break;
                            default:
                                echo "Email ou mot de passe incorrect.";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="input-box">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Adresse email</label>
                    <i class="uil uil-envelope"></i>
                </div>

                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Mot de passe</label>
                    <i class="uil uil-lock"></i>
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" id="remember-me" name="remember_me"> Se souvenir de moi
                    </label>
                    <a href="#">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn">Se connecter</button>
                
                <p class="footer-text">© 2026 S-SETICE - Tous droits réservés</p>
            </form>
        </div>
    </div>
</body>
</html>