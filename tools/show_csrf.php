<?php
// Debug helper: affiche le token CSRF en session (local only)
session_start();
require_once __DIR__ . '/../includes/csrf.php';
$token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '(aucun)';
?><!doctype html><html><head><meta charset="utf-8"><title>CSRF debug</title></head><body>
<h2>Session ID: <?php echo session_id(); ?></h2>
<p>CSRF token en session: <strong><?php echo htmlspecialchars($token); ?></strong></p>
<p>Générez un token via : <a href="../views/rendre_travail.php">ouvrir la page de rendu</a> (assurez-vous d'être connecté).</p>
</body></html>