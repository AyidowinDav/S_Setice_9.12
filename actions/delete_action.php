<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

if (isset($_GET['type']) && isset($_GET['id'])) {
    // Vérifier le token CSRF (pour les liens GET sensibles, on peut utiliser un paramètre)
    // Ici, comme c'est GET, on suppose que le token est passé en paramètre ou on le génère
    // Pour simplicité, on peut ajouter un token dans l'URL ou utiliser POST pour delete
    // Mais pour l'exemple, on vérifie si présent
    if (isset($_GET['csrf_token']) && !verifyCsrfToken($_GET['csrf_token'])) {
        setError('Token CSRF invalide.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
    $type = $_GET['type'];
    $id = intval($_GET['id']); // Sécurité : on force un entier

    try {
        if ($type == 'promotion') {
            $sql = "DELETE FROM promotions WHERE id = ?";
            $redirect = "../views/promotions.php";
        } elseif ($type == 'etudiant') {
            // Récupérer id_utilisateur avant suppression
            $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM etudiants WHERE id = ?");
            $stmtUser->execute([$id]);
            $userId = $stmtUser->fetchColumn();
            
            $sql = "DELETE FROM etudiants WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            // Supprimer aussi de utilisateur si id_utilisateur existe
            if ($userId) {
                $stmtDelUser = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
                $stmtDelUser->execute([$userId]);
            }
            $redirect = "../views/etudiants.php";
        } elseif ($type == 'formateur') {
            // Récupérer id_utilisateur avant suppression
            $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM formateurs WHERE id = ?");
            $stmtUser->execute([$id]);
            $userId = $stmtUser->fetchColumn();
            
            $sql = "DELETE FROM formateurs WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            // Supprimer aussi de utilisateur si id_utilisateur existe
            if ($userId) {
                $stmtDelUser = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
                $stmtDelUser->execute([$userId]);
            }
            $redirect = "../views/formateurs.php";
        } else {
            setError('Type inconnu.');
            header('Location: ../index.php');
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        setSuccess('Suppression réussie !');
        header("Location: $redirect");
        exit;
    } catch (PDOException $e) {
        // Erreur fréquente : impossible de supprimer si l'élément est lié ailleurs (Clé étrangère)
        setError('Erreur : Impossible de supprimer car cet élément est lié à d\'autres enregistrements.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
}