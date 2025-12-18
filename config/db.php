<?php
$host = "localhost";
<<<<<<< HEAD
<<<<<<< HEAD
$dbname = "s_setice";
=======
$db   = "s_setice";
>>>>>>> c87cc1969ffd83390032c7b9d539d389c24921af
$user = "root";
$pass = "";
=======
$dbname = "s_setice";
$user = "root";
$pass = ""; // mot de passe MySQL (souvent vide en local)
>>>>>>> 51bb5f3af26f3934429b676cf181c97ccfd8d42c

$pdo = null;
try {
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 51bb5f3af26f3934429b676cf181c97ccfd8d42c
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
<<<<<<< HEAD
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
=======
    $dsn = "mysql:host=$host;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
>>>>>>> c87cc1969ffd83390032c7b9d539d389c24921af
=======
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
>>>>>>> 51bb5f3af26f3934429b676cf181c97ccfd8d42c
}
