<?php
session_start();
require_once('../config/db.php');

try {
    // Check authentication
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ETUDIANT') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }

    $id_user = $_SESSION['user_id'];

    // Get student ID with error handling
    $stmtEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id_utilisateur = ?");
    $stmtEtu->execute([$id_user]);
    $etudiant = $stmtEtu->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant) {
        http_response_code(404);
        echo json_encode(['error' => 'Student profile not found']);
        exit;
    }

    $id_etudiant = $etudiant['id'];

    // Update overdue assignments to EN_RETARD
    $updateOverdueQuery = "
        UPDATE assignations_travaux
        SET statut = 'EN_RETARD'
        WHERE id_etudiant = :id_etudiant
        AND statut = 'EN_ATTENTE'
        AND id_travail IN (
            SELECT id FROM travaux WHERE date_limite < NOW()
        )
    ";
    $updateStmt = $pdo->prepare($updateOverdueQuery);
    $updateStmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
    $updateStmt->execute();

    // Get status counts with optimized query
    $countQuery = "
        SELECT
            COALESCE(statut, 'EMPTY') as statut,
            COUNT(*) as count
        FROM assignations_travaux
        WHERE id_etudiant = :id_etudiant
        GROUP BY statut
    ";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
    $countStmt->execute();
    $statusCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Calculate counts with proper logic
    $counts = [
        'all' => array_sum($statusCounts),
        'EN_ATTENTE' => $statusCounts['EN_ATTENTE'] ?? 0, // À rendre only includes pending assignments
        'RENDU' => ($statusCounts['RENDU'] ?? 0) + ($statusCounts['EVALUE'] ?? 0),
        'EVALUE' => $statusCounts['EVALUE'] ?? 0,
        'EN_RETARD' => $statusCounts['EN_RETARD'] ?? 0
    ];

    // Add timestamp for cache busting
    $counts['timestamp'] = time();

    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo json_encode($counts);

} catch (Exception $e) {
    error_log("API Error in get_assignment_counts.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
