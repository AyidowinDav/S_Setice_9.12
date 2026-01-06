<?php
session_start();
require_once('../config/db.php');
require_once('../includes/csrf.php');
require_once('../includes/messages.php');

/**
 * SSS-5 : CRÉATION D'UNE PROMOTION POUR UNE ANNÉE DONNÉE
 * 
 * Cas Favorable : Promotion créée avec succès
 * Cas Défavorable : Promotion existe déjà pour cette année précise
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/promotions.php');
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setError('Token CSRF invalide. Rechargez la page.');
    header('Location: ../views/promotions.php');
    exit;
}

// Récupération données
$nom = strtoupper(trim($_POST['nom'] ?? ''));
$annee = trim($_POST['annee'] ?? '');

// VALIDATION 1 : Champs obligatoires
if (empty($nom) || empty($annee)) {
    setError('Veuillez remplir tous les champs.');
    header('Location: ../views/promotions.php');
    exit;
}

// VALIDATION 2 : Nom minimum 2 caractères
if (strlen($nom) < 2) {
    setError('Le nom de la promotion doit contenir au moins 2 caractères.');
    header('Location: ../views/promotions.php');
    exit;
}

// VALIDATION 3 : Format année (YYYY ou YYYY-YYYY)
$anneeRegex = '/^(\d{4})(-\d{4})?$/';
if (!preg_match($anneeRegex, $annee)) {
    setError('L\'année doit être au format YYYY ou YYYY-YYYY (ex: 2024 ou 2024-2025).');
    header('Location: ../views/promotions.php');
    exit;
}

// VALIDATION 4 : Année cohérente
$premierAnnee = intval(explode('-', $annee)[0]);
$currentYear = date('Y');

if ($premierAnnee < 2000 || $premierAnnee > ($currentYear + 10)) {
    setError('L\'année doit être entre 2000 et ' . ($currentYear + 10) . '.');
    header('Location: ../views/promotions.php');
    exit;
}

// Si format YYYY-YYYY, vérifier la cohérence
if (strpos($annee, '-') !== false) {
    $annees = explode('-', $annee);
    $deuxiemeAnnee = intval($annees[1]);
    
    if ($deuxiemeAnnee !== ($premierAnnee + 1)) {
        setError('Les années doivent être consécutives (ex: 2024-2025).');
        header('Location: ../views/promotions.php');
        exit;
    }
}

try {
    // CAS DÉFAVORABLE : Vérifier si promotion existe déjà pour cette année
    $check = $pdo->prepare("SELECT id, nom, annee FROM promotions WHERE nom = ? AND annee = ?");
    $check->execute([$nom, $annee]);

    if ($check->rowCount() > 0) {
        setError('Erreur : Cette promotion (' . $nom . ') existe déjà pour l\'année ' . $annee . '.');
        header('Location: ../views/promotions.php');
        exit;
    }
    
    // CAS FAVORABLE : Insertion de la promotion
    $insert = $pdo->prepare("INSERT INTO promotions (nom, annee) VALUES (?, ?)");
    $insert->execute([$nom, $annee]);
    
    setSuccess('Promotion créée avec succès !');
    header('Location: ../views/promotions.php');
    exit;
    
} catch (PDOException $e) {
    error_log('Erreur création promotion : ' . $e->getMessage());
    setError('Erreur lors de la création : ' . $e->getMessage());
    header('Location: ../views/promotions.php');
    exit;
}
?>