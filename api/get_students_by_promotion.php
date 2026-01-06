<?php
header('Content-Type: application/json');
require_once('../config/db.php');

if (!isset($_GET['promotion_id']) || empty($_GET['promotion_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de promotion manquant']);
    exit;
}

$promotionId = intval($_GET['promotion_id']);

try {
    $stmt = $pdo->prepare("SELECT id, nom, prenom FROM etudiants WHERE id_promotion = ? ORDER BY nom, prenom");
    $stmt->execute([$promotionId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des étudiants'
    ]);
}
?>
