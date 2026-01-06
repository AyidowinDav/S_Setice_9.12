<?php
// Test script for frontend integration of COLLECTIF work type
require_once('config/db.php');
require_once('includes/csrf.php');
require_once('includes/messages.php');

// Simulate session for formateur
$_SESSION['role'] = 'FORMATEUR';
$_SESSION['user_id'] = 1; // Assuming formateur ID 1 exists

echo "Testing COLLECTIF work type frontend integration...\n\n";

// Test 1: Simulate COLLECTIF work creation without manual groups
echo "Test 1: COLLECTIF work without manual groups\n";
$_POST = [
    'action' => 'creer_travail',
    'type_travail' => 'COLLECTIF',
    'id_espace' => 1, // Assuming espace exists
    'id_promotion' => 1, // Assuming promotion exists
    'titre' => 'Test Travail Collectif',
    'description' => 'Description du travail collectif de test',
    'date_limite' => date('Y-m-d H:i:s', strtotime('+7 days')),
    'csrf_token' => generateCsrfToken()
];

require_once('actions/travail_action.php');

$success = getSuccess();
$error = getError();

if ($success) {
    echo "✓ SUCCESS: COLLECTIF work created successfully\n";
    echo "Message: $success\n";
} else {
    echo "✗ FAILED: $error\n";
}

echo "\n";

// Test 2: Simulate COLLECTIF work creation with manual groups
echo "Test 2: COLLECTIF work with manual groups\n";
$_POST = [
    'action' => 'creer_travail',
    'type_travail' => 'COLLECTIF',
    'id_espace' => 1,
    'id_promotion' => 1,
    'creer_groupes_manuellement' => 'on',
    'definition_groupes' => 'Groupe A: Dupont Jean, Martin Marie; Groupe B: Durand Paul',
    'titre' => 'Test Travail Collectif avec Groupes',
    'description' => 'Description du travail collectif avec groupes manuels',
    'date_limite' => date('Y-m-d H:i:s', strtotime('+7 days')),
    'csrf_token' => generateCsrfToken()
];

require_once('actions/travail_action.php');

$success = getSuccess();
$error = getError();

if ($success) {
    echo "✓ SUCCESS: COLLECTIF work with manual groups created successfully\n";
    echo "Message: $success\n";
} else {
    echo "✗ FAILED: $error\n";
}

echo "\n";

// Test 3: Check database records
echo "Test 3: Database verification\n";
try {
    // Check if travaux were created
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM travaux WHERE type_travail = 'COLLECTIF'");
    $result = $stmt->fetch();
    echo "✓ Found {$result['count']} COLLECTIF works in database\n";

    // Check if assignments were created
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM assignations_travail WHERE id_travail IN (SELECT id FROM travaux WHERE type_travail = 'COLLECTIF')");
    $result = $stmt->fetch();
    echo "✓ Found {$result['count']} assignments for COLLECTIF works\n";

    // Check if groups were created for manual groups
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM groupes WHERE id_travail IN (SELECT id FROM travaux WHERE type_travail = 'COLLECTIF')");
    $result = $stmt->fetch();
    echo "✓ Found {$result['count']} groups for COLLECTIF works\n";

} catch (Exception $e) {
    echo "✗ Database check failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
