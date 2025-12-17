<?php
/**
 * Modèle pour gérer les espaces pédagogiques
 * Utilise PDO pour les interactions avec la base de données PostgreSQL
 */
class Espace {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les espaces pédagogiques
     * @return array Liste des espaces
     */
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM espace_pedagogique ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un espace par son ID
     * @param int $id ID de l'espace
     * @return array|null Données de l'espace ou null si non trouvé
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM espace_pedagogique WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel espace pédagogique
     * @param string $nom_matiere Nom de la matière
     * @param string $description Description de l'espace
     * @return bool Succès de l'opération
     */
    public function create($nom_matiere, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO espace_pedagogique (nom_matiere, description) VALUES (?, ?)");
        return $stmt->execute([$nom_matiere, $description]);
    }

    /**
     * Met à jour un espace pédagogique
     * @param int $id ID de l'espace
     * @param string $nom_matiere Nom de la matière
     * @param string $description Description de l'espace
     * @return bool Succès de l'opération
     */
    public function update($id, $nom_matiere, $description) {
        $stmt = $this->pdo->prepare("UPDATE espace_pedagogique SET nom_matiere = ?, description = ? WHERE id = ?");
        return $stmt->execute([$nom_matiere, $description, $id]);
    }

    /**
     * Supprime un espace pédagogique
     * @param int $id ID de l'espace
     * @return bool Succès de l'opération
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM espace_pedagogique WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
