<?php
require_once 'config/db.php';
require_once 'admin/gestion_espaces.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = $_POST['etudiant_id'] ?? null;
    $promotion_id = $_POST['promotion_id'] ?? null;
    $espace_id = $_POST['espace_id'] ?? null;

    if ($etudiant_id && $promotion_id && $espace_id) {
        $message = insererEtudiantDansEspace($etudiant_id, $promotion_id, $espace_id);
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Insertion d'Étudiant</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Insertion d'Étudiant dans un Espace Pédagogique</h1>
        <form method="post">
            <label for="etudiant_id">ID Étudiant:</label>
            <input type="number" id="etudiant_id" name="etudiant_id" required>

            <label for="promotion_id">ID Promotion:</label>
            <input type="number" id="promotion_id" name="promotion_id" required>

            <label for="espace_id">ID Espace Pédagogique:</label>
            <input type="number" id="espace_id" name="espace_id" required>

            <button type="submit">Insérer Étudiant</button>
        </form>

        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <a href="index.php">Retour à l'accueil</a>
    </div>
</body>
</html>
