<?php
/**
 * Contrôleur pour gérer les espaces pédagogiques
 * Gère les actions CRUD et la logique métier
 */
require_once __DIR__ . '/../model/Espace.php';

class EspaceController {
    private $espaceModel;

    public function __construct($pdo) {
        $this->espaceModel = new Espace($pdo);
    }

    /**
     * Affiche la liste des espaces pédagogiques
     */
    public function index() {
        $espaces = $this->espaceModel->getAll();
        include __DIR__ . '/../views/liste.php';
    }

    /**
     * Affiche le formulaire de création
     */
    public function create() {
        include __DIR__ . '/../views/formulaire.php';
    }

    /**
     * Traite la création d'un nouvel espace
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_matiere = trim($_POST['nom_matiere']);
            $description = trim($_POST['description']);

            if (!empty($nom_matiere) && !empty($description)) {
                if ($this->espaceModel->create($nom_matiere, $description)) {
                    header('Location: index.php?action=index&success=1');
                    exit;
                } else {
                    $error = "Erreur lors de la création de l'espace.";
                }
            } else {
                $error = "Tous les champs sont obligatoires.";
            }
        }
        include __DIR__ . '/../views/formulaire.php';
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id) {
        $espace = $this->espaceModel->getById($id);
        if (!$espace) {
            header('Location: index.php?action=index&error=1');
            exit;
        }
        include __DIR__ . '/../views/formulaire.php';
    }

    /**
     * Traite la mise à jour d'un espace
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_matiere = trim($_POST['nom_matiere']);
            $description = trim($_POST['description']);

            if (!empty($nom_matiere) && !empty($description)) {
                if ($this->espaceModel->update($id, $nom_matiere, $description)) {
                    header('Location: index.php?action=index&success=2');
                    exit;
                } else {
                    $error = "Erreur lors de la mise à jour de l'espace.";
                }
            } else {
                $error = "Tous les champs sont obligatoires.";
            }
        }
        $espace = $this->espaceModel->getById($id);
        include __DIR__ . '/../views/formulaire.php';
    }

    /**
     * Supprime un espace
     */
    public function delete($id) {
        if ($this->espaceModel->delete($id)) {
            header('Location: index.php?action=index&success=3');
            exit;
        } else {
            header('Location: index.php?action=index&error=2');
            exit;
        }
    }
}
?>
