<!DOCTYPE html>
<html lang="fr"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
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
    background-color: #f8fafc;
    color: #1e293b;
}

.main-wrapper {
    display: flex;
}

/* SIDEBAR EXACTE */
.sidebar {
    width: 280px;
    height: 100vh;
    background: white;
    border-right: 1px solid #e2e8f0;
    position: fixed;
    padding: 30px 20px;
}

.logo-section h2 {
    color: #1a4a8e;
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 40px;
}

.sidebar-menu ul { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #64748b;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s;
}

.sidebar-menu a i { margin-right: 15px; font-size: 18px; }
.sidebar-menu a:hover, .sidebar-menu a.active {
    background: #f1f5f9;
    color: #1a4a8e;
    font-weight: 600;
}

/* CONTENT AREA */
.content-area {
    margin-left: 280px;
    padding: 50px;
    width: 100%;
}

.main-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.main-header h1 { font-size: 32px; font-weight: 700; color: #0f172a; }

/* CARTES D'ACTIONS */
.section-title { font-size: 20px; margin-bottom: 25px; color: #334155; }

.actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    margin-bottom: 50px;
}

.action-card {
    background: white;
    padding: 40px 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border: 1px solid #f1f5f9;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: #f8fafc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #94a3b8;
    font-size: 20px;
}

.action-card h3 { font-size: 15px; margin-bottom: 20px; color: #1e293b; }

.btn-card {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 8px 20px;
    border-radius: 6px;
    color: #475569;
    cursor: pointer;
    font-weight: 500;
}

/* TABLEAU / LISTE */
.management-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.table-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #f1f5f9;
}

.btn-outline {
    padding: 8px 25px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
}

.btn-blue {
    background: #3b82f6;
    color: white;
    border: none;
}

/* Styles pour la page de connexion */
.login {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f8fafc; /* Même fond que le dashboard */
    padding: 20px;
}

.wrapper {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 2px 4px rgba(0, 0, 0, 0.03);
    width: 100%;
    max-width: 400px;
    border: 1px solid #f1f5f9;
}

#login-form h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #0f172a;
    font-size: 28px;
    font-weight: 800;
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
    </style>
    <title>Page de Connexion</title>
</head>
<body>
    <div class="login">
        <div class="wrapper">
            <form id="login-form" action="actions/auth_action.php" method="post">
                <h1>Login</h1>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color: #ff4d4d; text-align: center; font-size: 14px;">
                        <?php
                        switch ($_GET['error']) {
                            case 'rate_limit':
                                $minutes = intval($_GET['minutes'] ?? 15);
                                echo "Trop de tentatives de connexion. Réessayez dans $minutes minutes.";
                                break;
                            case 'not_active':
                                echo "Votre compte n'est pas encore activé. Vérifiez vos emails.";
                                break;
                            case 'role_inconnu':
                                echo "Rôle utilisateur inconnu. Contactez l'administrateur.";
                                break;
                            default:
                                echo "Email ou mot de passe incorrect.";
                        }
                        ?>
                    </p>
                <?php endif; ?>

                <div class="input-box">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Email</label>
                    <i class="uil uil-user-circle"></i>
                </div>

                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Mot de passe</label>
                    <i class="uil uil-lock"></i>
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" id="remember-me" name="remember_me"> Souvenez-vous de moi
                    </label>
                    <a href="#">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>