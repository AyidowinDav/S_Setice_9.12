<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Fonction pour consulter tous les espaces pédagogiques
function consulterTousLesEspaces() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT id, nom_matiere, description FROM espace_pedagogique ORDER BY nom_matiere");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return "Erreur de base de données: " . $e->getMessage();
    }
}

$espaces = consulterTousLesEspaces();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation des Espaces Pédagogiques - Directeur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Consultation de tous les espaces pédagogiques</h1>
        <p>En tant que directeur, vous pouvez consulter tous les espaces pédagogiques du système.</p>

        <?php if (is_array($espaces) && count($espaces) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la matière</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($espaces as $espace): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($espace['id']); ?></td>
                            <td><?php echo htmlspecialchars($espace['nom_matiere']); ?></td>
                            <td><?php echo htmlspecialchars($espace['description'] ?? 'Aucune description'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (is_string($espaces)): ?>
            <p><?php echo htmlspecialchars($espaces); ?></p>
        <?php else: ?>
            <p>Aucun espace pédagogique trouvé.</p>
        <?php endif; ?>

        <a href="../index.php">Retour à l'accueil</a>
    </div>
</body>
</html>
