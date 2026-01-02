<?php
// Fonctions pour gérer les messages d'erreur et de succès via session

function setError($message) {
    $_SESSION['error'] = $message;
}

function setSuccess($message) {
    $_SESSION['success'] = $message;
}

function getError() {
    $error = $_SESSION['error'] ?? null;
    unset($_SESSION['error']); // Vider après récupération
    return $error;
}

function getSuccess() {
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['success']); // Vider après récupération
    return $success;
}
?>