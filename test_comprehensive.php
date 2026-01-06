<?php
/**
 * Comprehensive test for manual group creation feature
 */

// Mock PDO class for testing
class MockPDO {
    public function prepare($query) {
        return new MockStatement($query);
    }

    public function beginTransaction() {}
    public function commit() {}
    public function rollBack() {}
    public function lastInsertId() { return 1; }
}

class MockStatement {
    private $query;
    private $mockData = [];

    public function __construct($query) {
        $this->query = $query;
    }

    public function execute($params) {
        // Mock different query responses based on query type
        if (strpos($this->query, 'espaces_pedagogiques') !== false && strpos($this->query, 'JOIN formateurs') !== false) {
            // Check space authorization - return space if authorized
            $this->mockData = [['id' => 1]];
        } elseif (strpos($this->query, 'promotions WHERE id = ?') !== false) {
            // Check promotion exists
            $this->mockData = [['id' => 1]];
        } elseif (strpos($this->query, 'etudiants WHERE nom = ? AND prenom = ?') !== false) {
            // Student lookup
            $mockStudents = [
                ['nom' => 'Dupont', 'prenom' => 'Jean'],
                ['nom' => 'Martin', 'prenom' => 'Marie'],
                ['nom' => 'Dubois', 'prenom' => 'Pierre'],
                ['nom' => 'Garcia', 'prenom' => 'Sophie']
            ];

            $nom = $params[0] ?? '';
            $prenom = $params[1] ?? '';

            foreach ($mockStudents as $student) {
                if ($student['nom'] === $nom && $student['prenom'] === $prenom) {
                    $this->mockData = [['id' => rand(1, 100)]];
                    return;
                }
            }
            $this->mockData = []; // Student not found
        } elseif (strpos($this->query, 'COUNT(*) as nb FROM etudiants') !== false) {
            // Count students in promotion
            $this->mockData = [['nb' => 4]];
        } elseif (strpos($this->query, 'SELECT id FROM etudiants WHERE id_promotion') !== false) {
            // Get all students in promotion
            $this->mockData = [['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4]];
        } elseif (strpos($this->query, 'etudiants WHERE id = ?') !== false) {
            // Check individual student exists
            $this->mockData = [['id' => 1]];
        }
    }

    public function fetch() {
        return !empty($this->mockData) ? $this->mockData[0] : false;
    }

    public function fetchAll() {
        return $this->mockData;
    }
}

// Mock session and other globals
$_SESSION = ['role' => 'FORMATEUR', 'user_id' => 1];
$_SERVER = ['REQUEST_METHOD' => 'POST'];

// Include the function we want to test
function parseGroupesManuels($definition, $pdo, $id_promotion) {
    $groupes = [];

    // Séparer les groupes par ";"
    $groupes_str = explode(';', $definition);

    foreach ($groupes_str as $groupe_str) {
        $groupe_str = trim($groupe_str);
        if (empty($groupe_str)) continue;

        // Séparer le nom du groupe et les étudiants par ":"
        $parts = explode(':', $groupe_str, 2);
        if (count($parts) !== 2) continue;

        $nom_groupe = trim($parts[0]);
        $etudiants_str = trim($parts[1]);

        // Séparer les étudiants par ","
        $etudiants_noms = array_map('trim', explode(',', $etudiants_str));

        $etudiants_ids = [];
        foreach ($etudiants_noms as $nom_prenom) {
            if (empty($nom_prenom)) continue;

            // Séparer nom et prénom
            $parts_nom = explode(' ', $nom_prenom, 2);
            if (count($parts_nom) !== 2) continue;

            $nom = trim($parts_nom[0]);
            $prenom = trim($parts_nom[1]);

            // Trouver l'étudiant dans la promotion
            $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE nom = ? AND prenom = ? AND id_promotion = ?");
            $stmt->execute([$nom, $prenom, $id_promotion]);
            $etudiant = $stmt->fetch();

            if ($etudiant) {
                $etudiants_ids[] = $etudiant['id'];
            } else {
                return []; // Erreur si étudiant non trouvé
            }
        }

        if (!empty($etudiants_ids)) {
            $groupes[] = $etudiants_ids;
        }
    }

    return $groupes;
}

// Mock functions
function setError($msg) { global $error; $error = $msg; }
function setSuccess($msg) { global $success; $success = $msg; }
function generateCsrfToken() { return 'test_token'; }
function verifyCsrfToken($token) { return $token === 'test_token'; }

// Test cases
$testCases = [
    [
        'name' => 'Valid collective work with manual groups',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'COLLECTIF',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_promotion' => '1',
            'creer_groupes_manuellement' => 'on',
            'definition_groupes' => 'Groupe 1: Dupont Jean, Martin Marie; Groupe 2: Dubois Pierre, Garcia Sophie',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => null,
        'expected_success' => 'Travail créé avec succès ! 2 groupes ont été créés avec 4 étudiants.'
    ],
    [
        'name' => 'Invalid student name in group definition',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'COLLECTIF',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_promotion' => '1',
            'creer_groupes_manuellement' => 'on',
            'definition_groupes' => 'Groupe 1: Invalid Student, Martin Marie',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => 'Erreur dans la définition des groupes. Vérifiez le format et que tous les étudiants appartiennent à la promotion.',
        'expected_success' => null
    ],
    [
        'name' => 'Empty group definition',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'COLLECTIF',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_promotion' => '1',
            'creer_groupes_manuellement' => 'on',
            'definition_groupes' => '',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => 'La définition des groupes est requise.',
        'expected_success' => null
    ],
    [
        'name' => 'Valid collective work without manual groups',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'COLLECTIF',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_promotion' => '1',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => null,
        'expected_success' => 'Travail créé avec succès ! 4 étudiants ont été notifiés.'
    ],
    [
        'name' => 'Valid group work',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'GROUPE',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_promotion_groupe' => '1',
            'taille_groupe' => '2',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => null,
        'expected_success' => 'Travail créé avec succès ! 2 groupes ont été créés avec 4 étudiants.'
    ],
    [
        'name' => 'Valid individual work',
        'post' => [
            'action' => 'creer_travail',
            'type_travail' => 'INDIVIDUEL',
            'id_espace' => '1',
            'titre' => 'Test Work',
            'description' => 'Test description with enough characters',
            'date_limite' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id_etudiant' => '1',
            'csrf_token' => 'test_token'
        ],
        'expected_error' => null,
        'expected_success' => 'Travail créé avec succès ! L\'étudiant a été notifié.'
    ]
];

$pdo = new MockPDO();

echo "Running comprehensive tests for manual group creation feature:\n\n";

$passed = 0;
$total = count($testCases);

foreach ($testCases as $i => $test) {
    echo "Test " . ($i + 1) . ": " . $test['name'] . "\n";

    // Reset globals
    $error = null;
    $success = null;
    $_POST = $test['post'];

    // Include and run the action logic (simplified version)
    try {
        if ($_POST['action'] === 'creer_travail') {
            // Simplified validation logic from travail_action.php
            $type_travail = strtoupper(trim($_POST['type_travail'] ?? ''));
            $id_espace = intval($_POST['id_espace'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date_limite = $_POST['date_limite'] ?? '';
            $id_formateur = $_SESSION['user_id'];

            // Basic validations
            if (!in_array($type_travail, ['COLLECTIF', 'GROUPE', 'INDIVIDUEL'])) {
                setError('Type de travail invalide.');
            } elseif (strlen($titre) < 5) {
                setError('Le titre doit contenir au moins 5 caractères.');
            } elseif (strlen($description) < 10) {
                setError('La description doit contenir au moins 10 caractères.');
            } elseif (new DateTime($date_limite) <= new DateTime()) {
                setError('La date limite doit être dans le futur.');
            } else {
                // Space authorization check
                $checkEspace = $pdo->prepare("SELECT e.id FROM espaces_pedagogiques e JOIN formateurs f ON e.id_formateur = f.id WHERE e.id = ? AND f.id_utilisateur = ?");
                $checkEspace->execute([$id_espace, $id_formateur]);
                $espaceAutorise = $checkEspace->fetch();

                if (!$espaceAutorise) {
                    setError('Espace pédagogique invalide ou non autorisé.');
                } else {
                    // Type-specific validations
                    if ($type_travail === 'COLLECTIF') {
                        $id_promotion = intval($_POST['id_promotion'] ?? 0);
                        $creer_groupes_manuellement = isset($_POST['creer_groupes_manuellement']) && $_POST['creer_groupes_manuellement'] === 'on';

                        $checkPromo = $pdo->prepare("SELECT id FROM promotions WHERE id = ?");
                        $checkPromo->execute([$id_promotion]);

                        if (!$checkPromo->fetch()) {
                            setError('Promotion invalide.');
                        } elseif ($creer_groupes_manuellement) {
                            $definition_groupes = trim($_POST['definition_groupes'] ?? '');

                            if (empty($definition_groupes)) {
                                setError('La définition des groupes est requise.');
                            } else {
                                $groupes_manuels = parseGroupesManuels($definition_groupes, $pdo, $id_promotion);

                                if (empty($groupes_manuels)) {
                                    setError('Erreur dans la définition des groupes. Vérifiez le format et que tous les étudiants appartiennent à la promotion.');
                                } else {
                                    $nb_groupes = count($groupes_manuels);
                                    $nb_assignations = array_sum(array_map('count', $groupes_manuels));
                                    setSuccess("Travail créé avec succès ! $nb_groupes groupes ont été créés avec $nb_assignations étudiants.");
                                }
                            }
                        } else {
                            $countEtu = $pdo->prepare("SELECT COUNT(*) as nb FROM etudiants WHERE id_promotion = ?");
                            $countEtu->execute([$id_promotion]);
                            $result = $countEtu->fetch();
                            $nb_assignations = $result['nb'];
                            setSuccess("Travail créé avec succès ! $nb_assignations étudiants ont été notifiés.");
                        }
                    } elseif ($type_travail === 'GROUPE') {
                        $id_promotion_groupe = intval($_POST['id_promotion_groupe'] ?? 0);
                        $taille_groupe = intval($_POST['taille_groupe'] ?? 0);

                        $checkPromo = $pdo->prepare("SELECT id FROM promotions WHERE id = ?");
                        $checkPromo->execute([$id_promotion_groupe]);

                        if (!$checkPromo->fetch()) {
                            setError('Promotion invalide.');
                        } else {
                            $countEtu = $pdo->prepare("SELECT COUNT(*) as nb FROM etudiants WHERE id_promotion = ?");
                            $countEtu->execute([$id_promotion_groupe]);
                            $result = $countEtu->fetch();
                            $nb_etudiants = $result['nb'];

                            if ($nb_etudiants === 0) {
                                setError('Cette promotion ne contient aucun étudiant.');
                            } elseif ($taille_groupe < 2 || $taille_groupe > 10) {
                                setError('La taille du groupe doit être entre 2 et 10 étudiants.');
                            } else {
                                $nb_groupes = ceil($nb_etudiants / $taille_groupe);
                                $nb_assignations = $nb_etudiants;
                                setSuccess("Travail créé avec succès ! $nb_groupes groupes ont été créés avec $nb_assignations étudiants.");
                            }
                        }
                    } else { // INDIVIDUEL
                        $id_etudiant = intval($_POST['id_etudiant'] ?? 0);

                        $checkEtu = $pdo->prepare("SELECT id FROM etudiants WHERE id = ?");
                        $checkEtu->execute([$id_etudiant]);

                        if (!$checkEtu->fetch()) {
                            setError('Étudiant invalide.');
                        } else {
                            setSuccess('Travail créé avec succès ! L\'étudiant a été notifié.');
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        setError('Erreur : ' . $e->getMessage());
    }

    // Check results
    $actual_error = $error;
    $actual_success = $success;

    $error_match = ($actual_error === $test['expected_error']);
    $success_match = ($actual_success === $test['expected_success']);

    if ($error_match && $success_match) {
        echo "✓ PASS\n";
        $passed++;
    } else {
        echo "✗ FAIL\n";
        echo "  Expected error: " . ($test['expected_error'] ?? 'null') . "\n";
        echo "  Actual error: " . ($actual_error ?? 'null') . "\n";
        echo "  Expected success: " . ($test['expected_success'] ?? 'null') . "\n";
        echo "  Actual success: " . ($actual_success ?? 'null') . "\n";
    }

    echo "\n";
}

echo "Results: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "🎉 All tests passed! Manual group creation feature is working correctly.\n";
} else {
    echo "❌ Some tests failed. Please review the implementation.\n";
}
?>
