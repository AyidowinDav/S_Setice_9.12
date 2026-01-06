<?php
/**
 * Gestion de la protection CSRF (Cross-Site Request Forgery)
 * S-SETICE - Système de Gestion de Formation
 */

/**
 * Génère un token CSRF unique par session
 * @return string Le token CSRF
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité d'un token CSRF
 * @param string $token Le token à vérifier
 * @return bool True si valide, False sinon
 */
function verifyCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Alias de verifyCsrfToken pour compatibilité
 * @param string $token Le token à vérifier
 * @return bool True si valide, False sinon
 */
function validateCsrfToken($token) {
    return verifyCsrfToken($token);
}

/**
 * Vérifie le token CSRF et redirige en cas d'échec
 * @param string $token Le token à vérifier
 * @param string $redirectUrl URL de redirection en cas d'échec
 * @return void
 */
function requireValidCsrfToken($token, $redirectUrl = '../index.php') {
    if (!verifyCsrfToken($token)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error'] = 'Token CSRF invalide. Veuillez réessayer.';
        header('Location: ' . $redirectUrl);
        exit;
    }
}