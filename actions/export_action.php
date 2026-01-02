<?php
require_once '../config/db.php';
require_once '../includes/messages.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['DIRECTEUR', 'ADMIN'])) {
    header('Location: ../index.php');
    exit;
}

$table = $_GET['table'] ?? '';
$format = $_GET['format'] ?? 'csv';

$allowed_tables = ['utilisateur', 'etudiants', 'formateurs', 'promotions', 'espaces_pedagogiques', 'etudiant_espace', 'promotion_espace', 'travaux'];

if (!in_array($table, $allowed_tables)) {
    die('Table non autorisée');
}

try {
    $stmt = $pdo->query("SELECT * FROM $table");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        die('Aucune donnée à exporter');
    }

    $filename = $table . '_' . date('Y-m-d_H-i-s');

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // En-têtes
        fputcsv($output, array_keys($data[0]));

        // Données
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    } elseif ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    die('Erreur lors de l\'export : ' . $e->getMessage());
}
?>