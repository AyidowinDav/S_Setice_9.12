<?php
$host = "localhost";
<<<<<<< HEAD
$dbname = "s_setice";
=======
$db   = "s_setice";
>>>>>>> c87cc1969ffd83390032c7b9d539d389c24921af
$user = "root";
$pass = "";

$pdo = null;
try {
<<<<<<< HEAD
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
=======
    $dsn = "mysql:host=$host;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
>>>>>>> c87cc1969ffd83390032c7b9d539d389c24921af
}
