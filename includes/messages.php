<?php
/**
 * Gestion des messages flash
 * S-SETICE - Système de Gestion de Formation
 */

function setError($message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['error'] = $message;
}

function setSuccess($message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['success'] = $message;
}

function getError() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $error = $_SESSION['error'] ?? null;
    unset($_SESSION['error']);
    return $error;
}

function getSuccess() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['success']);
    return $success;
}

function setWarning($message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['warning'] = $message;
}

function getWarning() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $warning = $_SESSION['warning'] ?? null;
    unset($_SESSION['warning']);
    return $warning;
}

function hasError() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['error']);
}

function hasSuccess() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['success']);
}
?>