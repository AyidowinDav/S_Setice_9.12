<?php
/**
 * Point d'entrée pour la gestion des espaces pédagogiques
 * Utilise le pattern MVC pour une séparation claire des responsabilités
 */

// Inclusion de la configuration de la base de données
require_once '../../config/db.php';

// Inclusion du contrôleur
require_once 'controller/EspaceController.php';

// Initialisation du contrôleur
$controller = new EspaceController($pdo);

// Routage des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'edit':
        if ($id) {
            $controller->edit($id);
        } else {
            header('Location: index.php?action=index&error=1');
        }
        break;
    case 'update':
        if ($id) {
            $controller->update($id);
        } else {
            header('Location: index.php?action=index&error=1');
        }
        break;
    case 'delete':
        if ($id) {
            $controller->delete($id);
        } else {
            header('Location: index.php?action=index&error=1');
        }
        break;
    default:
        $controller->index();
        break;
}
?>
