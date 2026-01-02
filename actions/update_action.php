<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php'); // Protection CSRF
require_once('../includes/messages.php'); // Gestion des messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier le token CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setError('Token CSRF invalide. Rechargez la page et réessayez.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
    $type = $_POST['type'];
    $id = intval($_POST['id']);

    try {
        if ($type == 'promotion') {
            $nom = strtoupper(trim($_POST['nom']));
            $annee = trim($_POST['annee']);
            $sql = "UPDATE promotions SET nom = ?, annee = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $annee, $id]);
            $redirect = "../views/promotions.php";
        } 
        elseif ($type == 'etudiant') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $id_p = $_POST['id_promotion'];
            $sql = "UPDATE etudiants SET nom = ?, prenom = ?, email = ?, id_promotion = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prenom, $email, $id_p, $id]);
            
            // Mettre à jour aussi dans utilisateur
            $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM etudiants WHERE id = ?");
            $stmtUser->execute([$id]);
            $userId = $stmtUser->fetchColumn();
            if ($userId) {
                $stmtUpdateUser = $pdo->prepare("UPDATE utilisateur SET nom = ?, email = ? WHERE id = ?");
                $stmtUpdateUser->execute([$nom, $email, $userId]);
            }
            $redirect = "../views/etudiants.php";
        } 
        elseif ($type == 'formateur') {
            $nom = trim($_POST['nom']);
            $email = trim($_POST['email']);
            $sql = "UPDATE formateurs SET nom = ?, email = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $id]);
            
            // Mettre à jour aussi dans utilisateur
            $stmtUser = $pdo->prepare("SELECT id_utilisateur FROM formateurs WHERE id = ?");
            $stmtUser->execute([$id]);
            $userId = $stmtUser->fetchColumn();
            if ($userId) {
                $stmtUpdateUser = $pdo->prepare("UPDATE utilisateur SET nom = ?, email = ? WHERE id = ?");
                $stmtUpdateUser->execute([$nom, $email, $userId]);
            }
            $redirect = "../views/formateurs.php";
        }

        setSuccess('Mise à jour effectuée avec succès !');
        header("Location: $redirect");
        exit;
    } catch (PDOException $e) {
        setError('Erreur SQL : ' . $e->getMessage());
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit;
    }
}