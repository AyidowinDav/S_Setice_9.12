<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Fonction pour insérer un étudiant d'une promotion donnée dans un espace pédagogique
function insererEtudiantDansEspace($etudiant_id, $promotion_id, $espace_id) {
    global $pdo;

    try {
        // Vérifier si l'étudiant appartient à la promotion donnée
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM promotion_utilisateur WHERE utilisateur_id = ? AND promotion_id = ?");
        $stmt->execute([$etudiant_id, $promotion_id]);
        if ($stmt->fetchColumn() == 0) {
            return "Erreur: L'étudiant n'appartient pas à la promotion spécifiée.";
        }

        // Vérifier si l'espace pédagogique est associé à la promotion
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM espace_pedagogique_promotion WHERE espace_id = ? AND promotion_id = ?");
        $stmt->execute([$espace_id, $promotion_id]);
        if ($stmt->fetchColumn() == 0) {
            return "Erreur: L'espace pédagogique n'est pas associé à la promotion spécifiée.";
        }

        // Vérifier si l'étudiant n'est pas déjà dans l'espace
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM espace_pedagogique_utilisateur WHERE espace_id = ? AND utilisateur_id = ?");
        $stmt->execute([$espace_id, $etudiant_id]);
        if ($stmt->fetchColumn() > 0) {
            return "Erreur: L'étudiant est déjà inscrit dans cet espace pédagogique.";
        }

        // Insérer l'étudiant dans l'espace
        $stmt = $pdo->prepare("INSERT INTO espace_pedagogique_utilisateur (espace_id, utilisateur_id) VALUES (?, ?)");
        $stmt->execute([$espace_id, $etudiant_id]);

        return "Succès: L'étudiant a été inséré dans l'espace pédagogique.";
    } catch (PDOException $e) {
        return "Erreur de base de données: " . $e->getMessage();
    }
}

// Exemple d'utilisation (pour test)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = $_POST['etudiant_id'] ?? null;
    $promotion_id = $_POST['promotion_id'] ?? null;
    $espace_id = $_POST['espace_id'] ?? null;

    if ($etudiant_id && $promotion_id && $espace_id) {
        $result = insererEtudiantDansEspace($etudiant_id, $promotion_id, $espace_id);
        echo $result;
    } else {
        echo "Paramètres manquants.";
    }
}
?>
