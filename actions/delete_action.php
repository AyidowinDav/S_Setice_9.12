<?php
session_start();
require_once('../config/db.php');

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = intval($_GET['id']); // Sécurité : on force un entier

    try {
        if ($type == 'promotion') {
            $sql = "DELETE FROM promotions WHERE id = ?";
            $redirect = "../views/promotions.php";
        } elseif ($type == 'etudiant') {
            $sql = "DELETE FROM etudiants WHERE id = ?";
            $redirect = "../views/etudiants.php";
        } elseif ($type == 'formateur') {
            $sql = "DELETE FROM formateurs WHERE id = ?";
            $redirect = "../views/formateurs.php";
        } else {
            die("Type inconnu");
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo "<script>alert('Suppression réussie !'); window.location.href='$redirect';</script>";
    } catch (PDOException $e) {
        // Erreur fréquente : impossible de supprimer si l'élément est lié ailleurs (Clé étrangère)
        echo "<script>alert('Erreur : Impossible de supprimer car cet élément est lié à d\'autres enregistrements.'); window.history.back();</script>";
    }
}