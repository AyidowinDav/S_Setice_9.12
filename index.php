
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
        <script src="https://unicons.iconscout.com/release/v4.0.8/script/monochrome/bundle.js"></script>
        
        <title>Login Page</title>
    </head>
    <body>
        <div class="login">
            <div class="wrapper">
                <form id="login-form" action="actions/auth_action.php" method="post">
                    <h1>Login</h1>
                    <?php if (isset($_GET['error'])): ?>
                        <p style="color: red; text-align: center;">Email ou mot de passe incorrect.</p>
                    <?php endif; ?>
                    <div class="input-box">
                        <input type="email" id="username" name="username" placeholder="" required>
                        <label for="username">Email</label>
                        <i class="uil uil-user-circle"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" id="password" name="password" placeholder="" required>
                        <label for="password">Password</label>
                        <i class="uil uil-lock"></i>
                    </div>
                    <div class="remember-forgot">
                        <label><input type="checkbox" id="remember-me" name="remember_me">Remember me</label>
                        <a href="#">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
        </div>
                                                         
    </body>
</html>