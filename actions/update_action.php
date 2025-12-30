<?php
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $redirect = "../views/etudiants.php";
        } 
        elseif ($type == 'formateur') {
            $nom = trim($_POST['nom']);
            $email = trim($_POST['email']);
            $sql = "UPDATE formateurs SET nom = ?, email = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $id]);
            $redirect = "../views/formateurs.php";
        }

        echo "<script>alert('Mise à jour effectuée avec succès !'); window.location.href='$redirect';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erreur SQL : " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}